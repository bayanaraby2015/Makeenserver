<?php

namespace App\Filament\Association\Resources\ServiceEvaluations\Pages;

use App\Filament\Association\Resources\ServiceEvaluations\ServiceEvaluationResource;
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
