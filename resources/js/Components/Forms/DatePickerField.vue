<script setup>
import { computed } from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import { id as idLocale } from 'date-fns/locale';

const props = defineProps({
    id: { type: String, default: null },
    label: { type: String, default: null },
    helper: { type: String, default: null },
    modelValue: { type: String, default: '' }, // yyyy-MM-dd
    min: { type: String, default: null }, // yyyy-MM-dd
    max: { type: String, default: null }, // yyyy-MM-dd
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    placeholder: { type: String, default: 'Pilih tanggal' },
});

const emit = defineEmits(['update:modelValue']);

function parseYmdToDate(ymd) {
    if (!ymd || typeof ymd !== 'string') return null;
    const parts = ymd.split('-').map((p) => Number.parseInt(p, 10));
    if (parts.length !== 3 || parts.some((p) => Number.isNaN(p))) return null;
    const [year, month, day] = parts;
    return new Date(year, month - 1, day);
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

const minDate = computed(() => parseYmdToDate(props.min));
const maxDate = computed(() => parseYmdToDate(props.max));

function updateValue(value) {
    emit('update:modelValue', value ?? '');
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
            :model-value="modelValue"
            :min-date="minDate"
            :max-date="maxDate"
            :disabled="disabled"
            :placeholder="placeholder"
            model-type="yyyy-MM-dd"
            format="dd MMM yyyy"
            :locale="idLocale"
            :enable-time-picker="false"
            :auto-apply="true"
            :teleport="true"
            :ui="{ input: inputClasses }"
            @update:model-value="updateValue"
        />

        <p v-if="helper" class="text-xs text-slate-500">
            {{ helper }}
        </p>
    </div>
</template>
