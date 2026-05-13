<?php

namespace App\Filament\Excellence\Resources\VisitReports\Pages;

use App\Filament\Excellence\Resources\VisitReports\VisitReportResource;
use App\Filament\Resources\VisitReports\Pages\ListVisitReports as BaseListVisitReports;

class ListVisitReports extends BaseListVisitReports
{
    protected static string $resource = VisitReportResource::class;
}
