import { computed, ref } from 'vue';

function stableSort(list, compare) {
    return list
        .map((item, index) => ({ item, index }))
        .sort((a, b) => {
            const result = compare(a.item, b.item);
            if (result !== 0) return result;
            return a.index - b.index;
        })
        .map((x) => x.item);
}

export function useTableSort(itemsRef, { comparators, defaultKey = '', defaultDirection = null } = {}) {
    const sortKey = ref(defaultKey);
    const sortDirection = ref(defaultDirection); // 'asc' | 'desc' | null

    function cycleDirection(current) {
        if (current === 'asc') return 'desc';
        if (current === 'desc') return null;
        return 'asc';
    }

    function toggleSort(key) {
        if (!key) return;
        if (sortKey.value !== key) {
            sortKey.value = key;
            sortDirection.value = 'asc';
            return;
        }

        sortDirection.value = cycleDirection(sortDirection.value);
        if (!sortDirection.value) {
            sortKey.value = '';
        }
    }

    const sortedItems = computed(() => {
        const items = itemsRef?.value ?? [];
        if (!Array.isArray(items) || !items.length) return items;

        if (!sortKey.value || !sortDirection.value) return items;

        const comparator = comparators?.[sortKey.value];
        if (typeof comparator !== 'function') return items;

        const multiplier = sortDirection.value === 'desc' ? -1 : 1;
        return stableSort(items, (a, b) => multiplier * comparator(a, b));
    });

    function isActive(key) {
        return sortKey.value === key && Boolean(sortDirection.value);
    }

    return {
        sortKey,
        sortDirection,
        sortedItems,
        toggleSort,
        isActive,
    };
}

