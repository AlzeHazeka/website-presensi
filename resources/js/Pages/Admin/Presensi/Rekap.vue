<script setup>
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import { route } from '../../../lib/route';
import StatusBadge from '../../../Components/UI/StatusBadge.vue';
import EmptyState from '../../../Components/UI/EmptyState.vue';
import { useTableSort } from '../../../composables/useTableSort';

const props = defineProps({
    rekap: {
        type: Array,
        required: true,
    },
    bulan: {
        type: String,
        required: true,
    },
    tahun: {
        type: String,
        required: true,
    },
    summary: {
        type: Object,
        required: true,
    },
});

const selectedBulan = ref(props.bulan);
const selectedTahun = ref(props.tahun);
const page = usePage();
const authz = computed(() => page.props.authz ?? {});
const canExportPdf = computed(() => !!authz.value?.canExportReportPdf);
const canExportExcel = computed(() => !!authz.value?.canExportReportExcel);

watch(
    () => props.bulan,
    (value) => {
        if (value && value !== selectedBulan.value) selectedBulan.value = value;
    },
);
watch(
    () => props.tahun,
    (value) => {
        if (value && value !== selectedTahun.value) selectedTahun.value = value;
    },
);

function monthLabel(month) {
    const date = new Date(2000, month - 1, 1);
    return new Intl.DateTimeFormat('id-ID', { month: 'long' }).format(date);
}

function pad2(value) {
    return String(value).padStart(2, '0');
}

const monthHumanLabel = computed(() => `${monthLabel(Number.parseInt(selectedBulan.value, 10) || 1)} ${selectedTahun.value}`);

function compareNullableNumber(a, b) {
    const av = a ?? null;
    const bv = b ?? null;
    if (av === null && bv === null) return 0;
    if (av === null) return 1;
    if (bv === null) return -1;
    return av - bv;
}

const comparators = {
    nama: (a, b) => String(a?.nama ?? '').localeCompare(String(b?.nama ?? ''), 'id-ID', { sensitivity: 'base' }),
    jumlah_presensi: (a, b) => compareNullableNumber(a?.jumlah_presensi, b?.jumlah_presensi),
    jumlah_lembur: (a, b) => compareNullableNumber(a?.jumlah_lembur, b?.jumlah_lembur),
    kehadiran_pct: (a, b) => compareNullableNumber(a?.kehadiran_pct, b?.kehadiran_pct),
};

const { sortedItems: sortedRekap, sortKey, sortDirection, toggleSort, isActive } = useTableSort(computed(() => props.rekap), { comparators });

function sortIndicator(key) {
    if (!isActive(key)) return '';
    return sortDirection.value === 'asc' ? 'asc' : sortDirection.value === 'desc' ? 'desc' : '';
}

function statusTone(status) {
    const s = String(status ?? '').toLowerCase();
    if (s === 'aktif') return 'success';
    if (s === 'nonaktif' || s === 'inactive') return 'danger';
    return 'slate';
}

