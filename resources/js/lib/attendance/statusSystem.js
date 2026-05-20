const STATUS_KEYS = Object.freeze({
    PRESENSI_LENGKAP: 'presensi_lengkap',
    PRESENSI_MASUK_SAJA: 'presensi_masuk_saja',
    TIDAK_PRESENSI: 'tidak_presensi',
    IZIN_CUTI: 'izin_cuti',
    LEMBUR: 'lembur',
});

/**
 * NOTE:
 * - Semua class Tailwind ditulis sebagai string literal agar tidak ter-purge.
 * - Gunakan metadata ini untuk badge, legend, dan tampilan calendar.
 * - Jangan hardcode label/warna status di template.
 */
const STATUS_META = Object.freeze({
    [STATUS_KEYS.PRESENSI_LENGKAP]: {
        key: STATUS_KEYS.PRESENSI_LENGKAP,
        label: 'Hadir Lengkap',
        description: 'Masuk + keluar tercatat',
        icon: 'check',
        badgeClass:
            'bg-emerald-500/10 text-emerald-700 ring-1 ring-emerald-500/15',
        dotClass: 'bg-emerald-500',
        dayCellClass:
            'bg-emerald-500/5 hover:bg-emerald-500/10 border-emerald-500/10',
        eventClass:
            'bg-emerald-500/10 text-emerald-800 ring-1 ring-emerald-500/15',
        statRingClass: 'bg-emerald-500/10 ring-1 ring-emerald-500/15',
        statIconClass: 'text-emerald-700',
    },
    [STATUS_KEYS.PRESENSI_MASUK_SAJA]: {
        key: STATUS_KEYS.PRESENSI_MASUK_SAJA,
        label: 'Masuk Saja',
        description: 'Belum presensi keluar',
        icon: 'clock',
        badgeClass: 'bg-amber-500/10 text-amber-700 ring-1 ring-amber-500/15',
        dotClass: 'bg-amber-500',
        dayCellClass:
            'bg-amber-500/5 hover:bg-amber-500/10 border-amber-500/10',
        eventClass: 'bg-amber-500/10 text-amber-800 ring-1 ring-amber-500/15',
        statRingClass: 'bg-amber-500/10 ring-1 ring-amber-500/15',
        statIconClass: 'text-amber-700',
    },
    [STATUS_KEYS.TIDAK_PRESENSI]: {
        key: STATUS_KEYS.TIDAK_PRESENSI,
        label: 'Tidak Presensi',
        description: 'Tidak ada data presensi',
        icon: 'minus',
        badgeClass: 'bg-slate-500/10 text-slate-700 ring-1 ring-slate-500/15',
        dotClass: 'bg-slate-400',
        dayCellClass:
            'bg-white hover:bg-slate-50 border-slate-200',
        eventClass: 'bg-slate-500/10 text-slate-800 ring-1 ring-slate-500/15',
        statRingClass: 'bg-slate-50 ring-1 ring-slate-200',
        statIconClass: 'text-slate-600',
    },
    [STATUS_KEYS.IZIN_CUTI]: {
        key: STATUS_KEYS.IZIN_CUTI,
        label: 'Izin/Cuti',
        description: 'Izin atau cuti tercatat',
        icon: 'document',
        badgeClass: 'bg-sky-500/10 text-sky-700 ring-1 ring-sky-500/15',
        dotClass: 'bg-sky-500',
        dayCellClass: 'bg-sky-500/5 hover:bg-sky-500/10 border-sky-500/10',
        eventClass: 'bg-sky-500/10 text-sky-800 ring-1 ring-sky-500/15',
        statRingClass: 'bg-sky-500/10 ring-1 ring-sky-500/15',
        statIconClass: 'text-sky-700',
    },
    [STATUS_KEYS.LEMBUR]: {
        key: STATUS_KEYS.LEMBUR,
        label: 'Lembur',
        description: 'Aktivitas lembur tercatat',
        icon: 'moon',
        badgeClass: 'bg-violet-500/10 text-violet-700 ring-1 ring-violet-500/15',
        dotClass: 'bg-violet-500',
        dayCellClass: 'bg-violet-500/5 hover:bg-violet-500/10 border-violet-500/10',
        eventClass: 'bg-violet-500/10 text-violet-800 ring-1 ring-violet-500/15',
        statRingClass: 'bg-violet-500/10 ring-1 ring-violet-500/15',
        statIconClass: 'text-violet-700',
    },
});

const DEFAULT_META = Object.freeze({
    key: 'unknown',
    label: 'Status',
    description: '',
    icon: 'dot',
    badgeClass: 'bg-slate-50 text-slate-700 ring-1 ring-slate-200',
    dotClass: 'bg-slate-400',
    dayCellClass: 'bg-white hover:bg-slate-50 border-slate-200',
    eventClass: 'bg-slate-50 text-slate-800 ring-1 ring-slate-200',
    statRingClass: 'bg-slate-50 ring-1 ring-slate-200',
    statIconClass: 'text-slate-600',
});

export const ATTENDANCE_STATUS_KEYS = STATUS_KEYS;

export const ATTENDANCE_STATUS_ORDER = Object.freeze([
    STATUS_KEYS.PRESENSI_LENGKAP,
    STATUS_KEYS.PRESENSI_MASUK_SAJA,
    STATUS_KEYS.TIDAK_PRESENSI,
    STATUS_KEYS.IZIN_CUTI,
    STATUS_KEYS.LEMBUR,
]);

export function getAttendanceStatusMeta(statusKey) {
    return STATUS_META[statusKey] ?? DEFAULT_META;
}

export function toDateKey(value) {
    if (!value) return '';
    if (typeof value === 'string') return value.slice(0, 10);
    if (value instanceof Date && !Number.isNaN(value.valueOf())) {
        const yyyy = value.getFullYear();
        const mm = String(value.getMonth() + 1).padStart(2, '0');
        const dd = String(value.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }
    return '';
}

export function isSameDateKey(a, b) {
    return toDateKey(a) !== '' && toDateKey(a) === toDateKey(b);
}

export function deriveAttendanceStatusKey({ presensi, izinCuti, lembur }) {
    // Izin/Cuti bersifat eksklusif (tidak boleh presensi/lembur di hari yang sama)
    if (izinCuti) return STATUS_KEYS.IZIN_CUTI;

    // Jika tidak ada presensi tapi ada lembur (data legacy/edge case), tetap tampilkan lembur.
    if (!presensi && lembur) return STATUS_KEYS.LEMBUR;

    if (!presensi) return STATUS_KEYS.TIDAK_PRESENSI;

    const hasMasuk = Boolean(presensi.jam_masuk);
    const hasKeluar = Boolean(presensi.jam_keluar);

    if (hasMasuk && hasKeluar) return STATUS_KEYS.PRESENSI_LENGKAP;
    if (hasMasuk && !hasKeluar) return STATUS_KEYS.PRESENSI_MASUK_SAJA;

    return STATUS_KEYS.TIDAK_PRESENSI;
}

export function hasOvertime({ izinCuti, lembur }) {
    // Izin/Cuti bersifat eksklusif, sehingga lembur diabaikan sebagai indikator tambahan.
    if (izinCuti) return false;
    return Boolean(lembur);
}
