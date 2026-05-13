<?php

namespace App\Filament\Excellence\Resources\Organizations\Pages;

use App\Filament\Excellence\Resources\Organizations\OrganizationResource;
use App\Filament\Resources\Organizations\Pages\ListOrganizations as BaseListOrganizations;

class ListOrganizations extends BaseListOrganizations
{
    protected static string $resource = OrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
