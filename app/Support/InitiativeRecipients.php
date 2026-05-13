<?php

namespace App\Support;

use App\Models\Initiative;
use App\Models\User;
use Illuminate\Support\Collection;

class InitiativeRecipients
{
    /**
     * @return Collection<int, User>
     */
    public static function relatedUsers(Initiative $initiative): Collection
    {
        $organizationId = $initiative->organization_id;

        $associationUsers = User::query()
            ->where('primary_organization_id', $organizationId)
            ->where('status', 'active')
            ->role([
                config('makeen.roles.association_manager'),
                config('makeen.roles.association_member'),
            ])
            ->get();

        $reviewUsers = User::role([
            config('makeen.roles.super_admin'),
            config('makeen.roles.excellence_manager'),
            config('makeen.roles.excellence_member'),
        ])
            ->where('status', 'active')
            ->get();

        $specializations = $initiative->specializations ?? [];
        $consultants = User::role(config('makeen.roles.consultant'))
            ->where('status', 'active')
            ->whereHas('consultantSpecializations', function ($query) use ($specializations, $initiative): void {
                $query->whereIn('specialization', $specializations)
                    ->orWhere('specialization', $initiative->domain);
            })
            ->get();

        return $associationUsers
            ->merge($reviewUsers)
            ->merge($consultants)
            ->unique('id')
            ->values();
    }
}
