<?php

namespace App\Filament\Consultant\Resources\VisitReports\Pages;

use App\Filament\Consultant\Resources\VisitReports\VisitReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVisitReports extends ListRecords
{
    protected static string $resource = VisitReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
