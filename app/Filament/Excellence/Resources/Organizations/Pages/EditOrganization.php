<?php

namespace App\Filament\Excellence\Resources\Organizations\Pages;

use App\Filament\Excellence\Resources\Organizations\OrganizationResource;
use App\Filament\Resources\Organizations\Pages\EditOrganization as BaseEditOrganization;

class EditOrganization extends BaseEditOrganization
{
    protected static string $resource = OrganizationResource::class;
}
