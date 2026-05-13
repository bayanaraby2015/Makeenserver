<?php

namespace App\Filament\Excellence\Resources\Initiatives\Pages;

use App\Filament\Excellence\Resources\Initiatives\InitiativeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInitiative extends EditRecord
{
    protected static string $resource = InitiativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
