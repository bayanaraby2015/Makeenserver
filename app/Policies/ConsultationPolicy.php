<?php

namespace App\Policies;

use App\Models\Consultation;
use App\Models\User;

class ConsultationPolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'excellence_manager']);
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'super_admin',
            'excellence_manager',
            'excellence_member',
            'association_manager',
            'association_member',
            'consultant',
        ]);
    }

    public function view(User $user, Consultation $consultation): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ((int) $consultation->responsible_user_id === (int) $user->id) {
            return true;
        }

        if ($user->hasRole('consultant')) {
            return (int) $consultation->consultant_user_id === (int) $user->id
                || ($consultation->consultant_user_id === null && $consultation->status === 'requested');
        }

        if ($user->hasAnyRole(['association_manager', 'association_member'])) {
            return (int) $consultation->requester_organization_id === (int) $user->primary_organization_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'excellence_manager', 'excellence_member', 'association_manager', 'association_member']);
    }

    public function update(User $user, Consultation $consultation): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ((int) $consultation->responsible_user_id === (int) $user->id) {
            return true;
        }

        if ($user->hasRole('consultant')) {
            return (int) $consultation->consultant_user_id === (int) $user->id
                || ($consultation->consultant_user_id === null && $consultation->status === 'requested');
        }

        if ($user->hasAnyRole(['association_manager', 'association_member'])) {
            return (int) $consultation->requester_organization_id === (int) $user->primary_organization_id
                && $consultation->status === 'requested';
        }

        return false;
    }

    public function delete(User $user, Consultation $consultation): bool
    {
        return $this->isAdmin($user);
    }
}
