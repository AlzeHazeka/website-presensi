<script setup>
defineProps({
    value: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <label class="block text-sm font-medium text-slate-200">
        <slot>{{ value }}</slot>
    </label>
</template>
