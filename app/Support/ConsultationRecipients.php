<?php

namespace App\Support;

use App\Models\Consultation;
use App\Models\User;
use Illuminate\Support\Collection;

class ConsultationRecipients
{
    /**
     * @return Collection<int, User>
     */
    public static function consultationDepartment(): Collection
    {
        return User::role([
            config('makeen.roles.super_admin'),
            config('makeen.roles.excellence_manager'),
            config('makeen.roles.excellence_member'),
        ])
            ->where('status', 'active')
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public static function associationUsers(Consultation $consultation): Collection
    {
        $organizationId = $consultation->requester_organization_id;

        if ($organizationId === null) {
            return collect();
        }

        return User::query()
            ->where('primary_organization_id', $organizationId)
            ->where('status', 'active')
            ->role([
                config('makeen.roles.association_manager'),
                config('makeen.roles.association_member'),
            ])
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public static function consultant(Consultation $consultation): Collection
    {
        if ($consultation->consultant_user_id === null) {
            return collect();
        }

        return User::query()
            ->whereKey($consultation->consultant_user_id)
            ->where('status', 'active')
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public static function responsible(Consultation $consultation): Collection
    {
        if ($consultation->responsible_user_id === null) {
            return collect();
        }

        return User::query()
            ->whereKey($consultation->responsible_user_id)
            ->where('status', 'active')
            ->get();
    }
}
