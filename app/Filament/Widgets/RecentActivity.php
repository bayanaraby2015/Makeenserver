<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class RecentActivity extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return false;
    }

    public function getTableHeading(): ?string
    {
        return __('widgets.activity.heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Activity::query()
                    ->with('causer')
                    ->latest()
                    ->limit(10)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('activity.fields.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('log_name')
                    ->label(__('activity.fields.log_name'))
                    ->badge(),

                TextColumn::make('description')
                    ->label(__('activity.fields.description'))
                    ->wrap(),

                TextColumn::make('causer.name')
                    ->label(__('activity.fields.causer'))
                    ->default('—'),
            ]);
    }
}
