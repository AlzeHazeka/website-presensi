<script setup>
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import { route } from '../../../lib/route';
import StatusBadge from '../../../Components/UI/StatusBadge.vue';
import EmptyState from '../../../Components/UI/EmptyState.vue';
import EmployeeSelectorModal from '../../../Components/Selector/EmployeeSelectorModal.vue';
import { useTableSort } from '../../../composables/useTableSort';

const props = defineProps({
    users: {
        type: Array,
        required: true,
    },
    selectedUser: {
        type: Object,
        default: null,
    },
    presensi: {
        type: Array,
        required: true,
    },
    bulan: {
        type: Number,
        required: true,
    },
    tahun: {
        type: Number,
        required: true,
    },
    userId: {
        type: [Number, null],
        default: null,
    },
    jumlahHari: {
        type: Number,
        required: true,
    },
    izinDates: {
        type: Array,
        default: () => [],
    },
    lemburDates: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const flashError = computed(() => page.props.flash?.error ?? '');
const authz = computed(() => page.props.authz ?? {});
const canEditPresensi = computed(() => !!authz.value?.canEditPresensi);

const users = computed(() => props.users);
const selectedUser = computed(() => props.selectedUser);

const selectedUserId = ref(props.userId ?? '');
const selectedMonth = ref(props.bulan);
const selectedYear = ref(props.tahun);

watch(
    () => props.userId,
    (value) => {
        if (value === null || value === undefined) {
            selectedUserId.value = '';
            return;
        }
        if (String(value) !== String(selectedUserId.value)) selectedUserId.value = value;
    },
);

function monthLabel(month) {
    const date = new Date(2000, month - 1, 1);
    return new Intl.DateTimeFormat('id-ID', { month: 'long' }).format(date);
}

const years = computed(() => {
    const start = 2023;
    const end = new Date().getFullYear() + 1;
    const results = [];
    for (let year = start; year <= end; year += 1) results.push(year);
    return results;
});

function formatTanggal(ymd) {
    const date = new Date(`${ymd}T00:00:00`);
    if (Number.isNaN(date.getTime())) return ymd;
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(date);
}

const isEmployeeSelected = computed(() => Boolean(selectedUser.value?.user_id));

const izinDateSet = computed(() => new Set((props.izinDates ?? []).map((d) => String(d))));
const lemburDateSet = computed(() => new Set((props.lemburDates ?? []).map((d) => String(d))));

function parseTimeToMinutes(hhmm) {
    if (!hhmm || typeof hhmm !== 'string') return null;
    const [h, m] = hhmm.split(':').map((x) => Number.parseInt(x, 10));
    if (Number.isNaN(h) || Number.isNaN(m)) return null;
    return h * 60 + m;
}

function compareNullableNumber(a, b) {
    const av = a ?? null;
    const bv = b ?? null;
    if (av === null && bv === null) return 0;
    if (av === null) return 1;
    if (bv === null) return -1;
    return av - bv;
}

const comparators = {
    tanggal: (a, b) => String(a?.tanggal ?? '').localeCompare(String(b?.tanggal ?? '')),
    jam_masuk: (a, b) => compareNullableNumber(parseTimeToMinutes(a?.jam_masuk), parseTimeToMinutes(b?.jam_masuk)),
    jam_keluar: (a, b) => compareNullableNumber(parseTimeToMinutes(a?.jam_keluar), parseTimeToMinutes(b?.jam_keluar)),
    total_minutes: (a, b) => compareNullableNumber(Number.isFinite(a?.total_minutes) ? a.total_minutes : null, Number.isFinite(b?.total_minutes) ? b.total_minutes : null),
};

const { sortedItems: sortedRows, sortKey, sortDirection, toggleSort, isActive } = useTableSort(computed(() => props.presensi), { comparators });

function sortIndicator(key) {
    if (!isActive(key)) return '';
    return sortDirection.value === 'asc' ? 'asc' : sortDirection.value === 'desc' ? 'desc' : '';
}

function rowStatus(row) {
    const dateKey = String(row?.tanggal ?? '');

    const hasIzin = izinDateSet.value.has(dateKey);
    if (hasIzin) return { label: 'Izin/Cuti', tone: 'warning' };

    const masuk = Boolean(row?.jam_masuk);
    const keluar = Boolean(row?.jam_keluar);

    if (masuk && keluar) return { label: 'Hadir lengkap', tone: 'success' };
    if (masuk && !keluar) return { label: 'Masuk saja', tone: 'warning' };

    return { label: 'Tidak presensi', tone: 'slate' };
}

function mapsHref(locationString) {
    if (!locationString) return '';
    return `https://www.google.com/maps?q=${encodeURIComponent(locationString)}`;
}

const monthHumanLabel = computed(() => `${monthLabel(selectedMonth.value)} ${selectedYear.value}`);

const summary = computed(() => {
    if (!isEmployeeSelected.value) {
        return {
            totalHari: props.jumlahHari ?? 0,
            hadirLengkap: 0,
            masukSaja: 0,
            izinCuti: 0,
            lembur: 0,
            tidakPresensi: 0,
        };
    }

    let hadirLengkap = 0;
    let masukSaja = 0;
    let tidakPresensi = 0;

    for (const row of props.presensi ?? []) {
        const tanggal = String(row?.tanggal ?? '');
        const hasIzin = izinDateSet.value.has(tanggal);
        const masuk = Boolean(row?.jam_masuk);
        const keluar = Boolean(row?.jam_keluar);

        if (hasIzin) continue;
        if (masuk && keluar) hadirLengkap += 1;
        else if (masuk && !keluar) masukSaja += 1;
        else if (!masuk) tidakPresensi += 1;
    }

    return {
        totalHari: props.jumlahHari ?? 0,
        hadirLengkap,
        masukSaja,
        izinCuti: props.izinDates?.length ?? 0,
        lembur: props.lemburDates?.length ?? 0,
        tidakPresensi,
    };
});

const isModalOpen = ref(false);

function openModal() {
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
}

function handleSelectEmployee(user) {
    selectedUserId.value = user?.user_id ?? '';
}

function submit() {
    router.get(
        route('admin.presensi.by-user'),
        {
            user_id: selectedUserId.value || undefined,
            bulan: selectedMonth.value,
            tahun: selectedYear.value,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Rekap Presensi Bulanan per Karyawan</h2>
                <p class="mt-1 text-sm text-slate-600">Lihat status presensi seorang karyawan pada bulan tertentu.</p>
            </div>
        </template>

        <EmployeeSelectorModal
            :show="isModalOpen"
            :users="users"
            :selected-user-id="selectedUserId"
            @close="closeModal"
            @select="handleSelectEmployee"
        />

        <div class="max-w-7xl mx-auto space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8 space-y-5">
                    <div v-if="flashError" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        {{ flashError }}
                    </div>

                    <form class="space-y-4" @submit.prevent="submit">
                        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                            <div
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200"
                            >
                                <span class="inline-flex h-2 w-2 rounded-full bg-sky-500/70" />
                                <span class="truncate">Bulan aktif: {{ monthHumanLabel }}</span>
                                <span class="text-slate-400">•</span>
                                <span>{{ jumlahHari }} hari</span>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-end">
                                <div class="w-full sm:w-[320px]">
                                    <label class="sr-only" for="karyawan">Karyawan</label>
                                    <button
                                        id="karyawan"
                                        type="button"
                                        class="inline-flex h-11 w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/30"
                                        @click="openModal"
                                    >
                                        <span class="truncate">
                                            <template v-if="selectedUser">{{ selectedUser.nama }}</template>
                                            <template v-else>Pilih karyawan…</template>
                                        </span>
                                        <span class="shrink-0 text-slate-400">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    </button>
                                    <div v-if="selectedUser?.posisi || selectedUser?.role" class="mt-1 text-xs text-slate-600">
                                        {{ selectedUser.posisi || selectedUser.role }}
                                    </div>
                                </div>

                                <div class="w-full sm:w-[170px]">
                                    <label class="sr-only" for="bulan">Bulan</label>
                                    <select
                                        id="bulan"
                                        v-model.number="selectedMonth"
                                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-400/30"
                                    >
                                        <option v-for="month in 12" :key="month" :value="month">{{ monthLabel(month) }}</option>
                                    </select>
                                </div>

                                <div class="w-full sm:w-[130px]">
                                    <label class="sr-only" for="tahun">Tahun</label>
                                    <select
                                        id="tahun"
                                        v-model.number="selectedYear"
                                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-400/30"
                                    >
                                        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                                    </select>
                                </div>

                                <button
                                    type="submit"
                                    class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-sky-500 px-5 text-sm font-semibold text-white shadow-sm hover:bg-sky-400 active:bg-sky-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40 sm:w-auto"
                                >
                                    Tampilkan Rekap
                                </button>
                            </div>
                        </div>
                    </form>

                    <div v-if="isEmployeeSelected" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Hadir lengkap</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.hadirLengkap }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Masuk saja</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.masukSaja }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Izin/Cuti</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.izinCuti }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Lembur</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.lembur }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Tidak presensi</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.tidakPresensi }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 sm:px-8 sm:py-5 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-base font-semibold text-slate-900">Tabel Presensi Bulanan</div>
                        <div class="mt-1 text-sm text-slate-600">
                            <template v-if="isEmployeeSelected">Klik header untuk sorting ringan.</template>
                            <template v-else>Pilih karyawan untuk melihat rekap.</template>
                        </div>
                    </div>
                    <div v-if="isEmployeeSelected" class="shrink-0 text-xs text-slate-500">
                        Sort: <span class="font-semibold text-slate-700">{{ sortKey || 'default' }}</span>
                    </div>
                </div>

                <div class="border-t border-slate-200">
                    <div v-if="!isEmployeeSelected" class="p-6 sm:p-8">
                        <EmptyState title="Pilih karyawan untuk melihat rekap presensi bulanan." description="Gunakan tombol Pilih Karyawan di toolbar." />
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-[1050px] w-full">
                            <thead class="bg-slate-50 text-slate-700">
                                <tr class="text-xs font-semibold uppercase tracking-wider">
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-left">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('tanggal')">
                                            Tanggal
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('tanggal') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('tanggal') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-left">Hari</th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-left">Status</th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-center">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('jam_masuk')">
                                            Jam Masuk
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('jam_masuk') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('jam_masuk') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-left">Lokasi Masuk</th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-center">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('jam_keluar')">
                                            Jam Keluar
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('jam_keluar') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('jam_keluar') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-left">Lokasi Keluar</th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-center">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('total_minutes')">
                                            Total Jam
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('total_minutes') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('total_minutes') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="row in sortedRows" :key="row.tanggal" class="hover:bg-slate-50/70 transition">
                                    <td class="px-4 py-4 text-sm font-semibold text-slate-900">{{ formatTanggal(row.tanggal) }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-700">{{ row.hari }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <StatusBadge :label="rowStatus(row).label" :tone="rowStatus(row).tone" />
                                            <StatusBadge v-if="lemburDateSet.has(String(row.tanggal))" label="Lembur" tone="info" />
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-800">{{ row.jam_masuk ?? '—' }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-700">
                                        <a
                                            v-if="row.lokasi_masuk"
                                            :href="mapsHref(row.lokasi_masuk)"
                                            target="_blank"
                                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                                        >
                                            <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Maps
                                        </a>
                                        <span v-else class="text-xs text-slate-500">Tidak tersedia</span>
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-800">{{ row.jam_keluar ?? '—' }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-700">
                                        <a
                                            v-if="row.lokasi_keluar"
                                            :href="mapsHref(row.lokasi_keluar)"
                                            target="_blank"
                                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                                        >
                                            <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Maps
                                        </a>
                                        <span v-else class="text-xs text-slate-500">Tidak tersedia</span>
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm font-semibold text-slate-900">{{ row.total_jam_text }}</td>
                                    <td class="px-4 py-4 text-center">
                                        <a
                                            v-if="canEditPresensi && row.id_presensi"
                                            :href="route('admin.presensi.edit', row.id_presensi)"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40"
                                        >
                                            <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </a>
                                        <span v-else class="text-xs text-slate-400">-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
