<?php

namespace App\Filament\Excellence\Resources\Initiatives\Pages;

use App\Filament\Excellence\Resources\Initiatives\InitiativeResource;
use App\Models\Initiative;
use App\Models\InitiativeEvaluation;
use App\Models\InitiativeKpiValue;
use App\Notifications\InitiativeReviewedNotification;
use App\Support\InitiativeRecipients;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * @property Schema $form
 */
class EvaluateInitiative extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = InitiativeResource::class;

    protected string $view = 'filament.excellence.evaluate-initiative';

    public ?int $initiativeId = null;

    /** @var array<string, mixed> */
    public array $data = [];

    public function mount(int|string $record): void
    {
        $this->initiativeId = (int) $record;

        abort_unless(Auth::user()?->can('review', $this->getRecord()) ?? false, 403);

        $kpiData = [];
        foreach ($this->getRecord()->kpiValues()->with('definition')->get() as $kpi) {
            $definition = $kpi->definition;
            $kpiData[] = [
                'kpi_value_id' => $kpi->id,
                'indicator' => __('initiatives.domains.'.($definition->domain ?? '')).' — '.($definition->indicator ?? ''),
                'baseline' => $kpi->baseline,
                'target' => $kpi->target,
                'score' => $kpi->score,
                'reviewer_notes' => $kpi->reviewer_notes,
            ];
        }

        $evaluation = $this->getRecord()->evaluation;

        $this->data = [
            'overall_score' => $evaluation?->overall_score,
            'strengths' => $evaluation?->strengths,
            'improvements' => $evaluation?->improvements,
            'recommendation' => $evaluation?->recommendation,
            'decision' => $evaluation->decision ?? 'pending',
            'kpis' => $kpiData,
        ];

        $this->form->fill($this->data);
    }

    public function getRecord(): Initiative
    {
        /** @var Initiative $initiative */
        $initiative = Initiative::query()->findOrFail($this->initiativeId);

        return $initiative;
    }

    public function getTitle(): string
    {
        return __('initiatives.tabs.kpis').' — '.$this->getRecord()->name_ar;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('initiatives.tabs.kpis'))
                    ->schema([
                        Repeater::make('kpis')
                            ->label(__('initiatives.fields.kpis'))
                            ->columns(4)
                            ->schema([
                                TextInput::make('indicator')
                                    ->label(__('initiatives.fields.kpi_indicator'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpanFull(),
                                TextInput::make('baseline')
                                    ->label(__('initiatives.fields.baseline'))
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('target')
                                    ->label(__('initiatives.fields.target'))
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('score')
                                    ->label('التقييم (0-5)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(5),
                                Textarea::make('reviewer_notes')
                                    ->label(__('initiatives.fields.rejection_reason'))
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['indicator'] ?? null)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->collapsible(),
                    ]),

                Section::make(__('initiatives.sections.workflow'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('overall_score')
                            ->label('الدرجة الإجمالية (0-5)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->step(0.1),
                        Select::make('decision')
                            ->label('قرار المراجعة')
                            ->options([
                                'pending' => 'قيد المراجعة',
                                'approved' => __('initiatives.statuses.approved'),
                                'revisions_requested' => __('initiatives.statuses.revisions_requested'),
                                'rejected' => __('initiatives.statuses.rejected'),
                            ])
                            ->native(false)
                            ->required(),
                        Textarea::make('strengths')
                            ->label('نقاط القوة')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('improvements')
                            ->label('فرص التحسين')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('recommendation')
                            ->label('التوصية النهائية')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $initiative = $this->getRecord();

        abort_unless(Auth::user()?->can('review', $initiative) ?? false, 403);

        DB::transaction(function () use ($state, $initiative): void {
            InitiativeEvaluation::query()->updateOrCreate(
                ['initiative_id' => $initiative->id],
                [
                    'evaluator_id' => Auth::id(),
                    'overall_score' => $state['overall_score'] ?? null,
                    'strengths' => $state['strengths'] ?? null,
                    'improvements' => $state['improvements'] ?? null,
                    'recommendation' => $state['recommendation'] ?? null,
                    'decision' => $state['decision'] ?? 'pending',
                    'finalized_at' => in_array(($state['decision'] ?? 'pending'), ['approved', 'rejected'], true)
                        ? now()
                        : null,
                ],
            );

            foreach (($state['kpis'] ?? []) as $row) {
                if (! isset($row['kpi_value_id'])) {
                    continue;
                }

                InitiativeKpiValue::query()
                    ->where('id', $row['kpi_value_id'])
                    ->update([
                        'score' => $row['score'] ?? null,
                        'reviewer_notes' => $row['reviewer_notes'] ?? null,
                    ]);
            }

            // Excellence approval is the FIRST step. Approving moves the initiative to
            // 'excellence_approved' status. The matching consultant(s) then receive a
            // notification + email to perform the SECOND approval which finalises the
            // record (status='approved').
            $decision = $state['decision'] ?? null;

            if ($decision === 'approved' && $initiative->status !== 'excellence_approved') {
                $initiative->update(['status' => 'excellence_approved']);
                $notifyEvent = 'excellence_approved';
            } elseif ($decision === 'rejected' && $initiative->status !== 'rejected') {
                $initiative->update([
                    'status' => 'rejected',
                    'rejection_reason' => $state['recommendation'] ?? null,
                    'rejected_at' => now(),
                    'rejected_by' => Auth::id(),
                ]);
                $notifyEvent = 'rejected';
            } elseif ($decision === 'revisions_requested' && $initiative->status !== 'revisions_requested') {
                $initiative->update(['status' => 'revisions_requested']);
                $notifyEvent = 'revisions_requested';
            } else {
                if ($initiative->status === 'submitted') {
                    $initiative->update(['status' => 'under_review']);
                }
                $notifyEvent = null;
            }
        });

        $initiative->refresh();
        $this->dispatchEvaluationNotifications($initiative, $state['decision'] ?? null, $state['recommendation'] ?? null);

        Notification::make()
            ->success()
            ->title('تم حفظ تقرير المراجعة')
            ->send();

        $this->redirect(static::getResource()::getUrl('view', ['record' => $initiative->id]));
    }

    /**
     * Push in-app + email notifications based on the decision made by the
     * Excellence reviewer. Excellence approval is step 1 of 2 — the
     * matching consultant(s) get pinged to perform the final review.
     */
    protected function dispatchEvaluationNotifications(Initiative $initiative, ?string $decision, ?string $recommendation): void
    {
        if ($decision === null) {
            return;
        }

        \Illuminate\Support\Facades\Log::info('EvaluateInitiative: dispatching', [
            'initiative_id' => $initiative->id,
            'decision' => $decision,
            'status' => $initiative->status,
        ]);

        try {
            if ($decision === 'approved' && $initiative->status === 'excellence_approved') {
                $consultants = InitiativeRecipients::consultants($initiative);
                \Illuminate\Support\Facades\Log::info('EvaluateInitiative: notifying consultants', [
                    'count' => $consultants->count(),
                    'emails' => $consultants->pluck('email')->all(),
                ]);
                if ($consultants->isNotEmpty()) {
                    NotificationFacade::send(
                        $consultants,
                        new InitiativeReviewedNotification($initiative, 'status_updated', 'اعتمدها مسار الإجادة — يرجى استكمال المراجعة النهائية'),
                    );
                }

                $association = InitiativeRecipients::associationUsers($initiative);
                \Illuminate\Support\Facades\Log::info('EvaluateInitiative: notifying association users', [
                    'count' => $association->count(),
                    'emails' => $association->pluck('email')->all(),
                ]);
                NotificationFacade::send(
                    $association,
                    new InitiativeReviewedNotification($initiative, 'status_updated', 'تم اعتماد المبادرة من مسار الإجادة وهي قيد المراجعة النهائية من المستشار'),
                );
            } elseif ($decision === 'rejected') {
                $association = InitiativeRecipients::associationUsers($initiative);
                \Illuminate\Support\Facades\Log::info('EvaluateInitiative: notifying association (rejected)', [
                    'count' => $association->count(),
                    'emails' => $association->pluck('email')->all(),
                ]);
                NotificationFacade::send(
                    $association,
                    new InitiativeReviewedNotification($initiative, 'rejected', $recommendation),
                );
            } elseif ($decision === 'revisions_requested') {
                $association = InitiativeRecipients::associationUsers($initiative);
                \Illuminate\Support\Facades\Log::info('EvaluateInitiative: notifying association (revisions)', [
                    'count' => $association->count(),
                    'emails' => $association->pluck('email')->all(),
                ]);
                NotificationFacade::send(
                    $association,
                    new InitiativeReviewedNotification($initiative, 'status_updated', $recommendation),
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('EvaluateInitiative: notification dispatch failed', [
                'initiative_id' => $initiative->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * @return array<int, Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ التقرير')
                ->submit('save'),
        ];
    }
}
