<script setup>
import { computed, onBeforeUnmount, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { route } from '../lib/route';
import { useSidebarState } from '../composables/useSidebarState';
import { useMediaQuery } from '../composables/useMediaQuery';
import { buildSidebarNavigation } from '../lib/navigation/sidebarNavigation';
import SidebarNavItem from './Sidebar/SidebarNavItem.vue';
import SidebarNavDropdown from './Sidebar/SidebarNavDropdown.vue';

const { isCollapsed, isMobileOpen, toggleCollapsed, closeMobile } = useSidebarState();

const page = usePage();
const currentUrl = computed(() => page.url ?? '');
const user = computed(() => page.props.auth?.user ?? null);
const authz = computed(() => page.props.authz ?? {});

const isDesktop = useMediaQuery('(min-width: 768px)');
const isDesktopCollapsed = computed(() => Boolean(isDesktop.value && isCollapsed.value));

const navigationGroups = computed(() => buildSidebarNavigation({ route, authz: authz.value }));

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

function closeMobileIfOpen() {
    closeMobile();
}

const asideClasses = computed(() => {
    const width = isCollapsed.value ? 'md:w-20' : 'md:w-64';
    const transform = isMobileOpen.value ? 'translate-x-0' : '-translate-x-full';
    return [
        'w-72 md:translate-x-0',
        width,
        transform,
    ].join(' ');
});

function onKeydown(e) {
    if (e.key === 'Escape') closeMobile();
}

onMounted(() => {
    if (typeof window !== 'undefined') window.addEventListener('keydown', onKeydown);
});
onBeforeUnmount(() => {
    if (typeof window !== 'undefined') window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <!-- Mobile overlay -->
    <div
        v-show="isMobileOpen"
        class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-[1px] md:hidden"
        role="button"
        aria-label="Close sidebar overlay"
        tabindex="0"
        @click="closeMobile()"
        @keydown.enter.prevent="closeMobile()"
        @keydown.space.prevent="closeMobile()"
    />

    <aside
        class="fixed inset-y-0 left-0 z-50 bg-slate-950 text-slate-100 ring-1 ring-white/10 transition-[transform,width] duration-200 ease-out"
        :class="asideClasses"
        aria-label="Sidebar navigation"
    >
        <div class="flex h-full flex-col">
            <div class="px-4 py-5 flex items-center gap-2">
                <Link
                    :href="route('dashboard')"
                    class="flex min-w-0 flex-1 items-center gap-3 rounded-2xl px-3 py-2 hover:bg-white/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40"
                    :title="isCollapsed ? 'Dashboard' : undefined"
                    @click="closeMobileIfOpen"
                >
                    <img src="/images/IPS_logo.png" class="h-9 w-9" alt="IPS Logo" />
                    <div class="leading-tight min-w-0" :class="isCollapsed ? 'md:hidden' : ''">
                        <div class="text-sm font-semibold tracking-wide">IPS</div>
                        <div class="text-[11px] text-slate-200/70">Internal System</div>
                    </div>
                </Link>

                <!-- Desktop collapse/expand toggle -->
                <button
                    type="button"
                    class="hidden md:inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-200/80 hover:bg-white/5 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40"
                    :aria-label="isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    @click="toggleCollapsed()"
                >
                    <svg v-if="isCollapsed" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <svg v-else class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Mobile close button -->
                <button
                    type="button"
                    class="md:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-200/80 hover:bg-white/5 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40"
                    aria-label="Close sidebar menu"
                    @click="closeMobile()"
                >
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-4">
                <div class="h-px w-full bg-white/10" />
            </div>

            <nav class="flex-1 min-h-0 overflow-y-auto px-4 py-4 space-y-2">
                <template v-for="group in navigationGroups" :key="group.id">
                    <div v-if="group.label" class="pt-2">
                        <div class="px-3 py-2 text-[11px] font-semibold tracking-wider text-slate-200/60 uppercase" :class="isCollapsed ? 'md:hidden' : ''">
                            {{ group.label }}
                        </div>
                    </div>

                    <div class="space-y-1">
                        <template v-for="item in group.items" :key="item.id">
                            <SidebarNavItem
                                v-if="item.type === 'link'"
                                :item="item"
                                :current-url="currentUrl"
                                :is-collapsed="isCollapsed"
                                @navigate="closeMobileIfOpen"
                            />
                            <SidebarNavDropdown
                                v-else-if="item.type === 'dropdown'"
                                :item="item"
                                :current-url="currentUrl"
                                :is-desktop-collapsed="isDesktopCollapsed"
                                :is-collapsed="isCollapsed"
                                @navigate="closeMobileIfOpen"
                            />
                        </template>
                    </div>
                </template>
            </nav>

            <div class="px-4 pb-5 space-y-3">
                <div class="h-px w-full bg-white/10" />

                <!-- User profile (compact) -->
                <div class="flex items-center gap-3 rounded-2xl px-3 py-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 ring-1 ring-white/10 text-slate-100">
                        <span class="text-sm font-semibold">
                            {{ (user?.nama ?? 'U').slice(0, 1).toUpperCase() }}
                        </span>
                    </div>

                    <div class="min-w-0 flex-1" :class="isCollapsed ? 'md:hidden' : ''">
                        <div class="truncate text-sm font-semibold text-slate-100">{{ user?.nama ?? 'User' }}</div>
                        <div class="truncate text-[11px] text-slate-200/70">{{ user?.role ?? 'Member' }}</div>
                    </div>
                </div>

                <form method="POST" :action="route('logout')">
                    <input type="hidden" name="_token" :value="csrfToken" />
                    <button
                        type="submit"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-200/85 hover:bg-white/5 hover:text-white transition"
                        :class="isCollapsed ? 'md:justify-center md:gap-0 md:px-2' : ''"
                        :title="isCollapsed ? 'Log Out' : undefined"
                    >
                        <svg class="h-5 w-5 shrink-0 text-slate-200/80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 11-4 0v-1a2 2 0 014 0z" />
                        </svg>
                        <span :class="isCollapsed ? 'md:hidden' : ''">Log Out</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>
</template>
