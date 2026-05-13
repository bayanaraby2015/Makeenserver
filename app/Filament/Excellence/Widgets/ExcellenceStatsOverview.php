<?php

namespace App\Filament\Excellence\Widgets;

use App\Models\Consultation;
use App\Models\Initiative;
use App\Models\Organization;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExcellenceStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = null;

    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $pending = Initiative::query()->whereIn('status', ['submitted', 'under_review'])->count();
        $approved = Initiative::query()->where('status', 'approved')->count();
        $needsAction = Initiative::query()->whereIn('status', ['submitted', 'under_review', 'revisions_requested'])->count();
        $consultations = Consultation::query()->whereIn('status', ['requested', 'accepted', 'scheduled'])->count();
        $organizations = Organization::query()->where('status', 'active')->count();

        return [
            Stat::make('مبادرات قيد المتابعة', (string) $pending)
                ->description('تحتاج مراجعة أو اعتماد')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pending > 0 ? 'warning' : 'gray'),

            Stat::make('مبادرات معتمدة', (string) $approved)
                ->description('جاهزة للتنفيذ والمتابعة')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('استشارات نشطة', (string) $consultations)
                ->description('طلبات وجلسات لم تغلق بعد')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($consultations > 0 ? 'info' : 'gray'),

            Stat::make('جهات مشاركة', (string) $organizations)
                ->description('إجمالي عناصر المتابعة: '.$needsAction)
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),
        ];
    }
}
