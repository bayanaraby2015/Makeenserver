<?php

namespace App\Filament\Association\Pages;

use App\Filament\Association\Widgets\AssociationInitiativeStatusChart;
use App\Filament\Association\Widgets\AssociationExecutiveOverviewWidget;
use App\Filament\Association\Widgets\AssociationStatsOverview;
use App\Filament\Association\Widgets\AssociationWorkQueueWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string
    {
        return 'لوحة الجهة';
    }

    public function getHeading(): string
    {
        return 'لوحة الجهة';
    }

    public function getSubheading(): ?string
    {
        return 'متابعة مبادرات واستشارات الجهة المرتبطة بحسابك.';
    }

    public function getWidgets(): array
    {
        return [
            AssociationExecutiveOverviewWidget::class,
            AssociationStatsOverview::class,
            AssociationInitiativeStatusChart::class,
            AssociationWorkQueueWidget::class,
        ];
    }
}
