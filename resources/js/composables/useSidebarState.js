import { onMounted, ref, watch } from 'vue';

const STORAGE_KEY = 'ips.sidebar.collapsed.v1';

function readCollapsedFromStorage() {
    if (typeof window === 'undefined') return false;
    try {
        return window.localStorage.getItem(STORAGE_KEY) === '1';
    } catch {
        return false;
    }
}

function writeCollapsedToStorage(value) {
    if (typeof window === 'undefined') return;
    try {
        window.localStorage.setItem(STORAGE_KEY, value ? '1' : '0');
    } catch {
        // ignore storage failures (private mode, blocked, etc.)
    }
}

// Module-level singleton state so Sidebar & AppLayout stay in sync.
const isCollapsed = ref(readCollapsedFromStorage());
const isMobileOpen = ref(false);
const hydrated = ref(false);
const watchRegistered = ref(false);

export function useSidebarState() {
    onMounted(() => {
        // Always reset mobile drawer when a new page/layout mounts.
        isMobileOpen.value = false;

        if (hydrated.value) return;
        hydrated.value = true;
    });

    if (!watchRegistered.value) {
        watchRegistered.value = true;
        watch(isCollapsed, (value) => writeCollapsedToStorage(value), { flush: 'post' });
    }

    function toggleCollapsed() {
        isCollapsed.value = !isCollapsed.value;
    }

    function openMobile() {
        isMobileOpen.value = true;
    }

    function closeMobile() {
        isMobileOpen.value = false;
    }

    function toggleMobile() {
        isMobileOpen.value = !isMobileOpen.value;
    }

    return {
        isCollapsed,
        isMobileOpen,
        toggleCollapsed,
        openMobile,
        closeMobile,
        toggleMobile,
    };
}
