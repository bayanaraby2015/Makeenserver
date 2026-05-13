<?php

namespace App\Policies;

use App\Models\MonthlyReport;
use App\Models\User;

class MonthlyReportPolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole([
            config('makeen.roles.super_admin'),
            config('makeen.roles.excellence_manager'),
        ]);
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            config('makeen.roles.super_admin'),
            config('makeen.roles.excellence_manager'),
            config('makeen.roles.excellence_member'),
            config('makeen.roles.consultant'),
            config('makeen.roles.association_manager'),
            config('makeen.roles.association_member'),
        ]);
    }

    public function view(User $user, MonthlyReport $report): bool
    {
        if ($this->isAdmin($user) || $user->hasRole(config('makeen.roles.excellence_member'))) {
            return true;
        }

        if ($user->hasRole(config('makeen.roles.consultant'))) {
            return (int) $report->consultant_user_id === (int) $user->id;
        }

        if ($user->hasAnyRole([
            config('makeen.roles.association_manager'),
            config('makeen.roles.association_member'),
        ])) {
            return $report->initiative !== null
                && (int) $report->initiative->organization_id === (int) $user->primary_organization_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            config('makeen.roles.super_admin'),
            config('makeen.roles.excellence_manager'),
            config('makeen.roles.consultant'),
        ]);
    }

    public function update(User $user, MonthlyReport $report): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($user->hasRole(config('makeen.roles.consultant'))) {
            return (int) $report->consultant_user_id === (int) $user->id;
        }

        return false;
    }

    public function delete(User $user, MonthlyReport $report): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, MonthlyReport $report): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, MonthlyReport $report): bool
    {
        return $user->hasRole(config('makeen.roles.super_admin'));
    }
}
