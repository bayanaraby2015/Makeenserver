<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('activity.fields.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('log_name')
                    ->label(__('activity.fields.log_name'))
                    ->badge()
                    ->formatStateUsing(function (?string $state): string {
                        if ($state === null) {
                            return '-';
                        }

                        $key = 'activity.logs.'.$state;
                        $translated = __($key);

                        return is_string($translated) && $translated !== $key ? $translated : $state;
                    }),

                TextColumn::make('event')
                    ->label(__('activity.fields.event'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? __('activity.events.'.$state) : '-')
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'restored' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('description')
                    ->label(__('activity.fields.description'))
                    ->wrap(),

                TextColumn::make('causer.name')
                    ->label(__('activity.fields.causer'))
                    ->default('-'),

                TextColumn::make('subject_type')
                    ->label(__('activity.fields.subject_type'))
                    ->formatStateUsing(function (?string $state): string {
                        if ($state === null) {
                            return '-';
                        }

                        $base = class_basename($state);
                        $key = 'activity.models.'.$base;
                        $translated = __($key);

                        return is_string($translated) && $translated !== $key ? $translated : $base;
                    })
                    ->toggleable(),

                TextColumn::make('subject_id')
                    ->label(__('activity.fields.subject_id'))
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('log_name')
                    ->label(__('activity.fields.log_name'))
                    ->options(fn (): array => Activity::query()
                        ->whereNotNull('log_name')
                        ->distinct()
                        ->pluck('log_name', 'log_name')
                        ->all()),

                SelectFilter::make('event')
                    ->label(__('activity.fields.event'))
                    ->options([
                        'created' => __('activity.events.created'),
                        'updated' => __('activity.events.updated'),
                        'deleted' => __('activity.events.deleted'),
                        'restored' => __('activity.events.restored'),
                    ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['causer']));
    }
}
