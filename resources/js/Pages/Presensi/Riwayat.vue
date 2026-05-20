<script setup>
import { computed, nextTick, ref } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import idLocale from '@fullcalendar/core/locales/id';

import AppLayout from '../../Layouts/AppLayout.vue';
import AttendanceMonthSummary from '../../Components/Presensi/AttendanceMonthSummary.vue';
import AttendanceLegend from '../../Components/Presensi/AttendanceLegend.vue';
import AttendanceDetailPanel from '../../Components/Presensi/AttendanceDetailPanel.vue';
import AttendanceStatusBadge from '../../Components/Presensi/AttendanceStatusBadge.vue';
import { useAttendanceCalendarData } from '../../composables/useAttendanceCalendarData';
import { ATTENDANCE_STATUS_KEYS, getAttendanceStatusMeta, isSameDateKey, toDateKey } from '../../lib/attendance/statusSystem';

const props = defineProps({
    presensi: {
        type: Array,
        required: true,
    },
    izinCuti: {
        type: Array,
        default: () => [],
    },
    lembur: {
        type: Array,
        default: () => [],
    },
});

const calendarRef = ref(null);
const detailSectionRef = ref(null);

const now = new Date();
const selectedMonth = ref(now.getMonth() + 1);
const selectedYear = ref(now.getFullYear());
const selectedDateKey = ref('');

const todayKey = toDateKey(now);

const years = computed(() => {
    const start = 2023;
    const end = new Date().getFullYear() + 1;
    const results = [];
    for (let year = start; year <= end; year += 1) results.push(year);
    return results;
});

function monthLabel(month) {
    const date = new Date(2000, month - 1, 1);
    return new Intl.DateTimeFormat('id-ID', { month: 'long' }).format(date);
}

const { calendarEvents, monthSummary, getDayData, statusByDate, overtimeByDate } = useAttendanceCalendarData(
    { presensi: computed(() => props.presensi), izinCuti: computed(() => props.izinCuti), lembur: computed(() => props.lembur) },
    { year: selectedYear, month: selectedMonth },
);

const selectedDayData = computed(() => (selectedDateKey.value ? getDayData(selectedDateKey.value) : null));
const selectedStatusKey = computed(() => selectedDayData.value?.statusKey ?? '');

function gotoSelectedMonth() {
    const api = calendarRef.value?.getApi?.();
    if (!api) return;

    api.gotoDate(new Date(selectedYear.value, selectedMonth.value - 1, 1));
}