function submit() {
    router.get(
        route('admin.presensi.rekap.presensi'),
        { bulan: selectedBulan.value, tahun: selectedTahun.value },
        { preserveScroll: true, preserveState: true },
    );
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Rekap Presensi Bulanan</h2>
                <p class="mt-1 text-sm text-slate-600">Operational monthly attendance dashboard (seluruh karyawan).</p>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Toolbar -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8 space-y-5">
                    <form class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between" @submit.prevent="submit">
                        <div
                            class="inline-flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200"
                        >
                            <span class="inline-flex h-2 w-2 rounded-full bg-sky-500/70" />
                            <span class="truncate">Periode: {{ monthHumanLabel }}</span>
                            <span class="text-slate-400">•</span>
                            <span>{{ summary.daysInMonth }} hari</span>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-end">
                            <div class="w-full sm:w-[180px]">
                                <label class="sr-only" for="bulan">Bulan</label>
                                <select
                                    id="bulan"
                                    v-model="selectedBulan"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-400/30"
                                >
                                    <option v-for="i in 12" :key="i" :value="pad2(i)">{{ monthLabel(i) }}</option>
                                </select>
                            </div>
                            <div class="w-full sm:w-[130px]">
                                <label class="sr-only" for="tahun">Tahun</label>
                                <input
                                    id="tahun"
                                    v-model="selectedTahun"
                                    type="number"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-400/30"
                                />
                            </div>
                            <button
                                type="submit"
                                class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-sky-500 px-5 text-sm font-semibold text-white shadow-sm hover:bg-sky-400 active:bg-sky-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40 sm:w-auto"
                            >
                                Tampilkan
                            </button>
                            <a
                                v-if="canExportExcel"
                                :href="route('admin.presensi.rekap.export', { bulan: selectedBulan, tahun: selectedTahun })"
                                class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-emerald-500 px-5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-400 active:bg-emerald-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400/40 sm:w-auto"
                            >
                                Export Excel
                            </a>
                            <a
                                v-if="canExportPdf"
                                :href="route('admin.presensi.rekap.pdf', { bulan: selectedBulan, tahun: selectedTahun })"
                                class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-sm hover:bg-rose-400 active:bg-rose-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400/40 sm:w-auto"
                            >
                                Export PDF
                            </a>
                        </div>
                    </form>

                    <!-- Summary cards -->
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Karyawan aktif</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.totalKaryawanAktif }}</div>
                            <div class="mt-1 text-xs text-slate-600">Dari {{ summary.totalKaryawan }} karyawan</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Total presensi</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.totalPresensi }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Total izin/cuti</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.totalIzin }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Total lembur</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.totalLembur }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Rata-rata kehadiran</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.avgKehadiranPct }}%</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Table -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 sm:px-8 sm:py-5 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-base font-semibold text-slate-900">Tabel Rekap</div>
                        <div class="mt-1 text-sm text-slate-600">Klik header untuk sorting ringan.</div>
                    </div>
                    <div class="shrink-0 text-xs text-slate-500">
                        Sort: <span class="font-semibold text-slate-700">{{ sortKey || 'default' }}</span>
                    </div>
                </div>

                <div class="border-t border-slate-200">
                    <div v-if="sortedRekap.length === 0" class="p-6 sm:p-8">
                        <EmptyState title="Tidak ada data rekap presensi pada periode ini." description="Coba ganti bulan/tahun, atau pastikan data sudah tersedia." />
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-[1100px] w-full">
                            <thead class="bg-slate-50 text-slate-700">
                                <tr class="text-xs font-semibold uppercase tracking-wider">
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-left">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('nama')">
                                            Nama
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('nama') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('nama') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-left">Divisi/Posisi</th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-left">Role</th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-left">Status</th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-center">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('jumlah_presensi')">
                                            Presensi
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('jumlah_presensi') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('jumlah_presensi') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-center">Izin/Cuti</th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-center">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('jumlah_lembur')">
                                            Lembur
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('jumlah_lembur') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('jumlah_lembur') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-center">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('kehadiran_pct')">
                                            Kehadiran %
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('kehadiran_pct') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('kehadiran_pct') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="r in sortedRekap" :key="r.user_id" class="hover:bg-slate-50/70 transition">
                                    <td class="px-4 py-4 text-sm font-semibold text-slate-900">{{ r.nama }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-700">{{ r.posisi ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-700">{{ r.role ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        <StatusBadge :label="r.status ?? '-'" :tone="statusTone(r.status)" />
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm font-semibold text-slate-900">{{ r.jumlah_presensi }}</td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-800">{{ r.jumlah_izin }}</td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-800">{{ r.jumlah_lembur }}</td>
                                    <td class="px-4 py-4 text-center text-sm font-semibold text-slate-900">{{ r.kehadiran_pct }}%</td>
                                    <td class="px-4 py-4 text-center">
                                        <a
                                            :href="route('admin.presensi.by-user', { user_id: r.user_id, bulan: Number(selectedBulan), tahun: Number(selectedTahun) })"
                                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40"
                                        >
                                            Detail
                                        </a>
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
