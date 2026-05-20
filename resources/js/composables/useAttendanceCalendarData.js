import { computed } from 'vue';
import {
    ATTENDANCE_STATUS_KEYS,
    deriveAttendanceStatusKey,
    hasOvertime,
    toDateKey,
} from '../lib/attendance/statusSystem';

function indexByDateKey(items, dateField) {
    const map = new Map();
    for (const item of items ?? []) {
        const key = toDateKey(item?.[dateField]);
        if (!key) continue;
        map.set(key, item);
    }
    return map;
}

function daysInMonth(year, month1to12) {
    return new Date(year, month1to12, 0).getDate();
}

function buildDateKey(year, month1to12, day) {
    const mm = String(month1to12).padStart(2, '0');
    const dd = String(day).padStart(2, '0');
    return `${year}-${mm}-${dd}`;
}

export function useAttendanceCalendarData({ presensi, izinCuti, lembur }, { year, month }) {
    const presensiByDate = computed(() => indexByDateKey(presensi?.value ?? presensi, 'tanggal'));
    const izinCutiByDate = computed(() => indexByDateKey(izinCuti?.value ?? izinCuti, 'tanggal_izin'));
    const lemburByDate = computed(() => indexByDateKey(lembur?.value ?? lembur, 'tanggal'));

    const statusByDate = computed(() => {
        const map = new Map();
        const allKeys = new Set([
            ...presensiByDate.value.keys(),
            ...izinCutiByDate.value.keys(),
            ...lemburByDate.value.keys(),
        ]);

        for (const key of allKeys) {
            map.set(
                key,
                deriveAttendanceStatusKey({
                    presensi: presensiByDate.value.get(key),
                    izinCuti: izinCutiByDate.value.get(key),
                    lembur: lemburByDate.value.get(key),
                }),
            );
        }

        return map;
    });

    const overtimeByDate = computed(() => {
        const map = new Map();
        const allKeys = new Set([
            ...presensiByDate.value.keys(),
            ...izinCutiByDate.value.keys(),
            ...lemburByDate.value.keys(),
        ]);

        for (const key of allKeys) {
            map.set(
                key,
                hasOvertime({
                    izinCuti: izinCutiByDate.value.get(key),
                    lembur: lemburByDate.value.get(key),
                }),
            );
        }

        return map;
    });

    const calendarEvents = computed(() => {
        const events = [];

        // 1) Exclusive state: izin/cuti
        for (const [dateKey, item] of izinCutiByDate.value.entries()) {
            const statusKey = ATTENDANCE_STATUS_KEYS.IZIN_CUTI;
            events.push({
                id: `izin_cuti:${dateKey}`,
                start: dateKey,
                allDay: true,
                title: statusKey,
                extendedProps: { statusKey, dateKey, izinCuti: item, hasLembur: false },
            });
        }

        // 2) Attendance (presensi)
        for (const [dateKey, item] of presensiByDate.value.entries()) {
            if (izinCutiByDate.value.has(dateKey)) continue;
            const statusKey = deriveAttendanceStatusKey({
                presensi: item,
                izinCuti: izinCutiByDate.value.get(dateKey),
                lembur: lemburByDate.value.get(dateKey),
            });
            if (statusKey === ATTENDANCE_STATUS_KEYS.TIDAK_PRESENSI) continue;
            events.push({
                id: `presensi:${dateKey}`,
                start: dateKey,
                allDay: true,
                title: statusKey,
                extendedProps: {
                    statusKey,
                    dateKey,
                    presensi: item,
                    lembur: lemburByDate.value.get(dateKey) ?? null,
                    hasLembur: hasOvertime({
                        izinCuti: izinCutiByDate.value.get(dateKey),
                        lembur: lemburByDate.value.get(dateKey),
                    }),
                },
            });
        }

        // 3) Overtime only (no presensi, not izin/cuti)
        for (const [dateKey, item] of lemburByDate.value.entries()) {
            if (izinCutiByDate.value.has(dateKey)) continue;
            if (presensiByDate.value.has(dateKey)) continue;
            const statusKey = ATTENDANCE_STATUS_KEYS.LEMBUR;
            events.push({
                id: `lembur:${dateKey}`,
                start: dateKey,
                allDay: true,
                title: statusKey,
                extendedProps: { statusKey, dateKey, lembur: item, hasLembur: true },
            });
        }

        return events;
    });

    const monthSummary = computed(() => {
        const y = year?.value ?? year;
        const m = month?.value ?? month;
        const totalDays = daysInMonth(y, m);

        const summary = {
            [ATTENDANCE_STATUS_KEYS.PRESENSI_LENGKAP]: 0,
            [ATTENDANCE_STATUS_KEYS.PRESENSI_MASUK_SAJA]: 0,
            [ATTENDANCE_STATUS_KEYS.TIDAK_PRESENSI]: 0,
            [ATTENDANCE_STATUS_KEYS.IZIN_CUTI]: 0,
            [ATTENDANCE_STATUS_KEYS.LEMBUR]: 0, // overlay count (hari yang ada lembur)
        };

        for (let d = 1; d <= totalDays; d += 1) {
            const dateKey = buildDateKey(y, m, d);
            const statusKey = statusByDate.value.get(dateKey) ?? deriveAttendanceStatusKey({
                presensi: presensiByDate.value.get(dateKey),
                izinCuti: izinCutiByDate.value.get(dateKey),
                lembur: lemburByDate.value.get(dateKey),
            });

            summary[statusKey] = (summary[statusKey] ?? 0) + 1;
            if (overtimeByDate.value.get(dateKey) && statusKey !== ATTENDANCE_STATUS_KEYS.LEMBUR) {
                summary[ATTENDANCE_STATUS_KEYS.LEMBUR] += 1;
            }
        }

        return summary;
    });

    function getDayData(dateKey) {
        const key = toDateKey(dateKey);
        if (!key) return null;

        const presensiItem = presensiByDate.value.get(key) ?? null;
        const izinCutiItem = izinCutiByDate.value.get(key) ?? null;
        const lemburItem = lemburByDate.value.get(key) ?? null;

        return {
            dateKey: key,
            presensi: presensiItem,
            izinCuti: izinCutiItem,
            lembur: lemburItem,
            statusKey: deriveAttendanceStatusKey({ presensi: presensiItem, izinCuti: izinCutiItem, lembur: lemburItem }),
            hasLembur: hasOvertime({ izinCuti: izinCutiItem, lembur: lemburItem }),
        };
    }

    return {
        presensiByDate,
        izinCutiByDate,
        lemburByDate,
        statusByDate,
        overtimeByDate,
        calendarEvents,
        monthSummary,
        getDayData,
    };
}
