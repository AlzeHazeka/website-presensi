import { onBeforeUnmount, onMounted, ref } from 'vue';

export function useMediaQuery(query) {
    const matches = ref(false);

    function update(value) {
        matches.value = Boolean(value);
    }

    onMounted(() => {
        if (typeof window === 'undefined' || typeof window.matchMedia !== 'function') {
            update(false);
            return;
        }

        const media = window.matchMedia(query);
        update(media.matches);

        const handler = (event) => update(event.matches);
        if (typeof media.addEventListener === 'function') {
            media.addEventListener('change', handler);
        } else {
            media.addListener(handler);
        }

        onBeforeUnmount(() => {
            if (typeof media.removeEventListener === 'function') {
                media.removeEventListener('change', handler);
            } else {
                media.removeListener(handler);
            }
        });
    });

    return matches;
}

