<?php

namespace App\Filament\Resources\VisitReports\Pages;

use App\Filament\Resources\VisitReports\VisitReportResource;
use App\Filament\Support\ServiceEvaluationAction;
use App\Models\VisitReport;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewVisitReport extends ViewRecord
{
    protected static string $resource = VisitReportResource::class;

    protected string $view = 'filament.visit-reports.admin-view';

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            ServiceEvaluationAction::make('visit_report', fn (VisitReport $record): ?int => $record->organization_id),
        ];
    }
}
