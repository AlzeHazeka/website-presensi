<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center rounded-lg bg-sky-500 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-sky-950/30 transition hover:bg-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-300/70 disabled:opacity-50"
    >
        <slot />
    </button>
</template>
