<?php

namespace App\Filament\Excellence\Resources\VisitReports;

use App\Filament\Excellence\Resources\VisitReports\Pages\ListVisitReports;
use App\Filament\Excellence\Resources\VisitReports\Pages\ViewVisitReport;
use App\Filament\Resources\VisitReports\VisitReportResource as BaseVisitReportResource;

class VisitReportResource extends BaseVisitReportResource
{
    protected static ?string $slug = 'visit-reports';

    protected static ?int $navigationSort = 40;

    public static function getPages(): array
    {
        return [
            'index' => ListVisitReports::route('/'),
            'view' => ViewVisitReport::route('/{record}'),
        ];
    }
}
