<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** @var array<int, string> */
    protected array $consultantSpecializations = [];

    protected bool $consultantRoleSelected = false;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['consultant_specializations'] = $this->getRecord()
            ->consultantSpecializations()
            ->pluck('specialization')
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->consultantSpecializations = array_values((array) ($this->data['consultant_specializations'] ?? []));
        $this->consultantRoleSelected = $this->roleIsSelected((array) ($this->data['roles'] ?? []));

        unset($data['consultant_specializations']);

        return $data;
    }

    protected function afterSave(): void
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

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
