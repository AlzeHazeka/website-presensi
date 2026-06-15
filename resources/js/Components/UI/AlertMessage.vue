<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'info',
        validator: (value) => ['info', 'success', 'warning', 'error'].includes(value),
    },
    title: {
        type: String,
        default: '',
    },
    message: {
        type: String,
        default: '',
    },
    messages: {
        type: Array,
        default: () => [],
    },
});

const styles = {
    info: {
        wrapper: 'border-sky-200 bg-sky-50 text-sky-900',
        icon: 'bg-sky-100 text-sky-700 ring-sky-200',
    },
    success: {
        wrapper: 'border-emerald-200 bg-emerald-50 text-emerald-900',
        icon: 'bg-emerald-100 text-emerald-700 ring-emerald-200',
    },
    warning: {
        wrapper: 'border-amber-200 bg-amber-50 text-amber-900',
        icon: 'bg-amber-100 text-amber-700 ring-amber-200',
    },
    error: {
        wrapper: 'border-rose-200 bg-rose-50 text-rose-900',
        icon: 'bg-rose-100 text-rose-700 ring-rose-200',
    },
};

const style = computed(() => styles[props.type] ?? styles.info);
const iconLabel = computed(() => {
    if (props.type === 'success') return 'OK';
    if (props.type === 'warning') return '!';
    if (props.type === 'error') return '!';
    return 'i';
});
</script>

<template>
    <div class="rounded-2xl border px-4 py-3 text-sm shadow-sm" :class="style.wrapper">
        <div class="flex gap-3">
            <span
                class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold ring-1"
                :class="style.icon"
            >
                {{ iconLabel }}
            </span>

            <div class="min-w-0 flex-1">
                <div v-if="title" class="font-semibold">{{ title }}</div>
                <p v-if="message" class="leading-relaxed" :class="title ? 'mt-1' : ''">{{ message }}</p>
                <ul v-if="messages.length" class="list-disc space-y-1 pl-5 leading-relaxed" :class="title || message ? 'mt-2' : ''">
                    <li v-for="(item, index) in messages" :key="`${item}-${index}`">{{ item }}</li>
                </ul>
            </div>
        </div>
    </div>
</template>
