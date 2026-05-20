<script setup>
import { computed } from 'vue';
import Banner from '../Components/Banner.vue';
import Sidebar from '../Components/Sidebar.vue';
import { useSidebarState } from '../composables/useSidebarState';

const { isCollapsed, openMobile } = useSidebarState();

const contentMarginClass = computed(() => (isCollapsed.value ? 'md:ml-20' : 'md:ml-64'));
</script>

<template>
    <div class="font-sans antialiased">
        <Banner />

        <div class="min-h-screen bg-slate-50 flex">
            <Sidebar />

            <div
                class="flex-1 ml-0 overflow-y-auto transition-[margin] duration-200 ease-out"
                :class="contentMarginClass"
            >
                <!-- Mobile top bar (menu + optional page header) -->
                <div class="sticky top-0 z-20 md:hidden bg-white/80 backdrop-blur border-b border-slate-200">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-700 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/50"
                            aria-label="Open sidebar menu"
                            @click="openMobile()"
                        >
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div v-if="$slots.header" class="min-w-0 flex-1">
                            <slot name="header" />
                        </div>
                        <div v-else class="flex-1" />
                    </div>
                </div>

                <header v-if="$slots.header" class="hidden md:block bg-white/80 backdrop-blur border-b border-slate-200">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <main class="p-4 sm:p-6">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
