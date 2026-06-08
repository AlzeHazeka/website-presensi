<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '../../../Layouts/AppLayout.vue';
import { route } from '../../../lib/route';
import DatePickerField from '../../../Components/Forms/DatePickerField.vue';
import StatusBadge from '../../../Components/UI/StatusBadge.vue';
import EmptyState from '../../../Components/UI/EmptyState.vue';
import { useTableSort } from '../../../composables/useTableSort';

const props = defineProps({
    tanggal: {
        type: String,
        required: true,
    },
    presensi: {
        type: Array,
        required: true,
    },
    total: {
        type: Number,
        required: true,
    },
    summary: {
        type: Object,
        required: true,
    },
});

const tanggalValue = ref(props.tanggal);
watch(
    () => props.tanggal,
    (value) => {
        if (value && value !== tanggalValue.value) tanggalValue.value = value;
    },
);

function submitTanggal() {
    router.get(
        route('admin.presensi.by-date'),
        { tanggal: tanggalValue.value },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
}

const page = usePage();
const authz = computed(() => page.props.authz ?? {});
const flashSuccess = computed(() => page.props.flash?.success ?? '');
const canExportPdf = computed(() => !!authz.value?.canExportReportPdf);
const canExportExcel = computed(() => !!authz.value?.canExportReportExcel);
const canEditPresensi = computed(() => !!authz.value?.canEditPresensi);

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
    nama: (a, b) => String(a?.nama ?? '').localeCompare(String(b?.nama ?? ''), 'id-ID', { sensitivity: 'base' }),
    status_hari_ini: (a, b) => String(a?.status_hari_ini?.label ?? '').localeCompare(String(b?.status_hari_ini?.label ?? ''), 'id-ID', { sensitivity: 'base' }),
    jam_masuk: (a, b) => compareNullableNumber(parseTimeToMinutes(a?.jam_masuk), parseTimeToMinutes(b?.jam_masuk)),
    jam_keluar: (a, b) => compareNullableNumber(parseTimeToMinutes(a?.jam_keluar), parseTimeToMinutes(b?.jam_keluar)),
    total_minutes: (a, b) => compareNullableNumber(Number.isFinite(a?.total_minutes) ? a.total_minutes : null, Number.isFinite(b?.total_minutes) ? b.total_minutes : null),
    lembur: (a, b) => compareNullableNumber(Number.isFinite(a?.lembur?.duration_minutes) ? a.lembur.duration_minutes : null, Number.isFinite(b?.lembur?.duration_minutes) ? b.lembur.duration_minutes : null),
};

const { sortedItems: sortedPresensi, sortKey, sortDirection, toggleSort, isActive } = useTableSort(computed(() => props.presensi), {
    comparators,
});

function sortIndicator(key) {
    if (!isActive(key)) return '';
    return sortDirection.value === 'asc' ? 'asc' : sortDirection.value === 'desc' ? 'desc' : '';
}

function mapsHref(locationString) {
    if (!locationString) return '';
    return `https://www.google.com/maps?q=${encodeURIComponent(locationString)}`;
}

function dash(value) {
    return value || '-';
}

function overtimeTimeText(lembur) {
    if (!lembur?.jam_mulai) return '-';
    if (!lembur?.jam_selesai) return `Mulai: ${lembur.jam_mulai}`;
    return `${lembur.jam_mulai} - ${lembur.jam_selesai}`;
}

