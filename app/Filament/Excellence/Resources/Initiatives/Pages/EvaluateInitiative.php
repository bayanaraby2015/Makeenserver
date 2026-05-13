<?php

namespace App\Filament\Excellence\Resources\Initiatives\Pages;

use App\Filament\Excellence\Resources\Initiatives\InitiativeResource;
use App\Models\Initiative;
use App\Models\InitiativeEvaluation;
use App\Models\InitiativeKpiValue;
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

            // Move initiative status forward when decision is finalized
            if (($state['decision'] ?? null) === 'approved' && $initiative->status !== 'approved') {
                $initiative->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                ]);
            } elseif (($state['decision'] ?? null) === 'rejected' && $initiative->status !== 'rejected') {
                $initiative->update([
                    'status' => 'rejected',
                    'rejection_reason' => $state['recommendation'] ?? null,
                    'rejected_at' => now(),
                    'rejected_by' => Auth::id(),
                ]);
            } elseif (($state['decision'] ?? null) === 'revisions_requested' && $initiative->status !== 'revisions_requested') {
                $initiative->update(['status' => 'revisions_requested']);
            } else {
                if ($initiative->status === 'submitted') {
                    $initiative->update(['status' => 'under_review']);
                }
            }
        });

        Notification::make()
            ->success()
            ->title('تم حفظ تقرير المراجعة')
            ->send();

        $this->redirect(static::getResource()::getUrl('view', ['record' => $initiative->id]));
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
