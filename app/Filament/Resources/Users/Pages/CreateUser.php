<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** @var array<int, string> */
    protected array $consultantSpecializations = [];

    protected bool $consultantRoleSelected = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->consultantSpecializations = array_values((array) ($this->data['consultant_specializations'] ?? []));
        $this->consultantRoleSelected = $this->roleIsSelected((array) ($this->data['roles'] ?? []));

        unset($data['consultant_specializations']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncConsultantSpecializations($this->getRecord());
    }

    protected function syncConsultantSpecializations(User $user): void
    {
        $user->consultantSpecializations()->delete();

        if (! $this->consultantRoleSelected) {
            return;
        }

        foreach ($this->consultantSpecializations as $specialization) {
            if (! is_string($specialization) || $specialization === '') {
                continue;
            }

            $user->consultantSpecializations()->create([
                'specialization' => $specialization,
            ]);
        }
    }

    protected function roleIsSelected(array $roles): bool
    {
        if (in_array(config('makeen.roles.consultant'), $roles, true)) {
            return true;
        }

        $consultantRoleId = \Spatie\Permission\Models\Role::query()
            ->where('name', config('makeen.roles.consultant'))
            ->value('id');

        return $consultantRoleId !== null
            && in_array((string) $consultantRoleId, array_map('strval', $roles), true);
    }
}
