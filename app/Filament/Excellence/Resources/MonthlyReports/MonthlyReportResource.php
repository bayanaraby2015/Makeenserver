<?php

namespace App\Filament\Excellence\Resources\MonthlyReports;

use App\Filament\Excellence\Resources\MonthlyReports\Pages\ListMonthlyReports;
use App\Filament\Excellence\Resources\MonthlyReports\Pages\ViewMonthlyReport;
use App\Filament\Resources\MonthlyReports\MonthlyReportResource as BaseMonthlyReportResource;

class MonthlyReportResource extends BaseMonthlyReportResource
{
    protected static ?string $slug = 'monthly-reports';

    protected static ?int $navigationSort = 50;

    public static function getPages(): array
    {
        return [
            'index' => ListMonthlyReports::route('/'),
            'view' => ViewMonthlyReport::route('/{record}'),
        ];
    }
}