onMounted(() => {
    if (!flashSuccess.value) return;

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: flashSuccess.value,
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true,
    });
});
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Rekap Harian Presensi</h2>
                <p class="mt-1 text-sm text-slate-600">Ringkasan presensi operasional berdasarkan tanggal.</p>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Toolbar -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8 space-y-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div class="min-w-0">
                            <div class="inline-flex items-center gap-2 text-xs font-semibold tracking-wider text-slate-500 uppercase">
                                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-sky-500/70" />
                                Rekap Harian
                            </div>
                            <div class="mt-2 text-sm text-slate-600">
                                Tanggal dipilih: <span class="font-semibold text-slate-900">{{ tanggalValue }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-end">
                            <div class="w-full sm:w-64">
                                <DatePickerField id="tanggal" v-model="tanggalValue" label="Pilih tanggal" :required="true" @update:modelValue="submitTanggal" />
                            </div>

                            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                                <a
                                    v-if="canExportPdf"
                                    :href="route('admin.presensi.export.pdf', { tanggal: tanggalValue })"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-500 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-rose-400 active:bg-rose-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400/40 sm:w-auto"
                                >
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v14l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z" />
                                    </svg>
                                    Export PDF
                                </a>
                                <a
                                    v-if="canExportExcel"
                                    :href="route('admin.presensi.export.excel', { tanggal: tanggalValue })"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-400 active:bg-emerald-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400/40 sm:w-auto"
                                >
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H6a2 2 0 01-2-2V7a2 2 0 012-2h3l2-2h2l2 2h3a2 2 0 012 2v12a2 2 0 01-2 2z" />
                                    </svg>
                                    Export Excel
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Mini summary -->
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Total hadir</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.total_hadir }}</div>
                            <div class="mt-1 text-xs text-slate-600">Presensi tercatat</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Belum checkout</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.total_belum_checkout }}</div>
                            <div class="mt-1 text-xs text-slate-600">Masuk saja</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Izin/Sakit/Cuti</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.total_izin_cuti }}</div>
                            <div class="mt-1 text-xs text-slate-600">Di tanggal ini</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Lembur</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.total_lembur }}</div>
                            <div class="mt-1 text-xs text-slate-600">Di tanggal ini</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Table -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 sm:px-8 sm:py-5 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-base font-semibold text-slate-900">Tabel Operasional Harian</div>
                        <div class="mt-1 text-sm text-slate-600">Klik header untuk sorting ringan.</div>
                    </div>
                    <div class="shrink-0 text-xs text-slate-500">
                        Sort: <span class="font-semibold text-slate-700">{{ sortKey || 'default' }}</span>
                    </div>
                </div>

                <div class="border-t border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-[1120px] w-full">
                            <thead class="bg-slate-50 text-slate-700">
                                <tr class="text-xs font-semibold uppercase tracking-wider">
                                    <th class="px-4 py-3 text-left">
                                        <button
                                            type="button"
                                            class="group inline-flex items-center gap-2 hover:text-slate-900"
                                            @click="toggleSort('nama')"
                                        >
                                            Nama
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('nama') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('nama') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-left">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('status_hari_ini')">
                                            Status Hari Ini
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('status_hari_ini') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('status_hari_ini') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-center">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('jam_masuk')">
                                            Jam Masuk
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('jam_masuk') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('jam_masuk') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-left">Lokasi Masuk</th>
                                    <th class="px-4 py-3 text-center">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('jam_keluar')">
                                            Jam Keluar
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('jam_keluar') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('jam_keluar') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-left">Lokasi Keluar</th>
                                    <th class="px-4 py-3 text-center">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('total_minutes')">
                                            Total Kerja
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('total_minutes') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('total_minutes') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-left">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('lembur')">
                                            Lembur
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('lembur') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('lembur') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="row in sortedPresensi"
                                    :key="row.user_id"
                                    class="hover:bg-slate-50/70 transition"
                                >
                                    <td class="px-4 py-4 text-sm font-semibold text-slate-900">
                                        {{ row.nama ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <StatusBadge :label="row.status_hari_ini?.label ?? '-'" :tone="row.status_hari_ini?.tone ?? 'slate'" />
                                            <StatusBadge v-if="row.has_lembur" label="Lembur" tone="info" />
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-800">
                                        {{ dash(row.jam_masuk) }}
                                    </td>
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
                                        <span v-else class="text-xs text-slate-500">-</span>
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-800">
                                        {{ dash(row.jam_keluar) }}
                                    </td>
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
                                        <span v-else class="text-xs text-slate-500">-</span>
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm font-semibold text-slate-900">
                                        {{ row.total_jam_text }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-700">
                                        <div v-if="row.lembur" class="space-y-1">
                                            <div class="font-semibold text-slate-900">{{ overtimeTimeText(row.lembur) }}</div>
                                            <div v-if="row.lembur.durasi_text" class="text-xs text-slate-600">{{ row.lembur.durasi_text }}</div>
                                            <StatusBadge
                                                v-if="row.lembur.status"
                                                :label="row.lembur.status.label"
                                                :tone="row.lembur.status.tone"
                                            />
                                        </div>
                                        <span v-else class="text-xs text-slate-500">-</span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <a
                                                v-if="canEditPresensi && row.id_presensi"
                                                :href="route('admin.presensi.edit', row.id_presensi)"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40"
                                            >
                                                <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit Presensi
                                            </a>
                                            <a
                                                v-if="canEditPresensi && row.lembur?.id_lembur"
                                                :href="route('admin.presensi.lembur.edit', row.lembur.id_lembur)"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40"
                                            >
                                                <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Edit Lembur
                                            </a>
                                            <span v-if="!canEditPresensi || (!row.id_presensi && !row.lembur?.id_lembur)" class="text-xs text-slate-500">—</span>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="sortedPresensi.length === 0">
                                    <td colspan="9" class="px-6 py-10">
                                        <EmptyState title="Tidak ada data presensi pada tanggal ini." description="Silakan pilih tanggal lain atau cek kembali input presensi." />
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
