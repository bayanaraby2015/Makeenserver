<?php

namespace App\Filament\Association\Resources\Organization\Pages;

use App\Filament\Association\Resources\Organization\OrganizationResource;
use App\Models\Organization;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Single-record edit page: always loads the current user's
 * primary organization. The route is /association/organization
 * (no record key in the URL).
 */
class EditMyOrganization extends EditRecord
{
    protected static string $resource = OrganizationResource::class;

    public function mount(int|string|null $record = null): void
    {
        $orgId = Auth::user()?->primary_organization_id;

        if ($orgId === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Organization not found.');
        }

        $organization = Organization::query()->find($orgId);

        if ($organization === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Organization not found.');
        }

        parent::mount($organization->getKey());
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
