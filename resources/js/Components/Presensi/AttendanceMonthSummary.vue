<script setup>
import { computed } from 'vue';
import AttendanceStatusBadge from './AttendanceStatusBadge.vue';
import {
    ATTENDANCE_STATUS_KEYS,
    getAttendanceStatusMeta,
} from '../../lib/attendance/statusSystem';

const props = defineProps({
    summary: {
        type: Object,
        required: true,
    },
    statusKeys: {
        type: Array,
        default: () => [
            ATTENDANCE_STATUS_KEYS.PRESENSI_LENGKAP,
            ATTENDANCE_STATUS_KEYS.PRESENSI_MASUK_SAJA,
            ATTENDANCE_STATUS_KEYS.IZIN_CUTI,
            ATTENDANCE_STATUS_KEYS.LEMBUR,
            ATTENDANCE_STATUS_KEYS.TIDAK_PRESENSI,
        ],
    },
});

const keys = computed(() => props.statusKeys ?? []);

function countFor(key) {
    const val = props.summary?.[key];
    if (typeof val === 'number') return val;
    const parsed = Number(val);
    return Number.isFinite(parsed) ? parsed : 0;
}
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div class="min-w-0">
                <div class="text-sm font-semibold text-slate-900">Ringkasan Bulan Ini</div>
                <div class="mt-0.5 text-xs text-slate-600">Snapshot operasional untuk bulan yang sedang dilihat.</div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-3 lg:grid-cols-5">
            <div
                v-for="key in keys"
                :key="key"
                class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4"
            >
                <div class="flex items-start justify-between gap-3">
                    <AttendanceStatusBadge :status-key="key" size="sm" />
                    <div class="flex h-9 w-9 items-center justify-center rounded-2xl" :class="getAttendanceStatusMeta(key).statRingClass">
                        <svg class="h-5 w-5" :class="getAttendanceStatusMeta(key).statIconClass" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 text-2xl font-semibold tracking-tight text-slate-900">
                    {{ countFor(key) }}
                </div>
                <div class="mt-0.5 text-xs text-slate-600">hari</div>
            </div>
        </div>
    </section>
</template>
