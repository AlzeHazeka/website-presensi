<?php

namespace App\Support;

final class Permissions
{
    public const USERS_VIEW = 'users.view';
    public const USERS_CREATE = 'users.create';
    public const USERS_EDIT = 'users.edit';
    public const USERS_DELETE = 'users.delete';

    public const PROFILE_VIEW = 'profile.view';
    public const PROFILE_MANAGE = 'profile.manage';

    public const PASSWORD_RESET = 'password.reset';

    public const PRESENSI_VIEW = 'presensi.view';
    public const PRESENSI_CREATE = 'presensi.create';
    public const PRESENSI_MANUAL = 'presensi.manual';
    public const PRESENSI_EDIT = 'presensi.edit';
    public const PRESENSI_DELETE = 'presensi.delete';

    public const IZIN_VIEW = 'izin.view';
    public const IZIN_CREATE = 'izin.create';
    public const IZIN_EDIT = 'izin.edit';
    public const IZIN_DELETE = 'izin.delete';

    public const LEMBUR_VIEW = 'lembur.view';
    public const LEMBUR_CREATE = 'lembur.create';
    public const LEMBUR_EDIT = 'lembur.edit';
    public const LEMBUR_DELETE = 'lembur.delete';

    public const REPORT_DAILY_VIEW = 'report.daily.view';
    public const REPORT_BY_USER_VIEW = 'report.by-user.view';
    public const REPORT_MONTHLY_VIEW = 'report.monthly.view';

    public const REPORT_EXPORT_PDF = 'report.export.pdf';
    public const REPORT_EXPORT_EXCEL = 'report.export.excel';

    public const PAYROLL_VIEW = 'payroll.view';
    public const PAYROLL_MANAGE = 'payroll.manage';

    public const ROLES_MANAGE = 'roles.manage';
    public const PERMISSIONS_MANAGE = 'permissions.manage';
    public const SYSTEM_MANAGE = 'system.manage';

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
            self::USERS_VIEW,
            self::USERS_CREATE,
            self::USERS_EDIT,
            self::USERS_DELETE,

            self::PROFILE_VIEW,
            self::PROFILE_MANAGE,

            self::PASSWORD_RESET,

            self::PRESENSI_VIEW,
            self::PRESENSI_CREATE,
            self::PRESENSI_MANUAL,
            self::PRESENSI_EDIT,
            self::PRESENSI_DELETE,

            self::IZIN_VIEW,
            self::IZIN_CREATE,
            self::IZIN_EDIT,
            self::IZIN_DELETE,

            self::LEMBUR_VIEW,
            self::LEMBUR_CREATE,
            self::LEMBUR_EDIT,
            self::LEMBUR_DELETE,

            self::REPORT_DAILY_VIEW,
            self::REPORT_BY_USER_VIEW,
            self::REPORT_MONTHLY_VIEW,

            self::REPORT_EXPORT_PDF,
            self::REPORT_EXPORT_EXCEL,

            self::PAYROLL_VIEW,
            self::PAYROLL_MANAGE,

            self::ROLES_MANAGE,
            self::PERMISSIONS_MANAGE,
            self::SYSTEM_MANAGE,
        ];
    }

    public static function isKnown(string $permission): bool
    {
        return in_array($permission, self::all(), true);
    }

    /**
     * Fallback permissions for legacy role column (hybrid rollout).
     *
     * @return array<int, string>
     */
    public static function forLegacyRole(?string $role): array
    {
        $role = trim((string) $role);

        if ($role === Roles::SUPER_ADMIN) {
            return self::all();
        }

        if ($role === Roles::ADMIN) {
            return [
                self::USERS_VIEW,
                self::PROFILE_VIEW,

                self::PRESENSI_VIEW,
                self::PRESENSI_CREATE,

                self::IZIN_VIEW,
                self::IZIN_CREATE,

                self::LEMBUR_VIEW,
                self::LEMBUR_CREATE,

                self::REPORT_DAILY_VIEW,
                self::REPORT_BY_USER_VIEW,
                self::REPORT_MONTHLY_VIEW,

                self::REPORT_EXPORT_PDF,
                self::REPORT_EXPORT_EXCEL,

                self::PAYROLL_VIEW,
            ];
        }

        // Default: karyawan
        return [
            self::PROFILE_VIEW,
            self::PRESENSI_VIEW,
            self::PRESENSI_CREATE,
            self::IZIN_VIEW,
            self::IZIN_CREATE,
            self::LEMBUR_VIEW,
            self::LEMBUR_CREATE,
        ];
    }
}
