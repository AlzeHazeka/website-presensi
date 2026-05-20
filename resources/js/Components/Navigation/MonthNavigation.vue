<script setup>
const props = defineProps({
    label: { type: String, required: true },
    disablePrev: { type: Boolean, default: false },
    disableNext: { type: Boolean, default: false },
});

const emit = defineEmits(['prev', 'next']);
</script>

<template>
    <div class="flex items-center justify-between gap-2">
        <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50 active:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40 disabled:opacity-50"
            :disabled="disablePrev"
            aria-label="Bulan sebelumnya"
            @click="emit('prev')"
        >
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <div class="min-w-0 flex-1 text-center">
            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Bulan</div>
            <div class="truncate text-sm font-semibold text-slate-900">{{ label }}</div>
        </div>

        <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50 active:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40 disabled:opacity-50"
            :disabled="disableNext"
            aria-label="Bulan berikutnya"
            @click="emit('next')"
        >
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
</template>

