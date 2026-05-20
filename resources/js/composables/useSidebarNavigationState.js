import { ref, watch } from 'vue';

const STORAGE_KEY = 'ips.sidebar.dropdown.open.v1';

function readFromStorage() {
    if (typeof window === 'undefined') return {};
    try {
        const raw = window.localStorage.getItem(STORAGE_KEY);
        if (!raw) return {};
        const parsed = JSON.parse(raw);
        return parsed && typeof parsed === 'object' ? parsed : {};
    } catch {
        return {};
    }
}

function writeToStorage(value) {
    if (typeof window === 'undefined') return;
    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(value ?? {}));
    } catch {
        // ignore storage failures (private mode, blocked, etc.)
    }
}

// Module-level singleton so state persists across navigations/layout remounts.
const openMap = ref(readFromStorage());
const watchRegistered = ref(false);

export function useSidebarNavigationState() {
    if (!watchRegistered.value) {
        watchRegistered.value = true;
        watch(openMap, (value) => writeToStorage(value), { deep: true, flush: 'post' });
    }

    function isOpen(id) {
        return Boolean(openMap.value?.[id]);
    }

    function setOpen(id, value) {
        if (!id) return;
        openMap.value = {
            ...(openMap.value ?? {}),
            [id]: Boolean(value),
        };
    }

    function toggle(id) {
        setOpen(id, !isOpen(id));
    }

    return {
        openMap,
        isOpen,
        setOpen,
        toggle,
    };
}

