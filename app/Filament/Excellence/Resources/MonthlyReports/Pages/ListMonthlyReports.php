<?php

namespace App\Filament\Excellence\Resources\MonthlyReports\Pages;

use App\Filament\Excellence\Resources\MonthlyReports\MonthlyReportResource;
use App\Filament\Resources\MonthlyReports\Pages\ListMonthlyReports as BaseListMonthlyReports;

class ListMonthlyReports extends BaseListMonthlyReports
{
    protected static string $resource = MonthlyReportResource::class;
}
