<script setup>
import { computed } from 'vue';
import AttendanceStatusBadge from './AttendanceStatusBadge.vue';
import { ATTENDANCE_STATUS_ORDER } from '../../lib/attendance/statusSystem';

const props = defineProps({
    statusKeys: {
        type: Array,
        default: () => ATTENDANCE_STATUS_ORDER,
    },
    title: {
        type: String,
        default: 'Legenda Status',
    },
});

const keys = computed(() => props.statusKeys ?? ATTENDANCE_STATUS_ORDER);
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div class="min-w-0">
                <div class="text-sm font-semibold text-slate-900">{{ title }}</div>
                <div class="mt-0.5 text-xs text-slate-600">Panduan cepat untuk membaca status pada kalender.</div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 px-5 py-4">
            <AttendanceStatusBadge v-for="key in keys" :key="key" :status-key="key" />
        </div>
    </section>
</template>

