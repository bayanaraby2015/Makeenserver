<?php

namespace App\Filament\Association\Resources\Initiatives\Pages;

use App\Filament\Association\Resources\Initiatives\InitiativeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateInitiative extends CreateRecord
{
    protected static string $resource = InitiativeResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['organization_id'] = Auth::user()?->primary_organization_id;
        $data['status'] = 'draft';

        return $data;
    }
}
