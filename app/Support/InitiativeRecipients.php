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
        return self::associationUsers($initiative)
            ->merge(self::reviewUsers())
            ->merge(self::consultants($initiative))
            ->unique('id')
            ->values();
    }

    /** @return Collection<int, User> Association manager/member for the owning organization. */
    public static function associationUsers(Initiative $initiative): Collection
    {
        return User::query()
            ->where('primary_organization_id', $initiative->organization_id)
            ->where('status', 'active')
            ->role([
                config('makeen.roles.association_manager'),
                config('makeen.roles.association_member'),
            ])
            ->get();
    }

    /** @return Collection<int, User> super_admin + excellence team. */
    public static function reviewUsers(): Collection
    {
        return User::role([
            config('makeen.roles.super_admin'),
            config('makeen.roles.excellence_manager'),
            config('makeen.roles.excellence_member'),
        ])
            ->where('status', 'active')
            ->get();
    }

    /** @return Collection<int, User> Consultants whose specialization matches the initiative. */
    public static function consultants(Initiative $initiative): Collection
    {
        $specializations = $initiative->specializations ?? [];

        return User::role(config('makeen.roles.consultant'))
            ->where('status', 'active')
            ->whereHas('consultantSpecializations', function ($query) use ($specializations, $initiative): void {
                $query->whereIn('specialization', $specializations)
                    ->orWhere('specialization', $initiative->domain);
            })
            ->get();
    }

    /** @return Collection<int, User> Admin + excellence (final approval recipients). */
    public static function adminAndExcellence(): Collection
    {
        return self::reviewUsers();
    }
}
