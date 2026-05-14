<?php

namespace App\Filament\Association\Resources\Initiatives\Pages;

use App\Filament\Association\Resources\Initiatives\InitiativeResource;
use App\Models\Initiative;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
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

    /**
     * Deduplicate near-simultaneous draft saves (double click,
     * Livewire re-fire) by reusing the most recent draft created
     * by the same organization within the last 60 seconds.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $organizationId = $data['organization_id'] ?? null;

        if ($organizationId !== null) {
            $existing = Initiative::query()
                ->where('organization_id', $organizationId)
                ->where('status', 'draft')
                ->where('created_at', '>=', now()->subSeconds(60))
                ->latest('id')
                ->first();

            if ($existing !== null) {
                $existing->fill($data)->save();

                return $existing;
            }
        }

        return static::getModel()::create($data);
    }
}
