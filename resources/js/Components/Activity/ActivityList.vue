<script setup>
import StatusBadge from '../UI/StatusBadge.vue';

defineProps({
    items: { type: Array, required: true },
});
</script>

<template>
    <div class="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div
            v-for="item in items"
            :key="item.id"
            class="p-4 sm:p-5 flex items-start justify-between gap-4"
        >
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="text-sm font-semibold text-slate-900">{{ item.title }}</div>
                    <StatusBadge v-if="item.badgeLabel" :label="item.badgeLabel" :tone="item.badgeTone ?? 'slate'" />
                </div>
                <div v-if="item.subtitle" class="mt-1 text-xs text-slate-600">
                    {{ item.subtitle }}
                </div>
            </div>

            <div v-if="item.metaRight" class="shrink-0 text-right text-xs text-slate-500">
                {{ item.metaRight }}
            </div>
        </div>
    </div>
</template>

