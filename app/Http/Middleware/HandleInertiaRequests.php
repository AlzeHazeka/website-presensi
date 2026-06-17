<?php

namespace App\Http\Middleware;

use App\Support\RoleAccess;
use App\Support\RoleCatalog;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        $authUser = $request->user();
        $can = static function ($user, string $permission): bool {
            if (! $user || ! method_exists($user, 'can')) return false;
            try {
                return (bool) $user->can($permission);
            } catch (\Throwable) {
                return false;
            }
        };

        $isSuperAdmin = $authUser ? $can($authUser, Permissions::SYSTEM_MANAGE) : false;
        $canViewPayroll = $authUser ? $can($authUser, Permissions::PAYROLL_VIEW) : false;
        $canAccessDailyPayroll = $authUser
            ? RoleAccess::userHasAnyRole($authUser, Roles::adminRoles())
            : false;

        return array_merge(parent::share($request), [
            'userRoles' => fn () => RoleCatalog::availableRoleNames(),
            'authz' => [
                'isSuperAdmin' => fn () => $isSuperAdmin,
                'canViewUsers' => fn () => $authUser ? $can($authUser, Permissions::USERS_VIEW) : false,
                'canManageUsers' => fn () => $authUser ? ($can($authUser, Permissions::USERS_CREATE) || $can($authUser, Permissions::USERS_EDIT) || $can($authUser, Permissions::USERS_DELETE)) : false,

                'canManageProfile' => fn () => $authUser ? $can($authUser, Permissions::PROFILE_MANAGE) : false,
                'canResetPassword' => fn () => $authUser ? $can($authUser, Permissions::PASSWORD_RESET) : false,
                'canViewPayroll' => fn () => $authUser ? $can($authUser, Permissions::PAYROLL_VIEW) : false,
                'canAccessDailyPayroll' => fn () => $canAccessDailyPayroll,
                'canManagePayroll' => fn () => $authUser ? $can($authUser, Permissions::PAYROLL_MANAGE) : false,
                'canProfileView' => fn () => $authUser ? $can($authUser, Permissions::PROFILE_VIEW) : false,

                'canViewReportDaily' => fn () => $authUser ? $can($authUser, Permissions::REPORT_DAILY_VIEW) : false,
                'canViewReportByUser' => fn () => $authUser ? $can($authUser, Permissions::REPORT_BY_USER_VIEW) : false,
                'canViewReportMonthly' => fn () => $authUser ? $can($authUser, Permissions::REPORT_MONTHLY_VIEW) : false,
                'canExportReportPdf' => fn () => $authUser ? $can($authUser, Permissions::REPORT_EXPORT_PDF) : false,
                'canExportReportExcel' => fn () => $authUser ? $can($authUser, Permissions::REPORT_EXPORT_EXCEL) : false,

                'canManualPresensi' => fn () => $authUser ? $can($authUser, Permissions::PRESENSI_MANUAL) : false,
                'canEditPresensi' => fn () => $authUser ? $can($authUser, Permissions::PRESENSI_EDIT) : false,

                'canPresensiView' => fn () => $authUser ? $can($authUser, Permissions::PRESENSI_VIEW) : false,
                'canPresensiCreate' => fn () => $authUser ? $can($authUser, Permissions::PRESENSI_CREATE) : false,
                'canIzinView' => fn () => $authUser ? $can($authUser, Permissions::IZIN_VIEW) : false,
                'canIzinCreate' => fn () => $authUser ? $can($authUser, Permissions::IZIN_CREATE) : false,
                'canLemburView' => fn () => $authUser ? $can($authUser, Permissions::LEMBUR_VIEW) : false,
                'canLemburCreate' => fn () => $authUser ? $can($authUser, Permissions::LEMBUR_CREATE) : false,
            ],
            'auth' => [
                'user' => fn () => $authUser
                    ? array_merge(
                        $authUser->only([
                            'user_id',
                            'nama',
                            'email',
                            'username',
                            'role',
                            'status',
                            'posisi',
                            'tanggal_masuk',
                        ]),
                        [
                            'profile_photo_url' => $authUser->profile_photo_url ?? null,
                            'spatie_roles' => RoleAccess::getUserRoleNames($authUser),
                            'payroll' => $canViewPayroll
                                ? [
                                    'gaji' => $authUser->gaji ?? null,
                                    'tipe_gaji' => $authUser->tipe_gaji ?? null,
                                ]
                                : null,
                        ]
                    )
                    : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'banner' => fn () => $request->session()->get('flash.banner'),
                'bannerStyle' => fn () => $request->session()->get('flash.bannerStyle'),
            ],
        ]);
    }
}
