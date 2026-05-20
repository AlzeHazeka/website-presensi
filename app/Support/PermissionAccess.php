<?php

namespace App\Support;

use App\Models\User;

final class PermissionAccess
{
    /**
     * Hybrid permission check:
     * - Prefer Spatie permission tables when available.
     * - Fallback to legacy role column mapping during rollout.
     */
    public static function userCan(User $user, string $permission): bool
    {
        $permission = trim($permission);
        if ($permission === '') {
            return false;
        }

        $legacyAllowed = in_array($permission, Permissions::forLegacyRole($user->role ?? null), true);

        // Hybrid (safe rollout):
        // - Prefer Spatie permission tables when available.
        // - OR with legacy mapping to avoid accidental lockout when Spatie data is incomplete.
        if (RoleAccess::spatieReady() && method_exists($user, 'checkPermissionTo')) {
            try {
                return (bool) $user->checkPermissionTo($permission) || $legacyAllowed;
            } catch (\Throwable) {
                return $legacyAllowed;
            }
        }

        return $legacyAllowed;
    }

    /**
     * @param  array<int, string>  $permissions
     */
    public static function userCanAny(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (self::userCan($user, $permission)) {
                return true;
            }
        }
        return false;
    }
}
