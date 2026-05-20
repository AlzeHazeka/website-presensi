<script setup>
defineProps({
    message: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <p v-if="message" class="text-sm text-rose-200">
        {{ message }}
    </p>
</template>
