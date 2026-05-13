<?php

namespace App\Filament\Excellence\Resources\Organizations;

use App\Filament\Excellence\Resources\Organizations\Pages\EditOrganization;
use App\Filament\Excellence\Resources\Organizations\Pages\ListOrganizations;
use App\Filament\Excellence\Resources\Organizations\Pages\ViewOrganization;
use App\Filament\Resources\Organizations\OrganizationResource as BaseOrganizationResource;

class OrganizationResource extends BaseOrganizationResource
{
    protected static ?string $slug = 'organizations';

    protected static ?int $navigationSort = 30;

    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrganizations::route('/'),
            'view' => ViewOrganization::route('/{record}'),
            'edit' => EditOrganization::route('/{record}/edit'),
        ];
    }
}
