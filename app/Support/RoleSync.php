<?php

namespace App\Support;

use App\Models\User;

final class RoleSync
{
    public static function sync(User $user, ?string $role): void
    {
        $role = trim((string) $role);
        if ($role === '') {
            return;
        }

        if (! in_array($role, RoleCatalog::availableRoleNames(), true)) {
            return;
        }

        // Legacy enum sync (non-destructive)
        if (($user->role ?? null) !== $role) {
            $user->forceFill(['role' => $role])->save();
        }

        // Spatie sync (safe during staged rollout)
        if (! RoleAccess::spatieReady() || ! method_exists($user, 'syncRoles')) {
            return;
        }

        try {
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                \Spatie\Permission\Models\Role::findOrCreate($role, $user->getDefaultGuardName());
            }
        } catch (\Throwable) {
            // ignore - will try syncRoles anyway
        }

        try {
            $user->syncRoles([$role]);
        } catch (\Throwable) {
            // ignore - do not break legacy flow during rollout
        }
    }
}
