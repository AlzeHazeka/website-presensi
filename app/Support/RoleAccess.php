<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Schema;

final class RoleAccess
{
    public static function spatieReady(): bool
    {
        if (! class_exists(\Spatie\Permission\Models\Role::class)) {
            return false;
        }

        try {
            return Schema::hasTable('roles') && Schema::hasTable('model_has_roles');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<int, string>
     */
    public static function getUserRoleNames(User $user): array
    {
        if (! self::spatieReady() || ! method_exists($user, 'getRoleNames')) {
            return [];
        }

        try {
            /** @var \Illuminate\Support\Collection<int, string> $roles */
            $roles = $user->getRoleNames();
            return array_values($roles->all());
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Hybrid check: prefer Spatie role check, fallback to legacy enum column.
     *
     * @param  array<int, string>  $roles
     */
    public static function userHasAnyRole(User $user, array $roles): bool
    {
        $roles = array_values(array_filter(array_map('trim', $roles)));
        if ($roles === []) {
            return false;
        }

        if (self::spatieReady() && method_exists($user, 'hasAnyRole')) {
            try {
                if ($user->hasAnyRole($roles)) {
                    return true;
                }
            } catch (\Throwable) {
                // Fallback to legacy column below
            }
        }

        return in_array(($user->role ?? null), $roles, true);
    }
}

