<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <input
        :type="type"
        :disabled="disabled"
        class="w-full rounded-lg border border-white/15 bg-white/10 px-3 py-2 text-slate-100 placeholder:text-slate-300/70 shadow-sm outline-none ring-1 ring-transparent transition focus:border-transparent focus:ring-2 focus:ring-sky-400/70 disabled:cursor-not-allowed disabled:opacity-60"
        :value="modelValue"
        @input="emit('update:modelValue', $event.target.value)"
        v-bind="$attrs"
    />
</template>
