<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap gap-1">
        <template v-for="(link, index) in links" :key="index">
            <span
                v-if="!link.url"
                class="px-3 py-2 text-sm rounded border text-gray-400 bg-white"
                v-html="link.label"
            />

            <Link
                v-else
                class="px-3 py-2 text-sm rounded border bg-white hover:bg-gray-50"
                :class="link.active ? 'text-white bg-blue-600 border-blue-600 hover:bg-blue-700' : 'text-gray-700 border-gray-200'"
                :href="link.url"
                v-html="link.label"
            />
        </template>
    </nav>
</template>

