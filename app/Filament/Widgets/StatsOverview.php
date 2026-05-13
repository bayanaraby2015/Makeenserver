<?php

namespace App\Filament\Widgets;

use App\Models\Organization;
use App\Models\ServiceEvaluation;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = null;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $orgsTotal = Organization::query()->count();
        $orgsPending = Organization::query()->where('status', 'pending')->count();
        $orgsActive = Organization::query()->where('status', 'active')->count();
        $usersTotal = User::query()->count();
        $usersActive = User::query()->where('status', 'active')->count();
        $evaluationAverage = ServiceEvaluation::query()->avg('rating');
        $evaluationCount = ServiceEvaluation::query()->count();

        return [
            Stat::make(__('widgets.stats.organizations_pending'), (string) $orgsPending)
                ->description(__('widgets.stats.description_pending'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($orgsPending > 0 ? 'warning' : 'gray'),

            Stat::make(__('widgets.stats.organizations_active'), (string) $orgsActive)
                ->description(__('widgets.stats.organizations_total').': '.$orgsTotal)
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),

            Stat::make(__('widgets.stats.users_active'), (string) $usersActive)
                ->description(__('widgets.stats.users_total').': '.$usersTotal)
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('تقييم الخدمة', $evaluationAverage ? number_format((float) $evaluationAverage, 1).'/5' : '0/5')
                ->description('إجمالي التقييمات: '.$evaluationCount)
                ->descriptionIcon('heroicon-m-sparkles')
                ->color($evaluationAverage >= 4 ? 'success' : ($evaluationAverage ? 'warning' : 'gray')),
        ];
    }
}
