<?php

namespace App\Filament\Consultant\Resources\MonthlyReports\Pages;

use App\Filament\Consultant\Resources\MonthlyReports\MonthlyReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMonthlyReports extends ListRecords
{
    protected static string $resource = MonthlyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
