<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const show = ref(true);

const message = computed(() => page.props.flash?.banner ?? null);
const style = computed(() => page.props.flash?.bannerStyle ?? 'success');

watch(message, () => {
    show.value = true;
});

const wrapperClass = computed(() => {
    if (style.value === 'success') return 'bg-indigo-500';
    if (style.value === 'danger') return 'bg-red-700';
    if (style.value === 'warning') return 'bg-yellow-500';
    return 'bg-gray-500';
});

const pillClass = computed(() => {
    if (style.value === 'success') return 'bg-indigo-600';
    if (style.value === 'danger') return 'bg-red-600';
    if (style.value === 'warning') return 'bg-yellow-600';
    return 'bg-gray-600';
});

const dismissClass = computed(() => {
    if (style.value === 'success') return 'hover:bg-indigo-600 focus:bg-indigo-600';
    if (style.value === 'danger') return 'hover:bg-red-600 focus:bg-red-600';
    if (style.value === 'warning') return 'hover:bg-yellow-600 focus:bg-yellow-600';
    return 'hover:bg-gray-600 focus:bg-gray-600';
});
</script>

<template>
    <div
        v-show="show && message"
        :class="wrapperClass"
        class="max-w-screen-2xl mx-auto"
    >
        <div class="max-w-screen-xl mx-auto py-2 px-3 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between flex-wrap">
                <div class="w-0 flex-1 flex items-center min-w-0">
                    <span class="flex p-2 rounded-lg" :class="pillClass">
                        <svg
                            v-if="style === 'success'"
                            class="size-5 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>

                        <svg
                            v-else-if="style === 'danger'"
                            class="size-5 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"
                            />
                        </svg>

                        <svg
                            v-else-if="style === 'warning'"
                            class="size-5 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" fill="none" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4v.01 0 0" />
                        </svg>

                        <svg
                            v-else
                            class="size-5 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"
                            />
                        </svg>
                    </span>

                    <p class="ms-3 font-medium text-sm text-white truncate">
                        {{ message }}
                    </p>
                </div>

                <div class="shrink-0 sm:ms-3">
                    <button
                        type="button"
                        class="-me-1 flex p-2 rounded-md focus:outline-none sm:-me-2 transition"
                        :class="dismissClass"
                        aria-label="Dismiss"
                        @click="show = false"
                    >
                        <svg
                            class="size-5 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

