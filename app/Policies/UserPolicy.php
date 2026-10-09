<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::MANAGE_ADMINISTRATORS)
            || $user->hasPermission(Permission::MANAGE_USERS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::MANAGE_ADMINISTRATORS);
    }

    public function update(User $user, User $target): bool
    {
        if (! $user->hasPermission(Permission::MANAGE_ADMINISTRATORS)) {
            return false;
        }

        // SECURITY: a non-super-admin must never be able to edit, reset the
        // password of, or deactivate a Super Admin (account takeover).
        if ($target->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, User $target): bool
    {
        if (! $user->hasPermission(Permission::MANAGE_ADMINISTRATORS)) {
            return false;
        }

        if ($target->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        // Never allow deleting yourself or the primary/first super admin account.
        if ($target->id === $user->id) {
            return false;
        }

        if ($target->isSuperAdmin() && $target->id === $this->primarySuperAdminId()) {
            return false;
        }

        return true;
    }

    public function assignRoles(User $user, User $target): bool
    {
        return $user->hasPermission(Permission::MANAGE_ROLES);
    }

    private function primarySuperAdminId(): ?int
    {
        return User::where('is_super_admin', true)->oldest('id')->value('id');
    }
}
