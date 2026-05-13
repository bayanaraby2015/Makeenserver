<?php

namespace App\Filament\Consultant\Pages;

use App\Filament\Consultant\Widgets\ConsultantExecutiveOverviewWidget;
use App\Filament\Consultant\Widgets\ConsultantInitiativeStatusChart;
use App\Filament\Consultant\Widgets\ConsultantStatsOverview;
use App\Filament\Consultant\Widgets\ConsultantWorkQueueWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string
    {
        return 'لوحة المستشار';
    }

    public function getHeading(): string
    {
        return 'لوحة المستشار';
    }

    public function getSubheading(): ?string
    {
        return 'المبادرات والاستشارات المرتبطة بتخصصاتك فقط.';
    }

    public function getWidgets(): array
    {
        return [
            ConsultantExecutiveOverviewWidget::class,
            ConsultantStatsOverview::class,
            ConsultantInitiativeStatusChart::class,
            ConsultantWorkQueueWidget::class,
        ];
    }
}
