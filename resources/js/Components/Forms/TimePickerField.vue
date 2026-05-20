<script setup>
import { computed } from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';

const props = defineProps({
    id: { type: String, default: null },
    label: { type: String, default: null },
    helper: { type: String, default: null },
    modelValue: { type: String, default: '' }, // HH:mm
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    placeholder: { type: String, default: 'Pilih jam' },
});

const emit = defineEmits(['update:modelValue']);

function parseHHmmToDate(hhmm) {
    if (!hhmm || typeof hhmm !== 'string') return null;
    const [h, m] = hhmm.split(':').map((p) => Number.parseInt(p, 10));
    if (Number.isNaN(h) || Number.isNaN(m)) return null;
    const d = new Date();
    d.setHours(h, m, 0, 0);
    return d;
}

function formatDateToHHmm(date) {
    if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
    const hh = String(date.getHours()).padStart(2, '0');
    const mm = String(date.getMinutes()).padStart(2, '0');
    return `${hh}:${mm}`;
}

const inputClasses = computed(
    () =>
        [
            'w-full',
            'rounded-xl',
            'border',
            'border-slate-200',
            'bg-white',
            'px-3',
            'py-2.5',
            'text-sm',
            'text-slate-900',
            'shadow-sm',
            'placeholder:text-slate-400',
            'focus:border-sky-400',
            'focus:ring-2',
            'focus:ring-sky-400/30',
            'disabled:bg-slate-100',
            'disabled:text-slate-500',
            'disabled:cursor-not-allowed',
        ].join(' '),
);

const timeValue = computed(() => parseHHmmToDate(props.modelValue));

function updateValue(value) {
    emit('update:modelValue', formatDateToHHmm(value));
}
</script>

<template>
    <div class="space-y-1.5">
        <label v-if="label" :for="id" class="block text-sm font-medium text-slate-700">
            {{ label }}
            <span v-if="required" class="text-rose-500">*</span>
        </label>

        <VueDatePicker
            :id="id"
            :model-value="timeValue"
            :disabled="disabled"
            :placeholder="placeholder"
            :enable-time-picker="true"
            :time-picker="true"
            :teleport="true"
            :auto-apply="true"
            :ui="{ input: inputClasses }"
            @update:model-value="updateValue"
        />

        <p v-if="helper" class="text-xs text-slate-500">
            {{ helper }}
        </p>
    </div>
</template>

