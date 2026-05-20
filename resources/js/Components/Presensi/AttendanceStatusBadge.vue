<script setup>
import { computed } from 'vue';
import { getAttendanceStatusMeta } from '../../lib/attendance/statusSystem';

const props = defineProps({
    statusKey: {
        type: String,
        required: true,
    },
    size: {
        type: String,
        default: 'sm', // sm | md
    },
    showIcon: {
        type: Boolean,
        default: true,
    },
});

const meta = computed(() => getAttendanceStatusMeta(props.statusKey));

const sizeClass = computed(() => {
    if (props.size === 'md') return 'px-3 py-1.5 text-xs';
    return 'px-2.5 py-1 text-[11px]';
});

const iconClass = computed(() => {
    if (props.size === 'md') return 'h-3.5 w-3.5';
    return 'h-3 w-3';
});
</script>

<template>
    <span class="inline-flex items-center gap-1.5 rounded-full font-semibold" :class="[meta.badgeClass, sizeClass]">
        <span v-if="showIcon" class="inline-flex items-center justify-center">
            <svg
                v-if="meta.icon === 'check'"
                :class="iconClass"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <svg
                v-else-if="meta.icon === 'clock'"
                :class="iconClass"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg
                v-else-if="meta.icon === 'document'"
                :class="iconClass"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v13a2 2 0 01-2 2z" />
            </svg>
            <svg
                v-else-if="meta.icon === 'calendar'"
                :class="iconClass"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <svg
                v-else-if="meta.icon === 'moon'"
                :class="iconClass"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
            </svg>
            <svg
                v-else
                :class="iconClass"
                xmlns="http://www.w3.org/2000/svg"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <circle cx="10" cy="10" r="3" />
            </svg>
        </span>
        <span class="leading-none">{{ meta.label }}</span>
    </span>
</template>
