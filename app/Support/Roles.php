<?php

namespace App\Support;

final class Roles
{
    public const SUPER_ADMIN = 'Super Admin';
    public const ADMIN = 'Admin';
    public const KARYAWAN = 'Karyawan';

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [self::SUPER_ADMIN, self::ADMIN, self::KARYAWAN];
    }

    /**
     * @return array<int, string>
     */
    public static function adminRoles(): array
    {
        return [self::SUPER_ADMIN, self::ADMIN];
    }
}
