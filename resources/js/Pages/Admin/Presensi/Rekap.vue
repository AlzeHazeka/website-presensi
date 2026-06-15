<script setup>
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import { route } from '../../../lib/route';
import StatusBadge from '../../../Components/UI/StatusBadge.vue';
import EmptyState from '../../../Components/UI/EmptyState.vue';
import DatePickerField from '../../../Components/Forms/DatePickerField.vue';
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
    periodType: {
        type: String,
        default: 'month',
    },
    startDate: {
        type: [String, null],
        default: null,
    },
    endDate: {
        type: [String, null],
        default: null,
    },
    periodLabel: {
        type: String,
        default: '',
    },
    reportTitle: {
        type: String,
        default: 'Rekap Presensi Bulanan',
    },
    includeInactive: {
        type: Boolean,
        default: false,
    },
});

const selectedBulan = ref(props.bulan);
const selectedTahun = ref(props.tahun);
const selectedPeriodType = ref(props.periodType === 'range' ? 'range' : 'month');
const selectedStartDate = ref(props.startDate ?? '');
const selectedEndDate = ref(props.endDate ?? '');
const showInactiveEmployees = ref(props.includeInactive);
const page = usePage();
const flashError = computed(() => page.props.flash?.error ?? '');
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
watch(
    () => props.periodType,
    (value) => {
        selectedPeriodType.value = value === 'range' ? 'range' : 'month';
    },
);
watch(
    () => props.startDate,
    (value) => {
        selectedStartDate.value = value ?? '';
    },
);
watch(
    () => props.endDate,
    (value) => {
        selectedEndDate.value = value ?? '';
    },
);
watch(
    () => props.includeInactive,
    (value) => {
        showInactiveEmployees.value = !!value;
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
const isRangeMode = computed(() => selectedPeriodType.value === 'range');
const activePeriodShortText = computed(() => {
    if (isRangeMode.value) {
        return props.periodLabel || `${selectedStartDate.value} - ${selectedEndDate.value}`;
    }

    return monthHumanLabel.value;
});
const activePeriodText = computed(() => `Periode: ${activePeriodShortText.value}`);
const activeDays = computed(() => props.summary?.daysInPeriod ?? props.summary?.daysInMonth ?? 0);
const pageTitle = computed(() => (isRangeMode.value ? 'Rekap Presensi Periode' : 'Rekap Presensi Bulanan'));

function monthStartDate(month, year) {
    return `${year}-${pad2(month)}-01`;
}

function monthEndDate(month, year) {
    const date = new Date(Number(year), Number(month), 0);
    return `${year}-${pad2(month)}-${pad2(date.getDate())}`;
}

function setPeriodType(type) {
    selectedPeriodType.value = type;

    if (type === 'range') {
        selectedStartDate.value ||= monthStartDate(selectedBulan.value, selectedTahun.value);
        selectedEndDate.value ||= monthEndDate(selectedBulan.value, selectedTahun.value);
    }
}

function requestParams() {
    if (isRangeMode.value) {
        return {
            period_type: 'range',
            start_date: selectedStartDate.value || undefined,
            end_date: selectedEndDate.value || undefined,
            include_inactive: showInactiveEmployees.value ? 1 : undefined,
        };
    }

    return {
        bulan: selectedBulan.value,
        tahun: selectedTahun.value,
        include_inactive: showInactiveEmployees.value ? 1 : undefined,
    };
}

function detailParams(row) {
    return {
        user_id: row.user_id,
        ...requestParams(),
    };
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
    jumlah_presensi: (a, b) => compareNullableNumber(a?.jumlah_presensi, b?.jumlah_presensi),
    jumlah_lembur: (a, b) => compareNullableNumber(a?.jumlah_lembur, b?.jumlah_lembur),
    total_jam_kerja_minutes: (a, b) => compareNullableNumber(a?.total_jam_kerja_minutes, b?.total_jam_kerja_minutes),
    total_jam_lembur_minutes: (a, b) => compareNullableNumber(a?.total_jam_lembur_minutes, b?.total_jam_lembur_minutes),
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
    if (s === 'tidak aktif' || s === 'nonaktif' || s === 'inactive') return 'danger';
    return 'slate';
}

function submit() {
    router.get(
        route('admin.presensi.rekap.presensi'),
        requestParams(),
        { preserveScroll: true, preserveState: true },
    );
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">{{ pageTitle }}</h2>
                <p class="mt-1 text-sm text-slate-600">Dashboard operasional kehadiran seluruh karyawan.</p>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Toolbar -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-5 sm:p-6 space-y-5">
                    <div v-if="flashError" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        {{ flashError }}
                    </div>

                    <form class="space-y-4" @submit.prevent="submit">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                            <div
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200"
                            >
                                <span class="inline-flex h-2 w-2 rounded-full bg-sky-500/70" />
                                <span class="truncate">{{ activePeriodText }}</span>
                                <span class="text-slate-400">•</span>
                                <span>{{ activeDays }} hari</span>
                            </div>

                            <div class="w-full lg:w-auto">
                                <div class="mb-1 text-xs font-semibold tracking-wider text-slate-500 uppercase">Tipe Periode</div>
                                <div class="grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1 ring-1 ring-slate-200">
                                    <button
                                        type="button"
                                        class="h-9 rounded-lg px-3 text-sm font-semibold transition"
                                        :class="!isRangeMode ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                        @click="setPeriodType('month')"
                                    >
                                        Bulanan
                                    </button>
                                    <button
                                        type="button"
                                        class="h-9 rounded-lg px-3 text-sm font-semibold transition"
                                        :class="isRangeMode ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                        @click="setPeriodType('range')"
                                    >
                                        Custom Range
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="!isRangeMode" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div class="w-full sm:w-[180px]">
                                <label class="mb-1 block text-xs font-semibold text-slate-700" for="bulan">Bulan</label>
                                <select
                                    id="bulan"
                                    v-model="selectedBulan"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-400/30"
                                >
                                    <option v-for="i in 12" :key="i" :value="pad2(i)">{{ monthLabel(i) }}</option>
                                </select>
                            </div>
                            <div class="w-full sm:w-[130px]">
                                <label class="mb-1 block text-xs font-semibold text-slate-700" for="tahun">Tahun</label>
                                <input
                                    id="tahun"
                                    v-model="selectedTahun"
                                    type="number"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-400/30"
                                />
                            </div>
                            <button
                                type="submit"
                                class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-sky-500 px-4 text-sm font-semibold text-white shadow-sm hover:bg-sky-400 active:bg-sky-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40 sm:w-auto sm:min-w-[140px]"
                            >
                                Tampilkan
                            </button>
                            <a
                                v-if="canExportExcel"
                                :href="route('admin.presensi.rekap.export', requestParams())"
                                class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-emerald-500 px-5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-400 active:bg-emerald-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400/40 sm:w-auto sm:min-w-[132px]"
                            >
                                Export Excel
                            </a>
                            <a
                                v-if="canExportPdf"
                                :href="route('admin.presensi.rekap.pdf', requestParams())"
                                class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-sm hover:bg-rose-400 active:bg-rose-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400/40 sm:w-auto sm:min-w-[120px]"
                            >
                                Export PDF
                            </a>
                        </div>

                        <div v-else class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[180px_180px_auto_auto_auto] lg:items-end">
                            <DatePickerField id="start_date" v-model="selectedStartDate" label="Tanggal Mulai" placeholder="Pilih tanggal mulai" :required="true" />
                            <DatePickerField id="end_date" v-model="selectedEndDate" label="Tanggal Selesai" placeholder="Pilih tanggal selesai" :required="true" />
                            <button
                                type="submit"
                                class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-sky-500 px-4 text-sm font-semibold text-white shadow-sm hover:bg-sky-400 active:bg-sky-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40 sm:col-span-2 lg:col-span-1 lg:w-auto lg:min-w-[140px]"
                            >
                                Tampilkan
                            </button>
                            <a
                                v-if="canExportExcel"
                                :href="route('admin.presensi.rekap.export', requestParams())"
                                class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-emerald-500 px-5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-400 active:bg-emerald-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400/40 sm:col-span-2 lg:col-span-1 lg:w-auto lg:min-w-[132px]"
                            >
                                Export Excel
                            </a>
                            <a
                                v-if="canExportPdf"
                                :href="route('admin.presensi.rekap.pdf', requestParams())"
                                class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-sm hover:bg-rose-400 active:bg-rose-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400/40 sm:col-span-2 lg:col-span-1 lg:w-auto lg:min-w-[120px]"
                            >
                                Export PDF
                            </a>
                        </div>

                        <label class="inline-flex w-full cursor-pointer items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm sm:w-auto">
                            <span class="font-semibold text-slate-700">Tampilkan karyawan tidak aktif</span>
                            <input
                                v-model="showInactiveEmployees"
                                type="checkbox"
                                class="h-5 w-5 rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500"
                                @change="submit"
                            />
                        </label>
                    </form>

                    <div class="grid gap-3 md:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Karyawan aktif</div>
                            <div class="mt-1 text-xl font-semibold text-slate-900">{{ summary.totalKaryawanAktif }}</div>
                            <div class="mt-1 text-xs text-slate-600">
                                <template v-if="showInactiveEmployees">Dari {{ summary.totalKaryawan }} karyawan</template>
                                <template v-else>Nonaktif disembunyikan</template>
                            </div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Rata-rata kehadiran</div>
                            <div class="mt-1 text-xl font-semibold text-slate-900">{{ summary.avgKehadiranPct }}%</div>
                            <div class="mt-1 text-xs text-slate-600">Seluruh karyawan</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Periode aktif</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ activePeriodShortText }}</div>
                            <div class="mt-1 text-xs text-slate-600">{{ activeDays }} hari</div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-5">
                            <div class="flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2 ring-1 ring-slate-200">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Presensi</span>
                                <span class="font-semibold text-slate-900">{{ summary.totalPresensi }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2 ring-1 ring-slate-200">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Izin/Cuti</span>
                                <span class="font-semibold text-slate-900">{{ summary.totalIzin }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2 ring-1 ring-slate-200">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Lembur</span>
                                <span class="font-semibold text-slate-900">{{ summary.totalLembur }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2 ring-1 ring-slate-200">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Jam Kerja</span>
                                <span class="font-semibold text-slate-900">{{ summary.totalJamKerjaText }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2 ring-1 ring-slate-200">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Jam Lembur</span>
                                <span class="font-semibold text-slate-900">{{ summary.totalJamLemburText }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Table -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 sm:px-8 sm:py-5 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-base font-semibold text-slate-900">Tabel Rekap Seluruh Karyawan</div>
                        <div class="mt-1 text-sm text-slate-600">Data akumulasi presensi berdasarkan periode yang dipilih.</div>
                    </div>
                    <div class="shrink-0 text-xs text-slate-500">
                        Sort: <span class="font-semibold text-slate-700">{{ sortKey || 'default' }}</span>
                    </div>
                </div>

                <div class="border-t border-slate-200">
                    <div v-if="sortedRekap.length === 0" class="p-6 sm:p-8">
                        <EmptyState title="Tidak ada data rekap presensi pada periode ini." description="Coba ganti periode, atau pastikan data sudah tersedia." />
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-[1400px] w-full">
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
                                            Hari Hadir
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('jumlah_presensi') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('jumlah_presensi') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-center">Izin/Cuti</th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-center">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('jumlah_lembur')">
                                            Hari Lembur
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('jumlah_lembur') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('jumlah_lembur') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-center">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('total_jam_kerja_minutes')">
                                            Total Jam Kerja
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('total_jam_kerja_minutes') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('total_jam_kerja_minutes') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-center">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('total_jam_lembur_minutes')">
                                            Total Jam Lembur
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('total_jam_lembur_minutes') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('total_jam_lembur_minutes') === 'desc'">↓</template>
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
                                    <td class="px-4 py-4 text-center text-sm font-semibold text-slate-900">{{ r.total_jam_kerja_text }}</td>
                                    <td class="px-4 py-4 text-center text-sm font-semibold text-slate-900">{{ r.total_jam_lembur_text }}</td>
                                    <td class="px-4 py-4 text-center text-sm font-semibold text-slate-900">{{ r.kehadiran_pct }}%</td>
                                    <td class="px-4 py-4 text-center">
                                        <a
                                            :href="route('admin.presensi.by-user', detailParams(r))"
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
