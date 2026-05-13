<?php

namespace App\Filament\Consultant\Resources\Initiatives\Pages;

use App\Filament\Consultant\Resources\Initiatives\InitiativeResource;
use App\Filament\Support\ServiceEvaluationAction;
use App\Models\Initiative;
use App\Models\InitiativeEvaluation;
use App\Notifications\InitiativeReviewedNotification;
use App\Support\InitiativeRecipients;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class ViewInitiative extends ViewRecord
{
    protected static string $resource = InitiativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ServiceEvaluationAction::make('initiative', fn (Initiative $record): ?int => $record->organization_id),
            Action::make('evaluate')
                ->label(__('initiatives.actions.save_evaluation'))
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->color('primary')
                ->schema(fn (Schema $schema): Schema => $schema->components([
                    TextInput::make('overall_score')
                        ->label(__('initiatives.fields.overall_score'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100),
                    Select::make('decision')
                        ->label(__('initiatives.fields.decision'))
                        ->required()
                        ->options([
                            'approved' => __('initiatives.decisions.approved'),
                            'revisions_requested' => __('initiatives.decisions.revisions_requested'),
                            'rejected' => __('initiatives.decisions.rejected'),
                        ]),
                    Textarea::make('strengths')
                        ->label(__('initiatives.fields.strengths'))
                        ->rows(3),
                    Textarea::make('improvements')
                        ->label(__('initiatives.fields.improvements'))
                        ->rows(3),
                    Textarea::make('recommendation')
                        ->label(__('initiatives.fields.recommendation'))
                        ->required()
                        ->rows(4),
                ]))
                /** @param array{overall_score?: string|null, decision: string, strengths?: string|null, improvements?: string|null, recommendation: string} $data */
                ->action(function (Initiative $record, array $data): void {
                    InitiativeEvaluation::query()->updateOrCreate(
                        [
                            'initiative_id' => $record->id,
                            'evaluator_id' => Auth::id(),
                        ],
                        [
                            'overall_score' => filled($data['overall_score'] ?? null) ? $data['overall_score'] : null,
                            'decision' => $data['decision'],
                            'strengths' => $data['strengths'] ?? null,
                            'improvements' => $data['improvements'] ?? null,
                            'recommendation' => $data['recommendation'],
                            'finalized_at' => now(),
                        ],
                    );

                    NotificationFacade::send(
                        InitiativeRecipients::relatedUsers($record),
                        new InitiativeReviewedNotification($record, 'evaluated', $data['recommendation']),
                    );

                    Notification::make()->success()->title(__('initiatives.actions.save_evaluation_success'))->send();
                }),
            Action::make('update_status')
                ->label(__('initiatives.actions.update_status'))
                ->icon(Heroicon::OutlinedCog6Tooth)
                ->color('gray')
                ->schema(fn (Schema $schema): Schema => $schema->components([
                    Select::make('status')
                        ->label(__('initiatives.fields.status'))
                        ->required()
                        ->options([
                            'under_review' => __('initiatives.statuses.under_review'),
                            'approved' => __('initiatives.statuses.approved'),
                            'revisions_requested' => __('initiatives.statuses.revisions_requested'),
                            'rejected' => __('initiatives.statuses.rejected'),
                        ]),
                    Textarea::make('note')
                        ->label(__('initiatives.fields.reviewer_notes'))
                        ->required()
                        ->rows(4),
                ]))
                /** @param array{status: string, note: string} $data */
                ->action(function (Initiative $record, array $data): void {
                    $record->update([
                        'status' => $data['status'],
                        'rejection_reason' => in_array($data['status'], ['rejected', 'revisions_requested'], true) ? $data['note'] : $record->rejection_reason,
                        'approved_at' => $data['status'] === 'approved' ? now() : $record->approved_at,
                        'approved_by' => $data['status'] === 'approved' ? Auth::id() : $record->approved_by,
                        'rejected_at' => $data['status'] === 'rejected' ? now() : $record->rejected_at,
                        'rejected_by' => $data['status'] === 'rejected' ? Auth::id() : $record->rejected_by,
                    ]);

                    InitiativeEvaluation::query()->updateOrCreate(
                        [
                            'initiative_id' => $record->id,
                            'evaluator_id' => Auth::id(),
                        ],
                        [
                            'decision' => $data['status'],
                            'recommendation' => $data['note'],
                            'finalized_at' => now(),
                        ],
                    );

                    NotificationFacade::send(
                        InitiativeRecipients::relatedUsers($record),
                        new InitiativeReviewedNotification($record, 'status_updated', $data['note']),
                    );

                    Notification::make()->success()->title(__('initiatives.actions.update_status_success'))->send();
                }),
        ];
    }
}
