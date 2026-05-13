<?php

namespace App\Filament\Donor\Resources\Initiatives\Pages;

use App\Filament\Donor\Resources\Initiatives\InitiativeResource;
use Filament\Resources\Pages\ListRecords;

class ListInitiatives extends ListRecords
{
    protected static string $resource = InitiativeResource::class;
}
