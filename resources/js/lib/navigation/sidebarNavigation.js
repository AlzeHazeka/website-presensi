/**
 * Sidebar navigation source-of-truth.
 * Desktop & mobile should render from this exact structure.
 */
export function buildSidebarNavigation({ route, authz }) {
    const can = (key) => Boolean(authz?.[key]);
    const canViewAnyReports = can('canViewReportDaily') || can('canViewReportByUser') || can('canViewReportMonthly');

    const groups = [
        {
            id: 'main',
            label: null,
            items: [
                {
                    id: 'dashboard',
                    type: 'link',
                    label: 'Dashboard',
                    icon: 'home',
                    href: safeRoute(route, 'dashboard', '/dashboard'),
                },
            ],
        },
    ];

    if (can('canViewUsers') || canViewAnyReports || can('canManualPresensi') || can('canEditPresensi') || can('canAccessDailyPayroll')) {
        groups.push({
            id: 'master',
            label: 'Master',
            items: [
                ...(can('canViewUsers')
                    ? [
                        {
                            id: 'user-management',
                            type: 'link',
                            label: 'User Management',
                            icon: 'users',
                            href: safeRoute(route, 'data-user.index', '/data-user/users'),
                        },
                    ]
                    : []),
                ...(canViewAnyReports || can('canManualPresensi')
                    ? [
                        {
                            id: 'rekap-presensi',
                            type: 'dropdown',
                            label: 'Rekap Presensi',
                            icon: 'clipboard',
                            href: safeRoute(route, 'admin.presensi.index', '/admin/presensi'),
                            children: [
                                ...(can('canViewReportDaily')
                                    ? [
                                        {
                                            id: 'rekap-by-date',
                                            type: 'link',
                                            label: 'Berdasarkan Tanggal',
                                            icon: 'calendar',
                                            href: safeRoute(route, 'admin.presensi.by-date', '/admin/presensi/by-date'),
                                        },
                                    ]
                                    : []),
                                ...(can('canViewReportByUser')
                                    ? [
                                        {
                                            id: 'rekap-by-user',
                                            type: 'link',
                                            label: 'Berdasarkan Karyawan',
                                            icon: 'user',
                                            href: safeRoute(route, 'admin.presensi.by-user', '/admin/presensi/by-user'),
                                        },
                                    ]
                                    : []),
                                ...(can('canViewReportMonthly')
                                    ? [
                                        {
                                            id: 'rekap-presensi-bulanan',
                                            type: 'link',
                                            label: 'Rekap Presensi',
                                            icon: 'clipboard',
                                            href: safeRoute(route, 'admin.presensi.rekap.presensi', '/admin/presensi/rekap'),
                                        },
                                    ]
                                    : []),
                                ...(can('canManualPresensi')
                                    ? [
                                        {
                                            id: 'rekap-manual',
                                            type: 'link',
                                            label: 'Input Manual',
                                            icon: 'plus',
                                            href: safeRoute(route, 'admin.presensi.create', '/admin/presensi/create'),
                                        },
                                    ]
                                    : []),
                            ],
                        },
                    ]
                    : []),
                ...(can('canAccessDailyPayroll')
                    ? [
                        {
                            id: 'payroll-daily',
                            type: 'link',
                            label: 'Hitung Gaji Harian',
                            icon: 'cash',
                            href: safeRoute(route, 'payroll.daily.index', '/payroll/daily'),
                        },
                    ]
                    : []),
            ],
        });
    }

    const presensiItems = [
        ...(can('canPresensiView')
            ? [
                {
                    id: 'presensi',
                    type: 'link',
                    label: 'Presensi',
                    icon: 'calendar',
                    href: safeRoute(route, 'presensi.index', '/presensi'),
                },
                {
                    id: 'riwayat-presensi',
                    type: 'link',
                    label: 'Riwayat Presensi',
                    icon: 'clock',
                    href: safeRoute(route, 'presensi.riwayat', '/presensi/riwayat'),
                },
            ]
            : []),
        ...(can('canIzinView')
            ? [
                {
                    id: 'izin',
                    type: 'link',
                    label: 'Izin',
                    icon: 'document',
                    href: safeRoute(route, 'izin.index', '/izin'),
                },
            ]
            : []),
        ...(can('canLemburView')
            ? [
                {
                    id: 'lembur',
                    type: 'link',
                    label: 'Lembur',
                    icon: 'clock',
                    href: safeRoute(route, 'lembur.index', '/lembur'),
                },
            ]
            : []),
    ];

    if (presensiItems.length) {
        groups.push({
            id: 'presensi',
            label: 'Presensi',
            items: presensiItems,
        });
    }

    if (can('canProfileView')) {
        groups.push({
            id: 'account',
            label: 'Akun',
            items: [
                {
                    id: 'profile',
                    type: 'link',
                    label: 'Profile',
                    icon: 'profile',
                    href: safeRoute(route, 'profile.show', '/user/profile'),
                },
            ],
        });
    }

    return groups;
}

function safeRoute(routeFn, name, fallback) {
    try {
        return routeFn(name);
    } catch {
        return fallback;
    }
}
