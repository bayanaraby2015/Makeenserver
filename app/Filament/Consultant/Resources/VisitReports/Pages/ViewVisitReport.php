<?php

namespace App\Filament\Consultant\Resources\VisitReports\Pages;

use App\Filament\Consultant\Resources\VisitReports\VisitReportResource;
use App\Filament\Support\ServiceEvaluationAction;
use App\Models\VisitReport;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewVisitReport extends ViewRecord
{
    protected static string $resource = VisitReportResource::class;

    protected string $view = 'filament.visit-reports.consultant-view';

    protected function getHeaderActions(): array
    {
        return [
            ServiceEvaluationAction::make('visit_report', fn (VisitReport $record): ?int => $record->organization_id),
            EditAction::make(),
        ];
    }
}