async function maybeScrollToDetail() {
    if (typeof window === 'undefined') return;
    if (window.matchMedia && window.matchMedia('(min-width: 1024px)').matches) return;

    await nextTick();
    const el = detailSectionRef.value;
    if (!el || typeof el.scrollIntoView !== 'function') return;
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function setSelectedDate(dateKey, { scrollToDetail = true } = {}) {
    selectedDateKey.value = toDateKey(dateKey);
    if (scrollToDetail && selectedDateKey.value) {
        maybeScrollToDetail();
    }
}

function handleEventClick(info) {
    const dateKey = info?.event?.extendedProps?.dateKey ?? info?.event?.startStr ?? '';
    if (dateKey) setSelectedDate(dateKey, { scrollToDetail: true });
}

function handleDateClick(arg) {
    const dateKey = arg?.dateStr ?? '';
    if (dateKey) setSelectedDate(dateKey, { scrollToDetail: true });
}

function gotoToday() {
    const d = new Date();
    selectedMonth.value = d.getMonth() + 1;
    selectedYear.value = d.getFullYear();
    gotoSelectedMonth();
    setSelectedDate(toDateKey(d), { scrollToDetail: false });
}

function renderEventContent(arg) {
    const statusKey = arg?.event?.extendedProps?.statusKey ?? '';
    const meta = getAttendanceStatusMeta(statusKey);
    const label = meta.label ?? '';
    const hasLembur = Boolean(arg?.event?.extendedProps?.hasLembur);
    // Render compact pill (avoid heavy text inside cell).
    return {
        html: `<span class="att-event-pill"><span class="att-event-pill-label">${label}</span>${hasLembur ? '<span class=\"att-event-pill-ot\" title=\"Lembur\" aria-label=\"Lembur\"></span>' : ''}</span>`,
    };
}

function dayCellClassNames(arg) {
    const base = ['att-day', 'cursor-pointer'];
    if (arg?.isOther) return [...base, 'att-day--other'];

    const dateKey = toDateKey(arg?.date);
    const statusKey = statusByDate.value.get(dateKey) ?? ATTENDANCE_STATUS_KEYS.TIDAK_PRESENSI;
    const meta = statusKey ? getAttendanceStatusMeta(statusKey) : null;

    if (dateKey && isSameDateKey(dateKey, todayKey)) base.push('att-day--today');
    if (dateKey && selectedDateKey.value && isSameDateKey(dateKey, selectedDateKey.value)) base.push('att-day--selected');

    if (statusKey) {
        base.push(`att-status--${statusKey}`);
        const metaClasses = String(meta?.dayCellClass ?? '').split(/\s+/).filter(Boolean);
        base.push(...metaClasses);
    }
    return base;
}

function dayCellDidMount(arg) {
    if (!arg?.el || arg?.isOther) return;

    const dateKey = toDateKey(arg?.date);
    const statusKey = statusByDate.value.get(dateKey) ?? ATTENDANCE_STATUS_KEYS.TIDAK_PRESENSI;
    const meta = getAttendanceStatusMeta(statusKey);
    const hasLembur = Boolean(overtimeByDate.value.get(dateKey));

    const topEl = arg.el.querySelector('.fc-daygrid-day-top');
    if (!topEl) return;

    const existing = topEl.querySelector('.att-day-dot');
    if (existing) existing.remove();

    const dot = document.createElement('span');
    dot.className = ['att-day-dot', 'inline-flex', 'h-2', 'w-2', 'rounded-full', meta.dotClass].join(' ');
    dot.setAttribute('aria-hidden', 'true');
    topEl.appendChild(dot);

    const existingOt = topEl.querySelector('.att-day-ot');
    if (existingOt) existingOt.remove();
    if (hasLembur && statusKey !== ATTENDANCE_STATUS_KEYS.IZIN_CUTI) {
        const ot = document.createElement('span');
        ot.className = [
            'att-day-ot',
            'inline-flex',
            'items-center',
            'justify-center',
            'h-5',
            'w-5',
            'rounded-full',
            'bg-violet-500/10',
            'ring-1',
            'ring-violet-500/15',
            'text-violet-700',
        ].join(' ');
        ot.setAttribute('title', 'Lembur');
        ot.innerHTML =
            '<svg class=\"h-3 w-3\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z\" /></svg>';
        topEl.appendChild(ot);
    }
}

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    locale: idLocale,
    headerToolbar: false,
    height: 'auto',
    fixedWeekCount: false,
    dayMaxEvents: 1,
    events: calendarEvents.value,
    eventClassNames: (arg) => {
        const statusKey = arg?.event?.extendedProps?.statusKey ?? '';
        const meta = getAttendanceStatusMeta(statusKey);
        const metaClasses = String(meta.eventClass ?? '').split(/\s+/).filter(Boolean);
        return ['att-event', ...metaClasses];
    },
    eventContent: renderEventContent,
    eventClick: handleEventClick,
    dateClick: handleDateClick,
    dayCellClassNames,
    dayCellDidMount,
    datesSet: (info) => {
        const date = info?.view?.currentStart ?? new Date();
        selectedMonth.value = date.getMonth() + 1;
        selectedYear.value = date.getFullYear();
    },
}));
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Riwayat Presensi</h2>
                <p class="mt-1 text-sm text-slate-600">Kalender operasional presensi bulanan dengan detail per tanggal.</p>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 text-xs font-semibold tracking-wider text-slate-500 uppercase">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-sky-500/70" />
                        Kalender Presensi
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <AttendanceStatusBadge v-if="selectedStatusKey" :status-key="selectedStatusKey" />
                        <div v-if="selectedDateKey" class="text-sm text-slate-600">
                            Tanggal dipilih: <span class="font-semibold text-slate-900">{{ selectedDateKey }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <button
                        type="button"
                        class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 active:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40"
                        @click="gotoToday"
                    >
                        Hari Ini
                    </button>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-slate-600">Bulan</label>
                        <select
                            v-model.number="selectedMonth"
                            class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-400/30"
                            @change="gotoSelectedMonth"
                        >
                            <option v-for="month in 12" :key="month" :value="month">{{ monthLabel(month) }}</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-slate-600">Tahun</label>
                        <select
                            v-model.number="selectedYear"
                            class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-800 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-400/30"
                            @change="gotoSelectedMonth"
                        >
                            <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <AttendanceMonthSummary :summary="monthSummary" />

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-slate-900">Kalender Bulanan</div>
                        <div class="mt-0.5 text-xs text-slate-600">Klik tanggal untuk melihat detail.</div>
                    </div>
                </div>
                <div class="attendance-calendar p-3 sm:p-5">
                    <div class="overflow-x-auto">
                        <div class="min-w-[720px] sm:min-w-0">
                            <FullCalendar ref="calendarRef" :options="calendarOptions" />
                        </div>
                    </div>
                </div>
            </section>

            <AttendanceLegend />

            <div ref="detailSectionRef">
                <AttendanceDetailPanel :day-data="selectedDayData" />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.attendance-calendar .fc) {
    --fc-border-color: rgb(226 232 240); /* slate-200 */
    --fc-today-bg-color: transparent;
    font-family: inherit;
}

