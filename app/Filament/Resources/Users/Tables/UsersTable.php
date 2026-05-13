<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(__('users.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('users.fields.email'))
                    ->searchable()
                    ->copyable(),

                TextColumn::make('roles.name')
                    ->label(__('users.fields.roles'))
                    ->badge()
                    ->separator(',')
                    ->formatStateUsing(function (?string $state): string {
                        if ($state === null || $state === '') {
                            return '—';
                        }
                        $key = 'roles.names.'.$state;
                        $translated = __($key);

                        return is_string($translated) && $translated !== $key ? $translated : $state;
                    }),

                TextColumn::make('primaryOrganization.name_ar')
                    ->label(__('users.fields.primary_organization'))
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(__('users.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('users.statuses.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('last_login_at')
                    ->label(__('users.fields.last_login_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('users.fields.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('users.fields.status'))
                    ->options([
                        'active' => __('users.statuses.active'),
                        'pending' => __('users.statuses.pending'),
                        'suspended' => __('users.statuses.suspended'),
                    ]),

                SelectFilter::make('roles')
                    ->label(__('users.fields.roles'))
                    ->relationship('roles', 'name')
                    ->options(function (): array {
                        $options = [];
                        foreach (Role::query()->orderBy('name')->get(['id', 'name']) as $role) {
                            $name = (string) $role->name;
                            $key = 'roles.names.'.$name;
                            $translated = __($key);
                            $options[$role->id] = is_string($translated) && $translated !== $key ? $translated : $name;
                        }

                        return $options;
                    }),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('activate')
                    ->label(__('users.actions.activate'))
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->status !== 'active')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->update(['status' => 'active']);

                        Notification::make()
                            ->success()
                            ->title(__('users.actions.activate_success'))
                            ->send();
                    }),

                Action::make('suspend')
                    ->label(__('users.actions.suspend'))
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (User $record): bool => $record->status === 'active')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->update(['status' => 'suspended']);

                        Notification::make()
                            ->danger()
                            ->title(__('users.actions.suspend_success'))
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
