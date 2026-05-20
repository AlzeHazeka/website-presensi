<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    users: { type: Array, default: () => [] },
    selectedUserId: { type: [Number, String, null], default: null },
    title: { type: String, default: 'Pilih Karyawan' },
});

const emit = defineEmits(['close', 'select']);

const keyword = ref('');

watch(
    () => props.show,
    (value) => {
        if (!value) return;
        keyword.value = '';
    },
);

function initialsOf(name) {
    const safe = String(name ?? '').trim();
    if (!safe) return '?';
    const parts = safe.split(/\s+/).filter(Boolean);
    const first = parts[0]?.[0] ?? '?';
    const second = parts.length > 1 ? parts[parts.length - 1]?.[0] ?? '' : '';
    return `${String(first).toUpperCase()}${String(second).toUpperCase()}`;
}

const filteredUsers = computed(() => {
    const list = Array.isArray(props.users) ? props.users : [];
    const q = keyword.value.trim().toLowerCase();
    if (!q) return list;
    return list.filter((u) => {
        const name = String(u?.nama ?? '').toLowerCase();
        const role = String(u?.role ?? '').toLowerCase();
        const posisi = String(u?.posisi ?? '').toLowerCase();
        return name.includes(q) || role.includes(q) || posisi.includes(q);
    });
});

function selectUser(user) {
    emit('select', user);
    emit('close');
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
        <div class="fixed inset-0 bg-slate-950/50" @click.self="emit('close')" />

        <div class="relative w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">
                <div class="min-w-0">
                    <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Selector</div>
                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ title }}</div>
                    <div class="mt-1 text-sm text-slate-600">Cari nama / role / posisi.</div>
                </div>
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-600 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40"
                    aria-label="Tutup"
                    @click="emit('close')"
                >
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-5 py-4 sm:px-6">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z" />
                        </svg>
                    </span>
                    <input
                        v-model="keyword"
                        type="text"
                        class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-400/30"
                        placeholder="Cari karyawan…"
                    />
                </div>
            </div>

            <div class="max-h-[60vh] overflow-y-auto border-t border-slate-200">
                <div v-if="filteredUsers.length === 0" class="px-6 py-10 text-center text-sm text-slate-600">
                    Tidak ada karyawan yang cocok.
                </div>

                <button
                    v-for="user in filteredUsers"
                    :key="user.user_id"
                    type="button"
                    class="w-full px-5 py-3 sm:px-6 flex items-center gap-3 text-left hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/30"
                    @click="selectUser(user)"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500/10 text-sky-700 ring-1 ring-sky-500/15 font-bold text-sm"
                    >
                        {{ initialsOf(user.nama) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <div class="truncate text-sm font-semibold text-slate-900">{{ user.nama }}</div>
                            <span
                                v-if="String(user.user_id) === String(selectedUserId)"
                                class="inline-flex items-center rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-500/15"
                            >
                                Terpilih
                            </span>
                        </div>
                        <div class="mt-0.5 truncate text-xs text-slate-600">
                            <span v-if="user.posisi">{{ user.posisi }}</span>
                            <span v-else-if="user.role">{{ user.role }}</span>
                            <span v-else class="text-slate-500">-</span>
                        </div>
                    </div>
                    <div class="shrink-0 text-slate-400">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </button>
            </div>

            <div class="border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6 flex items-center justify-between">
                <div class="text-xs text-slate-600">Total: <span class="font-semibold text-slate-900">{{ users.length }}</span> karyawan</div>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40"
                    @click="emit('close')"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>

