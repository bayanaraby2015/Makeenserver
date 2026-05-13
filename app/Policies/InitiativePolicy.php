<?php

namespace App\Policies;

use App\Models\Initiative;
use App\Models\User;

class InitiativePolicy
{
    /**
     * Returns true for an administrator who may bypass per-record checks.
     */
    private function isAdmin(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Returns true if the user belongs to the association that owns the initiative.
     */
    private function ownsInitiative(User $user, Initiative $initiative): bool
    {
        return $user->primary_organization_id !== null
            && (int) $user->primary_organization_id === (int) $initiative->organization_id;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Initiative $initiative): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        // Excellence/donor can view; association can view its own; consultant scoped separately.
        if ($user->hasAnyRole(['excellence_manager', 'excellence_member'])) {
            return true;
        }

        if ($user->hasAnyRole(['donor_admin'])) {
            return $initiative->status === 'approved';
        }

        if ($user->hasAnyRole(['association_manager', 'association_member'])) {
            return $this->ownsInitiative($user, $initiative);
        }

        if ($user->hasRole('consultant')) {
            $specializations = $user->consultantSpecializations()->pluck('specialization')->all();

            if ($specializations === []) {
                return false;
            }

            foreach ($specializations as $specialization) {
                if (in_array($specialization, $initiative->specializations ?? [], true) || $initiative->domain === $specialization) {
                    return true;
                }
            }
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user)
            || $user->hasRole('excellence_manager')
            || $user->hasAnyRole(['association_manager', 'association_member']);
    }

    public function update(User $user, Initiative $initiative): bool
    {
        if ($this->isAdmin($user) || $user->hasRole('excellence_manager')) {
            return true;
        }

        if ($user->hasAnyRole(['association_manager', 'association_member'])) {
            return $this->ownsInitiative($user, $initiative)
                && in_array($initiative->status, ['draft', 'revisions_requested'], true);
        }

        return false;
    }

    public function delete(User $user, Initiative $initiative): bool
    {
        if ($this->isAdmin($user) || $user->hasRole('excellence_manager')) {
            return true;
        }

        if ($user->hasAnyRole(['association_manager', 'association_member'])) {
            return $this->ownsInitiative($user, $initiative)
                && $initiative->status === 'draft';
        }

        return false;
    }

    public function submit(User $user, Initiative $initiative): bool
    {
        return $user->hasAnyRole(['association_manager', 'association_member'])
            && $this->ownsInitiative($user, $initiative)
            && in_array($initiative->status, ['draft', 'revisions_requested'], true);
    }

    public function review(User $user, Initiative $initiative): bool
    {
        return $this->isAdmin($user) || $user->hasRole('excellence_manager');
    }

    public function restore(User $user, Initiative $initiative): bool
    {
        return $this->isAdmin($user) || $user->hasRole('excellence_manager');
    }

    public function forceDelete(User $user, Initiative $initiative): bool
    {
        return $this->isAdmin($user) || $user->hasRole('excellence_manager');
    }
}