:deep(.attendance-calendar .fc .fc-scrollgrid) {
    border-radius: 16px;
    overflow: hidden;
}

:deep(.attendance-calendar .fc .fc-col-header-cell) {
    background: rgb(248 250 252); /* slate-50 */
}

:deep(.attendance-calendar .fc .fc-col-header-cell-cushion) {
    padding: 10px 8px;
    font-size: 12px;
    font-weight: 700;
    color: rgb(71 85 105); /* slate-600 */
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

:deep(.attendance-calendar .fc .fc-daygrid-day) {
    border-color: rgb(226 232 240); /* slate-200 */
}

:deep(.attendance-calendar .fc .fc-daygrid-day-frame) {
    min-height: 98px;
}

:deep(.attendance-calendar .fc .fc-daygrid-day-number) {
    padding: 10px 10px 6px 10px;
    font-size: 13px;
    font-weight: 700;
    color: rgb(30 41 59); /* slate-800 */
}

:deep(.attendance-calendar .fc .fc-daygrid-day-top) {
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

:deep(.attendance-calendar .fc .att-day-dot) {
    margin-top: 14px;
    margin-right: 10px;
    flex: 0 0 auto;
}

:deep(.attendance-calendar .fc .att-day-ot) {
    margin-top: 9px;
    margin-right: 10px;
    flex: 0 0 auto;
}

:deep(.attendance-calendar .fc .fc-day-other .fc-daygrid-day-number) {
    color: rgb(148 163 184); /* slate-400 */
}

:deep(.attendance-calendar .fc .att-day) {
    transition: background-color 120ms ease, box-shadow 120ms ease;
}

:deep(.attendance-calendar .fc .att-day--today) {
    box-shadow: inset 0 0 0 2px rgba(56, 189, 248, 0.35); /* sky-400 */
}

:deep(.attendance-calendar .fc .att-day--selected) {
    box-shadow: inset 0 0 0 2px rgba(14, 165, 233, 0.55); /* sky-500 */
}

:deep(.attendance-calendar .fc .att-day--other) {
    background: rgb(248 250 252); /* slate-50 */
}

/* Event pill refinement */
:deep(.attendance-calendar .fc .att-event) {
    border: 0;
    border-radius: 999px;
    padding: 0;
    margin: 0 8px 8px 8px;
    background: transparent;
}

:deep(.attendance-calendar .fc .att-event .fc-event-main) {
    padding: 0;
}

:deep(.attendance-calendar .fc .att-event-pill) {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    border-radius: 999px;
    padding: 5px 8px;
    font-size: 11px;
    font-weight: 700;
    line-height: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

:deep(.attendance-calendar .fc .att-event-pill-ot) {
    display: inline-flex;
    height: 8px;
    width: 8px;
    border-radius: 999px;
    margin-left: 6px;
    background: rgba(139, 92, 246, 0.6); /* violet-500 */
}

:deep(.attendance-calendar .fc .att-event.bg-emerald-500\/10 .att-event-pill) {
    background: rgba(16, 185, 129, 0.1); /* emerald-500 */
    color: rgb(6 95 70); /* emerald-800 */
    box-shadow: inset 0 0 0 1px rgba(16, 185, 129, 0.15);
}
:deep(.attendance-calendar .fc .att-event.bg-amber-500\/10 .att-event-pill) {
    background: rgba(245, 158, 11, 0.1); /* amber-500 */
    color: rgb(146 64 14); /* amber-800 */
    box-shadow: inset 0 0 0 1px rgba(245, 158, 11, 0.15);
}
:deep(.attendance-calendar .fc .att-event.bg-sky-500\/10 .att-event-pill) {
    background: rgba(14, 165, 233, 0.1); /* sky-500 */
    color: rgb(7 89 133); /* sky-800 */
    box-shadow: inset 0 0 0 1px rgba(14, 165, 233, 0.15);
}
:deep(.attendance-calendar .fc .att-event.bg-violet-500\/10 .att-event-pill) {
    background: rgba(139, 92, 246, 0.1); /* violet-500 */
    color: rgb(91 33 182); /* violet-800 */
    box-shadow: inset 0 0 0 1px rgba(139, 92, 246, 0.15);
}
</style>
