<?php

namespace App\Filament\Excellence\Pages;

use App\Filament\Excellence\Widgets\ExcellenceActivityWidget;
use App\Filament\Excellence\Widgets\ExcellenceExecutiveOverviewWidget;
use App\Filament\Excellence\Widgets\ExcellenceInitiativeStatusChart;
use App\Filament\Excellence\Widgets\ExcellenceInitiativesWidget;
use App\Filament\Excellence\Widgets\ExcellenceStatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string
    {
        return 'لوحة مسار الإجادة';
    }

    public function getHeading(): string
    {
        return 'لوحة مسار الإجادة';
    }

    public function getSubheading(): ?string
    {
        return 'متابعة المبادرات والاستشارات ومؤشرات الأداء حسب صلاحية المستخدم.';
    }

    public function getWidgets(): array
    {
        return [
            ExcellenceExecutiveOverviewWidget::class,
            ExcellenceStatsOverview::class,
            ExcellenceInitiativeStatusChart::class,
            ExcellenceInitiativesWidget::class,
            ExcellenceActivityWidget::class,
        ];
    }
}
