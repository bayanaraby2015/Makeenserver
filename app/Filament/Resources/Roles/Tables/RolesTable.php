<?php

namespace App\Filament\Resources\Roles\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('roles.fields.name'))
                    ->formatStateUsing(fn (string $state): string => __('roles.names.'.$state, [], 'ar') ?: $state)
                    ->description(fn ($record): string => $record->name)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('guard_name')
                    ->label(__('roles.fields.guard_name'))
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('permissions_count')
                    ->label(__('roles.fields.permissions_count'))
                    ->counts('permissions')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('users_count')
                    ->label(__('roles.fields.users_count'))
                    ->counts('users')
                    ->badge()
                    ->color('success'),

                TextColumn::make('permissions.name')
                    ->label(__('roles.fields.permissions'))
                    ->badge()
                    ->separator(',')
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->wrap()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('roles.fields.created_at'))
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated(false);
    }
}
