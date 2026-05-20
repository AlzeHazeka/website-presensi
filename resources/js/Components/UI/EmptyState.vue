<script setup>
const props = defineProps({
    title: { type: String, required: true },
    description: { type: String, default: null },
});
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white ring-1 ring-slate-200 text-slate-600">
            <slot name="icon">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v14l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z"
                    />
                </svg>
            </slot>
        </div>
        <div class="mt-3 text-sm font-semibold text-slate-900">{{ title }}</div>
        <div v-if="description" class="mt-1 text-xs text-slate-600">
            {{ description }}
        </div>
    </div>
</template>

