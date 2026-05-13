<?php

namespace App\Filament\Consultant\Resources\MonthlyReports\Pages;

use App\Filament\Consultant\Resources\MonthlyReports\MonthlyReportResource;
use App\Filament\Support\ServiceEvaluationAction;
use App\Models\MonthlyReport;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMonthlyReport extends ViewRecord
{
    protected static string $resource = MonthlyReportResource::class;

    protected string $view = 'filament.monthly-reports.consultant-view';

    protected function getHeaderActions(): array
    {
        return [
            ServiceEvaluationAction::make('monthly_report', fn (MonthlyReport $record): ?int => $record->organization_id),
            EditAction::make(),
        ];
    }
}
