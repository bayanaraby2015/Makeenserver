<?php

namespace App\Filament\Resources\MonthlyReports\Pages;

use App\Filament\Resources\MonthlyReports\MonthlyReportResource;
use App\Filament\Support\ServiceEvaluationAction;
use App\Models\MonthlyReport;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewMonthlyReport extends ViewRecord
{
    protected static string $resource = MonthlyReportResource::class;

    protected string $view = 'filament.monthly-reports.admin-view';

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            ServiceEvaluationAction::make('monthly_report', fn (MonthlyReport $record): ?int => $record->organization_id),
        ];
    }
}
