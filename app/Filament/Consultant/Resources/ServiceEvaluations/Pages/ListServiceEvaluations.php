<?php

namespace App\Filament\Consultant\Resources\ServiceEvaluations\Pages;

use App\Filament\Consultant\Resources\ServiceEvaluations\ServiceEvaluationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceEvaluations extends ListRecords
{
    protected static string $resource = ServiceEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
