<?php

namespace App\Policies;

use App\Models\ServiceEvaluation;
use App\Models\User;

class ServiceEvaluationPolicy
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

    public function view(User $user, ServiceEvaluation $evaluation): bool
    {
        if ($this->isAdmin($user) || $user->hasRole(config('makeen.roles.excellence_member'))) {
            return true;
        }

        if ((int) $evaluation->evaluator_id === (int) $user->id) {
            return true;
        }

        if ($user->hasAnyRole([
            config('makeen.roles.association_manager'),
            config('makeen.roles.association_member'),
        ])) {
            return (int) $evaluation->organization_id === (int) $user->primary_organization_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            config('makeen.roles.super_admin'),
            config('makeen.roles.excellence_manager'),
            config('makeen.roles.association_manager'),
            config('makeen.roles.association_member'),
        ]);
    }

    public function update(User $user, ServiceEvaluation $evaluation): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return (int) $evaluation->evaluator_id === (int) $user->id;
    }

    public function delete(User $user, ServiceEvaluation $evaluation): bool
    {
        return $this->isAdmin($user);
    }
}
