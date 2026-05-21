<?php

namespace App\Filament\Resources\Organizations\Tables;

use App\Mail\OrganizationRejectedMail;
use App\Models\Organization;
use App\Support\SafeMailer;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrganizationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name_ar')
                    ->label(__('organizations.fields.name_ar'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('type')
                    ->label(__('organizations.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('organizations.types.'.$state)),

                TextColumn::make('city')
                    ->label(__('organizations.fields.city'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('phone')
                    ->label(__('organizations.fields.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('email')
                    ->label(__('organizations.fields.email'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(__('organizations.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('organizations.statuses.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'suspended' => 'danger',
                        'archived' => 'gray',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('approved_at')
                    ->label(__('organizations.fields.approved_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('organizations.fields.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('organizations.fields.type'))
                    ->options([
                        'association' => __('organizations.types.association'),
                        'donor' => __('organizations.types.donor'),
                        'excellence_team' => __('organizations.types.excellence_team'),
                        'consultant_firm' => __('organizations.types.consultant_firm'),
                    ]),

                SelectFilter::make('status')
                    ->label(__('organizations.fields.status'))
                    ->options([
                        'pending' => __('organizations.statuses.pending'),
                        'active' => __('organizations.statuses.active'),
                        'suspended' => __('organizations.statuses.suspended'),
                        'archived' => __('organizations.statuses.archived'),
                        'rejected' => __('organizations.statuses.rejected'),
                    ]),

            ])
            ->recordActions([
                Action::make('approve')
                    ->label(__('organizations.actions.approve'))
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (Organization $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading(fn (): string => __('organizations.actions.approve_modal_heading'))
                    ->modalDescription(fn (): string => __('organizations.actions.approve_modal_description'))
                    ->action(function (Organization $record): void {
                        Log::info('OrganizationApprove: action triggered', [
                            'organization_id' => $record->id,
                            'organization_email' => $record->email,
                            'approved_by' => Auth::id(),
                        ]);

                        // Activation, e-mail, and member roll-out are
                        // handled by the Organization model's `updated`
                        // event so every approval path (this action,
                        // the edit form, the new header action on the
                        // detail page, tinker, etc.) behaves the same.
                        $record->approveBy(Auth::id());

                        Notification::make()
                            ->success()
                            ->title(__('organizations.actions.approve_success'))
                            ->send();
                    }),

                Action::make('reject')
                    ->label(__('organizations.actions.reject'))
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (Organization $record): bool => $record->status === 'pending')
                    ->schema(fn (Schema $schema): Schema => $schema->components([
                        Textarea::make('reason')
                            ->label(__('organizations.actions.reject_reason_label'))
                            ->placeholder(__('organizations.actions.reject_reason_placeholder'))
                            ->required()
                            ->minLength(5)
                            ->rows(4),
                    ]))
                    ->modalHeading(fn (): string => __('organizations.actions.reject_modal_heading'))
                    ->modalDescription(fn (): string => __('organizations.actions.reject_modal_description'))
                    /** @param array{reason: string} $data */
                    ->action(function (Organization $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['reason'],
                            'rejected_at' => now(),
                            'rejected_by' => Auth::id(),
                        ]);

                        SafeMailer::send(
                            $record->email,
                            new OrganizationRejectedMail($record, $data['reason']),
                            'organization_rejected',
                        );

                        Notification::make()
                            ->danger()
                            ->title(__('organizations.actions.reject_success'))
                            ->send();
                }),

                Action::make('activate_manager')
                    ->label(__('organizations.actions.activate_manager'))
                    ->icon(Heroicon::OutlinedUserPlus)
                    ->color('success')
                    ->visible(function (Organization $record): bool {
                        if ($record->status !== 'active') {
                            return false;
                        }

                        return $record->members()
                            ->where('status', 'pending')
                            ->exists();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (): string => __('organizations.actions.activate_manager_modal_heading'))
                    ->modalDescription(fn (): string => __('organizations.actions.activate_manager_modal_description'))
                    ->action(function (Organization $record): void {
                        $activated = [];
                        foreach ($record->members()->where('status', 'pending')->get() as $member) {
                            $member->forceFill([
                                'status' => 'active',
                                'email_verified_at' => $member->email_verified_at ?? now(),
                            ])->save();
                            $activated[] = $member->id;
                        }

                        Log::info('OrganizationApprove: manual member activation', [
                            'organization_id' => $record->id,
                            'activated_user_ids' => $activated,
                            'actor_id' => Auth::id(),
                        ]);

                        Notification::make()
                            ->success()
                            ->title(__('organizations.actions.activate_manager_success', ['count' => count($activated)]))
                            ->send();
                    }),

                Action::make('suspend')
                    ->label(__('organizations.actions.suspend'))
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (Organization $record): bool => $record->status === 'active')
                    ->requiresConfirmation()
                    ->action(function (Organization $record): void {
                        $record->update(['status' => 'suspended']);
                        foreach ($record->members as $member) {
                            $member->update(['status' => 'suspended']);
                        }
                        Notification::make()
                            ->danger()
                            ->title(__('organizations.actions.suspend_success'))
                            ->send();
                    }),

                Action::make('reactivate')
                    ->label(__('organizations.actions.reactivate'))
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (Organization $record): bool => $record->status === 'suspended')
                    ->requiresConfirmation()
                    ->action(function (Organization $record): void {
                        $record->update(['status' => 'active']);
                        foreach ($record->members as $member) {
                            $member->update(['status' => 'active']);
                        }
                        Notification::make()
                            ->success()
                            ->title(__('organizations.actions.reactivate_success'))
                            ->send();
                    }),

                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
