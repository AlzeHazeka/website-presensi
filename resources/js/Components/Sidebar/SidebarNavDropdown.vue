<script setup>
import { computed } from 'vue';
import SidebarIcon from './SidebarIcon.vue';
import SidebarNavItem from './SidebarNavItem.vue';
import { useSidebarNavigationState } from '../../composables/useSidebarNavigationState';
import { useSidebarState } from '../../composables/useSidebarState';
import { isActiveHref } from '../../lib/navigation/isActiveHref';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    currentUrl: {
        type: String,
        required: true,
    },
    isDesktopCollapsed: {
        type: Boolean,
        required: true,
    },
    isCollapsed: {
        type: Boolean,
        required: true,
    },
});

const emit = defineEmits(['navigate']);

const { isOpen, toggle, setOpen } = useSidebarNavigationState();
const { setCollapsed } = useSidebarState();

const isActive = computed(() => isActiveHref(props.currentUrl, props.item?.href));
const hasActiveChild = computed(() => {
    const children = props.item?.children ?? [];
    return Array.isArray(children) && children.some((child) => isActiveHref(props.currentUrl, child?.href));
});

const open = computed(() => {
    if (hasActiveChild.value) return true;
    return isOpen(props.item?.id);
});

function onToggle() {
    if (props.isDesktopCollapsed) {
        setCollapsed(false);
        setOpen(props.item?.id, true);
        return;
    }

    toggle(props.item?.id);
    if (hasActiveChild.value) setOpen(props.item?.id, true);
}

const buttonClasses = computed(() => {
    return [
        'relative flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40',
        props.isCollapsed ? 'md:justify-center md:px-2' : '',
        (isActive.value || hasActiveChild.value)
            ? "bg-sky-500/10 text-sky-300 ring-1 ring-sky-500/20 after:content-[''] after:absolute after:inset-y-2 after:left-1 after:w-0.5 after:rounded-full after:bg-sky-400/80"
            : 'text-slate-200/85 hover:bg-white/5 hover:text-white/95',
    ].join(' ');
});

const buttonContentClasses = computed(() => {
    return [
        'flex items-center gap-3',
        props.isCollapsed ? 'md:justify-center md:gap-0' : '',
    ].join(' ');
});

const iconClasses = computed(() => {
    return [
        'h-5 w-5 shrink-0 transition-colors duration-150',
        (isActive.value || hasActiveChild.value) ? 'text-sky-300' : 'text-slate-200/80 group-hover:text-white/90',
    ].join(' ');
});

const submenuWrapperClasses = computed(() => {
    // Only hide submenu in desktop collapsed state (icon-only sidebar).
    return [
        'mt-1 space-y-1 pl-2',
        props.isDesktopCollapsed ? 'md:hidden' : '',
    ].join(' ');
});
</script>

<template>
    <button
        type="button"
        :class="buttonClasses"
        @click="onToggle"
        :aria-expanded="open ? 'true' : 'false'"
        :title="isCollapsed ? item.label : undefined"
    >
        <span :class="buttonContentClasses">
            <SidebarIcon :name="item.icon" :class="iconClasses" />
            <span :class="isCollapsed ? 'md:hidden' : ''">{{ item.label }}</span>
        </span>
        <svg
            class="h-4 w-4 transition-transform duration-150"
            :class="[{ 'rotate-180': open }, isCollapsed ? 'md:hidden' : '']"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <Transition
        enter-active-class="transition-all duration-200 ease-out"
        leave-active-class="transition-all duration-150 ease-in"
        enter-from-class="opacity-0 -translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-1"
    >
        <div v-show="open" :class="submenuWrapperClasses">
            <SidebarNavItem
                v-for="child in item.children"
                :key="child.id"
                :item="child"
                :current-url="currentUrl"
                :is-collapsed="false"
                @navigate="emit('navigate')"
            />
        </div>
    </Transition>
</template>
