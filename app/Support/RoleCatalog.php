<?php

namespace App\Support;

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
            /** @var array<int, string> $names */
            $names = \Spatie\Permission\Models\Role::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->pluck('name')
                ->all();
        } catch (\Throwable) {
            return Roles::all();
        }

        if ($names === []) {
            return Roles::all();
        }

        $priority = array_flip(Roles::all());

        usort($names, static function (string $left, string $right) use ($priority): int {
            $leftPriority = $priority[$left] ?? PHP_INT_MAX;
            $rightPriority = $priority[$right] ?? PHP_INT_MAX;

            if ($leftPriority === $rightPriority) {
                return strnatcasecmp($left, $right);
            }

            return $leftPriority <=> $rightPriority;
        });

        return array_values(array_unique($names));
    }
}
