<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import SidebarIcon from './SidebarIcon.vue';
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
    isCollapsed: {
        type: Boolean,
        required: true,
    },
});

const emit = defineEmits(['navigate']);

const active = computed(() => isActiveHref(props.currentUrl, props.item?.href));

const linkClasses = computed(() => {
    return [
        'group relative flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40',
        props.isCollapsed ? 'md:justify-center md:gap-0 md:px-2' : '',
        active.value
            ? "bg-sky-500/10 text-sky-300 ring-1 ring-sky-500/20 after:content-[''] after:absolute after:inset-y-2 after:left-1 after:w-0.5 after:rounded-full after:bg-sky-400/80"
            : 'text-slate-200/85 hover:bg-white/5 hover:text-white/95',
    ].join(' ');
});

const iconClasses = computed(() => {
    return [
        'h-5 w-5 shrink-0 transition-colors duration-150',
        active.value ? 'text-sky-300' : 'text-slate-200/80 group-hover:text-white/90',
    ].join(' ');
});
</script>

<template>
    <Link :href="item.href" :class="linkClasses" :title="isCollapsed ? item.label : undefined" @click="emit('navigate')">
        <SidebarIcon :name="item.icon" :class="iconClasses" />
        <span :class="isCollapsed ? 'md:hidden' : ''">{{ item.label }}</span>
    </Link>
</template>

