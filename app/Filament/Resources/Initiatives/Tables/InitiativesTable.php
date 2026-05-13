<?php

namespace App\Filament\Resources\Initiatives\Tables;

use App\Mail\InitiativeApprovedMail;
use App\Mail\InitiativeRejectedMail;
use App\Models\Initiative;
use App\Notifications\InitiativeReviewedNotification;
use App\Support\DisplayNumber;
use App\Support\InitiativeRecipients;
use App\Support\SafeMailer;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class InitiativesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name_ar')
                    ->label(__('initiatives.fields.name_ar'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('organization.name_ar')
                    ->label(__('initiatives.fields.organization'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('domain')
                    ->label(__('initiatives.fields.domain'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('initiatives.domains.'.$state)),

                TextColumn::make('grand_total')
                    ->label(__('initiatives.fields.grand_total'))
                    ->formatStateUsing(fn (mixed $state) => DisplayNumber::riyalHtml($state))
                    ->html()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('initiatives.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('initiatives.statuses.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted', 'under_review' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'revisions_requested' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('submitted_at')
                    ->label(__('initiatives.fields.submitted_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approved_at')
                    ->label(__('initiatives.fields.approved_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('initiatives.fields.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('initiatives.fields.status'))
                    ->options([
                        'draft' => __('initiatives.statuses.draft'),
                        'submitted' => __('initiatives.statuses.submitted'),
                        'under_review' => __('initiatives.statuses.under_review'),
                        'approved' => __('initiatives.statuses.approved'),
                        'rejected' => __('initiatives.statuses.rejected'),
                        'revisions_requested' => __('initiatives.statuses.revisions_requested'),
                    ]),

                SelectFilter::make('domain')
                    ->label(__('initiatives.fields.domain'))
                    ->options([
                        'developmental_impact' => __('initiatives.domains.developmental_impact'),
                        'sustainability' => __('initiatives.domains.sustainability'),
                        'institutional_empowerment' => __('initiatives.domains.institutional_empowerment'),
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label(__('initiatives.actions.approve'))
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (Initiative $record): bool => in_array($record->status, ['submitted', 'under_review'], true))
                    ->requiresConfirmation()
                    ->modalHeading(fn (): string => __('initiatives.actions.approve_modal_heading'))
                    ->modalDescription(fn (): string => __('initiatives.actions.approve_modal_description'))
                    ->action(function (Initiative $record): void {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approved_by' => Auth::id(),
                            'rejection_reason' => null,
                            'rejected_at' => null,
                            'rejected_by' => null,
                        ]);

                        NotificationFacade::send(
                            InitiativeRecipients::relatedUsers($record),
                            new InitiativeReviewedNotification($record, 'approved'),
                        );

                        SafeMailer::send(
                            $record->organization?->email,
                            new InitiativeApprovedMail($record),
                            'initiative_approved',
                        );

                        Notification::make()
                            ->success()
                            ->title(__('initiatives.actions.approve_success'))
                            ->send();
                    }),

                Action::make('reject')
                    ->label(__('initiatives.actions.reject'))
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (Initiative $record): bool => in_array($record->status, ['submitted', 'under_review'], true))
                    ->schema(fn (Schema $schema): Schema => $schema->components([
                        Textarea::make('reason')
                            ->label(__('initiatives.actions.reject_reason_label'))
                            ->placeholder(__('initiatives.actions.reject_reason_placeholder'))
                            ->required()
                            ->minLength(5)
                            ->rows(4),
                    ]))
                    ->modalHeading(fn (): string => __('initiatives.actions.reject_modal_heading'))
                    /** @param array{reason: string} $data */
                    ->action(function (Initiative $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['reason'],
                            'rejected_at' => now(),
                            'rejected_by' => Auth::id(),
                        ]);

                        NotificationFacade::send(
                            InitiativeRecipients::relatedUsers($record),
                            new InitiativeReviewedNotification($record, 'rejected', $data['reason']),
                        );

                        SafeMailer::send(
                            $record->organization?->email,
                            new InitiativeRejectedMail($record, $data['reason']),
                            'initiative_rejected',
                        );

                        Notification::make()
                            ->danger()
                            ->title(__('initiatives.actions.reject_success'))
                            ->send();
                    }),

                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
