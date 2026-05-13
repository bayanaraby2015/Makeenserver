<?php

namespace App\Filament\Widgets;

use App\Models\DonorInterest;
use App\Models\Initiative;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InitiativesStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = null;

    protected static ?int $sort = 2;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $total = Initiative::query()->count();
        $submitted = Initiative::query()->whereIn('status', ['submitted', 'under_review'])->count();
        $approved = Initiative::query()->where('status', 'approved')->count();
        $interests = DonorInterest::query()->count();

        return [
            Stat::make(__('widgets.initiatives.total'), (string) $total)
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('gray'),

            Stat::make(__('widgets.initiatives.pending'), (string) $submitted)
                ->descriptionIcon('heroicon-m-clock')
                ->color($submitted > 0 ? 'warning' : 'gray'),

            Stat::make(__('widgets.initiatives.approved'), (string) $approved)
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('widgets.initiatives.donor_interests'), (string) $interests)
                ->descriptionIcon('heroicon-m-hand-raised')
                ->color('primary'),
        ];
    }
}
