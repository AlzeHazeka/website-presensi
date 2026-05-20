import { computed, ref, watch } from 'vue';
import { route } from '../lib/route';

function createDebounced(fn, delayMs) {
    let timer = null;
    return (...args) => {
        if (timer) clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delayMs);
    };
}

function createEmptyEligibility(date) {
    return {
        date: date ?? '',
        has_presensi: false,
        has_lembur: false,
        has_izin: false,
        blocked_by_activity: false,
        activity_message: null,
    };
}

export function useIzinEligibility(dateRef, { initialEligibility = null, debounceMs = 250 } = {}) {
    const eligibility = ref(initialEligibility ?? createEmptyEligibility(dateRef?.value ?? ''));
    const isChecking = ref(false);
    const lastError = ref('');

    let activeController = null;

    async function doCheck(date) {
        if (!date) return;

        if (activeController) {
            activeController.abort();
        }

        const controller = new AbortController();
        activeController = controller;

        isChecking.value = true;
        lastError.value = '';
        try {
            const url = route('izin.eligibility', { date });
            const response = await fetch(url, {
                method: 'GET',
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });

            const json = await response.json().catch(() => ({}));
            if (!response.ok || json?.status !== 'success') {
                lastError.value = json?.message ?? 'Gagal memeriksa status tanggal.';
                eligibility.value = createEmptyEligibility(date);
                return;
            }

            eligibility.value = json.data ?? createEmptyEligibility(date);
        } catch (error) {
            if (error?.name === 'AbortError') return;
            lastError.value = 'Gagal memeriksa status tanggal.';
            eligibility.value = createEmptyEligibility(date);
        } finally {
            if (activeController === controller) {
                activeController = null;
            }
            isChecking.value = false;
        }
    }

    const debouncedCheck = createDebounced(doCheck, debounceMs);

    watch(
        dateRef,
        (date) => {
            if (!date) return;
            if (eligibility.value?.date === date && !lastError.value) return;
            debouncedCheck(date);
        },
        { immediate: true },
    );

    const blockedByActivity = computed(() => Boolean(eligibility.value?.blocked_by_activity));
    const hasIzin = computed(() => Boolean(eligibility.value?.has_izin));

    const activityMessage = computed(() => eligibility.value?.activity_message ?? '');
    const infoMessage = computed(() => {
        if (blockedByActivity.value) return activityMessage.value;
        if (hasIzin.value) return 'Anda sudah mengajukan izin untuk tanggal ini.';
        return '';
    });

    return {
        eligibility,
        isChecking,
        lastError,
        blockedByActivity,
        hasIzin,
        infoMessage,
        checkNow: doCheck,
    };
}

