<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: { type: String, required: true },
    tone: {
        type: String,
        default: 'slate',
        validator: (value) => ['slate', 'info', 'success', 'warning', 'danger'].includes(value),
    },
});

const toneClasses = computed(() => {
    if (props.tone === 'success') return 'bg-emerald-500/10 ring-emerald-500/15 text-emerald-700';
    if (props.tone === 'info') return 'bg-sky-500/10 ring-sky-500/15 text-sky-700';
    if (props.tone === 'warning') return 'bg-amber-500/10 ring-amber-500/15 text-amber-800';
    if (props.tone === 'danger') return 'bg-rose-500/10 ring-rose-500/15 text-rose-700';
    return 'bg-slate-500/10 ring-slate-500/15 text-slate-700';
});
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
        :class="toneClasses"
    >
        {{ label }}
    </span>
</template>

