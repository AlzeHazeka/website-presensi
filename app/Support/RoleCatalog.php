<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class RoleCatalog
{
    /**
     * @return array<int, string>
     */
    public static function availableRoleNames(): array
    {
        if (! RoleAccess::spatieReady()) {
            return Roles::all();
        }

        try {
            return Cache::remember('role_catalog.available_role_names', now()->addMinutes(10), function () {
                /** @var array<int, string> $names */
                $names = \Spatie\Permission\Models\Role::query()
                    ->where('guard_name', 'web')
                    ->orderBy('name')
                    ->pluck('name')
                    ->all();

                // Keep it compatible with legacy/hybrid mode: prefer configured roles order.
                $configured = Roles::all();
                $configuredSet = array_flip($configured);
                $filtered = array_values(array_filter($names, static fn ($n) => isset($configuredSet[$n])));

                return $filtered !== [] ? $filtered : $configured;
            });
        } catch (\Throwable) {
            return Roles::all();
        }
    }
}

