<?php

namespace App\Filament\Excellence\Resources\Initiatives\Pages;

use App\Filament\Excellence\Resources\Initiatives\InitiativeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInitiatives extends ListRecords
{
    protected static string $resource = InitiativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
