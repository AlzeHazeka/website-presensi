<script setup>
defineProps({
    on: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div v-if="on" class="text-sm text-green-600">
        <slot />
    </div>
</template>

