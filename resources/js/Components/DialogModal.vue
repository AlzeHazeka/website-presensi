<script setup>
import { onBeforeUnmount, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

function onKeydown(event) {
    if (!props.show) return;
    if (event.key === 'Escape') emit('close');
}

watch(
    () => props.show,
    (value) => {
        if (typeof document === 'undefined') return;
        if (value) {
            document.addEventListener('keydown', onKeydown);
            return;
        }

        document.removeEventListener('keydown', onKeydown);
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    if (typeof document === 'undefined') return;
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-0">
        <div class="fixed inset-0 bg-gray-900/60" @click.self="$emit('close')" />

        <div class="relative w-full max-w-lg rounded-lg bg-white shadow-xl">
            <div class="px-6 pt-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    <slot name="title" />
                </h3>

                <div class="mt-3 text-sm text-gray-600">
                    <slot name="content" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 bg-gray-50 px-6 py-4 rounded-b-lg">
                <slot name="footer" />
            </div>
        </div>
    </div>
</template>

