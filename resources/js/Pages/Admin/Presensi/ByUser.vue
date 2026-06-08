<script setup>
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import { route } from '../../../lib/route';
import StatusBadge from '../../../Components/UI/StatusBadge.vue';
import EmptyState from '../../../Components/UI/EmptyState.vue';
import EmployeeSelectorModal from '../../../Components/Selector/EmployeeSelectorModal.vue';
import DatePickerField from '../../../Components/Forms/DatePickerField.vue';
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
    summary: {
        type: Object,
        default: () => ({
            total_hadir_lengkap: 0,
            total_belum_checkout: 0,
            total_izin_cuti: 0,
            total_lembur: 0,
            total_tidak_hadir: 0,
            total_hari_hadir: 0,
            total_jam_kerja_minutes: 0,
            total_jam_lembur_minutes: 0,
        }),
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
});

const page = usePage();
const flashError = computed(() => page.props.flash?.error ?? '');
const authz = computed(() => page.props.authz ?? {});
const canEditPresensi = computed(() => !!authz.value?.canEditPresensi);

const users = computed(() => props.users);

const selectedUserId = ref(props.userId ?? '');
const selectedUserOption = ref(props.selectedUser);
const selectedMonth = ref(props.bulan);
const selectedYear = ref(props.tahun);
const selectedPeriodType = ref(props.periodType === 'range' ? 'range' : 'month');
const selectedStartDate = ref(props.startDate ?? '');
const selectedEndDate = ref(props.endDate ?? '');

watch(
    () => props.userId,
    (value) => {
        if (value === null || value === undefined) {
            selectedUserId.value = '';
            selectedUserOption.value = null;
            return;
        }
        if (String(value) !== String(selectedUserId.value)) selectedUserId.value = value;
    },
);

watch(
    () => props.selectedUser,
    (value) => {
        selectedUserOption.value = value;
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

function monthLabel(month) {
    const date = new Date(2000, month - 1, 1);
    return new Intl.DateTimeFormat('id-ID', { month: 'long' }).format(date);
}

function padNumber(value) {
    return String(value).padStart(2, '0');
}

function monthStartDate(month, year) {
    return `${year}-${padNumber(month)}-01`;
}

function monthEndDate(month, year) {
    const date = new Date(year, month, 0);
    return `${year}-${padNumber(month)}-${padNumber(date.getDate())}`;
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

const displaySelectedUser = computed(() => {
    if (!selectedUserId.value) return null;

    if (selectedUserOption.value && String(selectedUserOption.value.user_id) === String(selectedUserId.value)) {
        return selectedUserOption.value;
    }

    return users.value.find((user) => String(user?.user_id) === String(selectedUserId.value)) ?? null;
});
const isEmployeeSelected = computed(() => Boolean(displaySelectedUser.value?.user_id));
const tableTitle = computed(() => (displaySelectedUser.value?.nama ? `Tabel Presensi ${displaySelectedUser.value.nama}` : 'Tabel Presensi Karyawan'));

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
    status_hari_ini: (a, b) => String(a?.status_hari_ini?.label ?? '').localeCompare(String(b?.status_hari_ini?.label ?? '')),
    jam_masuk: (a, b) => compareNullableNumber(parseTimeToMinutes(a?.jam_masuk), parseTimeToMinutes(b?.jam_masuk)),
    jam_keluar: (a, b) => compareNullableNumber(parseTimeToMinutes(a?.jam_keluar), parseTimeToMinutes(b?.jam_keluar)),
    total_minutes: (a, b) => compareNullableNumber(Number.isFinite(a?.total_minutes) ? a.total_minutes : null, Number.isFinite(b?.total_minutes) ? b.total_minutes : null),
    lembur: (a, b) => compareNullableNumber(Number.isFinite(a?.lembur?.duration_minutes) ? a.lembur.duration_minutes : null, Number.isFinite(b?.lembur?.duration_minutes) ? b.lembur.duration_minutes : null),
};

const { sortedItems: sortedRows, sortKey, sortDirection, toggleSort, isActive } = useTableSort(computed(() => props.presensi), { comparators });

function sortIndicator(key) {
    if (!isActive(key)) return '';
    return sortDirection.value === 'asc' ? 'asc' : sortDirection.value === 'desc' ? 'desc' : '';
}

function mapsHref(locationString) {
    if (!locationString) return '';
    return `https://www.google.com/maps?q=${encodeURIComponent(locationString)}`;
}

const monthHumanLabel = computed(() => `${monthLabel(selectedMonth.value)} ${selectedYear.value}`);
const isRangeMode = computed(() => selectedPeriodType.value === 'range');
const activePeriodText = computed(() => {
    if (isRangeMode.value) {
        return `Periode aktif: ${props.periodLabel || `${selectedStartDate.value} - ${selectedEndDate.value}`}`;
    }

    return `Bulan aktif: ${monthHumanLabel.value}`;
});
const activePeriodShortText = computed(() => {
    if (isRangeMode.value) {
        return props.periodLabel || `${selectedStartDate.value} - ${selectedEndDate.value}`;
    }

    return monthHumanLabel.value;
});

const summary = computed(() => ({
    hadirLengkap: props.summary?.total_hadir_lengkap ?? 0,
    belumCheckout: props.summary?.total_belum_checkout ?? 0,
    izinCuti: props.summary?.total_izin_cuti ?? 0,
    lembur: props.summary?.total_lembur ?? 0,
    tidakPresensi: props.summary?.total_tidak_hadir ?? 0,
}));

function durationFromMinutes(minutes) {
    const safeMinutes = Number.isFinite(Number(minutes)) ? Math.max(0, Number(minutes)) : 0;
    if (safeMinutes <= 0) return '0 Jam';

    const hours = Math.floor(safeMinutes / 60);
    const remainingMinutes = safeMinutes % 60;

    if (remainingMinutes === 0) return `${hours} Jam`;
    if (hours === 0) return `${remainingMinutes} Menit`;
    return `${hours} Jam ${remainingMinutes} Menit`;
}

const periodTotals = computed(() => ({
    totalHariHadir: props.summary?.total_hari_hadir ?? summary.value.hadirLengkap + summary.value.belumCheckout,
    totalJamKerja: durationFromMinutes(props.summary?.total_jam_kerja_minutes ?? 0),
    totalHariLembur: props.summary?.total_lembur ?? 0,
    totalJamLembur: durationFromMinutes(props.summary?.total_jam_lembur_minutes ?? 0),
    totalIzinCuti: props.summary?.total_izin_cuti ?? 0,
    totalTidakHadir: props.summary?.total_tidak_hadir ?? 0,
}));

function dash(value) {
    return value || '-';
}

function overtimeTimeText(lembur) {
    if (!lembur) return '-';
    if (lembur.jam_mulai && lembur.jam_selesai) return `${lembur.jam_mulai} - ${lembur.jam_selesai}`;
    if (lembur.jam_mulai) return `Mulai: ${lembur.jam_mulai}`;
    return '-';
}

const isModalOpen = ref(false);

function openModal() {
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
}

function handleSelectEmployee(user) {
    selectedUserId.value = user?.user_id ?? '';
    selectedUserOption.value = user ?? null;
}

function setPeriodType(type) {
    selectedPeriodType.value = type;

    if (type === 'range') {
        selectedStartDate.value ||= monthStartDate(selectedMonth.value, selectedYear.value);
        selectedEndDate.value ||= monthEndDate(selectedMonth.value, selectedYear.value);
    }
}

function submit() {
    const params = {
        user_id: selectedUserId.value || undefined,
    };

    if (isRangeMode.value) {
        params.period_type = 'range';
        params.start_date = selectedStartDate.value || undefined;
        params.end_date = selectedEndDate.value || undefined;
    } else {
        params.bulan = selectedMonth.value;
        params.tahun = selectedYear.value;
    }

    router.get(
        route('admin.presensi.by-user'),
        params,
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
                <p class="mt-1 text-sm text-slate-600">Lihat status presensi seorang karyawan berdasarkan bulan atau custom range.</p>
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
                <div class="p-5 sm:p-6 space-y-5">
                    <div v-if="flashError" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        {{ flashError }}
                    </div>

                    <form class="space-y-4" @submit.prevent="submit">
                        <div class="grid gap-4 lg:grid-cols-[minmax(280px,1fr)_auto] lg:items-start">
                            <div class="w-full">
                                <label class="sr-only" for="karyawan">Karyawan</label>
                                <button
                                    id="karyawan"
                                    type="button"
                                    class="inline-flex h-11 w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/30"
                                    @click="openModal"
                                >
                                    <span class="truncate">
                                        <template v-if="displaySelectedUser">{{ displaySelectedUser.nama }}</template>
                                        <template v-else>Pilih karyawan…</template>
                                    </span>
                                    <span class="shrink-0 text-slate-400">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </span>
                                </button>
                                <div v-if="displaySelectedUser?.posisi || displaySelectedUser?.role" class="mt-1 text-xs text-slate-600">
                                    {{ displaySelectedUser.posisi || displaySelectedUser.role }}
                                </div>
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

                        <div
                            v-if="!isRangeMode"
                            class="grid gap-3 sm:grid-cols-2 md:grid-cols-[160px_120px_auto] md:items-end"
                        >
                            <div class="w-full">
                                <label class="mb-1 block text-xs font-semibold text-slate-700" for="bulan">Bulan</label>
                                <select
                                    id="bulan"
                                    v-model.number="selectedMonth"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-400/30"
                                >
                                    <option v-for="month in 12" :key="month" :value="month">{{ monthLabel(month) }}</option>
                                </select>
                            </div>

                            <div class="w-full">
                                <label class="mb-1 block text-xs font-semibold text-slate-700" for="tahun">Tahun</label>
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
                                class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-sky-500 px-4 text-sm font-semibold text-white shadow-sm hover:bg-sky-400 active:bg-sky-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40 sm:col-span-2 md:col-span-1 md:w-auto md:min-w-[150px]"
                            >
                                Tampilkan Rekap
                            </button>
                        </div>

                        <div
                            v-else
                            class="grid gap-3 sm:grid-cols-2 md:grid-cols-[180px_180px_auto] md:items-end"
                        >
                            <div class="w-full">
                                <DatePickerField
                                    id="start_date"
                                    v-model="selectedStartDate"
                                    label="Tanggal Mulai"
                                    placeholder="Pilih tanggal mulai"
                                    :required="true"
                                />
                            </div>

                            <div class="w-full">
                                <DatePickerField
                                    id="end_date"
                                    v-model="selectedEndDate"
                                    label="Tanggal Selesai"
                                    placeholder="Pilih tanggal selesai"
                                    :required="true"
                                />
                            </div>

                            <button
                                type="submit"
                                class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-sky-500 px-4 text-sm font-semibold text-white shadow-sm hover:bg-sky-400 active:bg-sky-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40 sm:col-span-2 md:col-span-1 md:w-auto md:min-w-[150px]"
                            >
                                Tampilkan Rekap
                            </button>
                        </div>

                        <div
                            class="inline-flex max-w-full items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200"
                        >
                            <span class="inline-flex h-2 w-2 shrink-0 rounded-full bg-sky-500/70" />
                            <span class="truncate">{{ activePeriodText }}</span>
                            <span class="text-slate-400">·</span>
                            <span class="shrink-0">{{ jumlahHari }} hari</span>
                        </div>
                    </form>

                    <div v-if="isEmployeeSelected" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Hadir lengkap</div>
                            <div class="mt-1 text-xl font-semibold text-slate-900">{{ summary.hadirLengkap }}</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Belum checkout</div>
                            <div class="mt-1 text-xl font-semibold text-slate-900">{{ summary.belumCheckout }}</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Izin/Cuti</div>
                            <div class="mt-1 text-xl font-semibold text-slate-900">{{ summary.izinCuti }}</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Lembur</div>
                            <div class="mt-1 text-xl font-semibold text-slate-900">{{ summary.lembur }}</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Tidak hadir</div>
                            <div class="mt-1 text-xl font-semibold text-slate-900">{{ summary.tidakPresensi }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 sm:px-8 sm:py-5 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-base font-semibold text-slate-900">{{ tableTitle }}</div>
                        <div class="mt-1 text-sm text-slate-600">
                            <template v-if="isEmployeeSelected">Data kehadiran berdasarkan periode yang dipilih.</template>
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
                        <table class="min-w-[1280px] w-full">
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
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-left">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('status_hari_ini')">
                                            Status Hari Ini
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('status_hari_ini') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('status_hari_ini') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
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
                                            Total Kerja
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('total_minutes') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('total_minutes') === 'desc'">↓</template>
                                            </span>
                                        </button>
                                    </th>
                                    <th class="sticky top-0 z-10 bg-slate-50 px-4 py-3 text-left">
                                        <button type="button" class="group inline-flex items-center gap-2 hover:text-slate-900" @click="toggleSort('lembur')">
                                            Lembur
                                            <span class="text-slate-400 group-hover:text-slate-600">
                                                <template v-if="sortIndicator('lembur') === 'asc'">↑</template>
                                                <template v-else-if="sortIndicator('lembur') === 'desc'">↓</template>
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
                                            <StatusBadge :label="row.status_hari_ini?.label ?? '-'" :tone="row.status_hari_ini?.tone ?? 'slate'" />
                                            <StatusBadge v-if="row.has_lembur" label="Lembur" tone="info" />
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm text-slate-800">{{ dash(row.jam_masuk) }}</td>
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
                                    <td class="px-4 py-4 text-center text-sm text-slate-800">{{ dash(row.jam_keluar) }}</td>
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
                                    <td class="px-4 py-4 text-center text-sm font-semibold text-slate-900">{{ dash(row.total_jam_text) }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-700">
                                        <div v-if="row.lembur" class="space-y-1">
                                            <div class="font-semibold text-slate-900">{{ overtimeTimeText(row.lembur) }}</div>
                                            <div v-if="row.lembur.durasi_text" class="text-xs font-medium text-slate-600">{{ row.lembur.durasi_text }}</div>
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
                            </tbody>
                        </table>
                    </div>

                    <div v-if="isEmployeeSelected" class="border-t border-slate-200 bg-white px-5 py-6 sm:px-6">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:p-5">
                            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">Ringkasan Periode</div>
                                    <div class="mt-1 text-xs text-slate-600">
                                        <span>{{ displaySelectedUser?.nama ?? 'Karyawan' }}</span>
                                        <span class="mx-1 text-slate-400">·</span>
                                        <span>{{ activePeriodShortText }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-3 lg:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                                    <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Jam Kerja</div>
                                    <div class="mt-1 text-2xl font-semibold text-slate-900">{{ periodTotals.totalJamKerja }}</div>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                                    <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Jam Lembur</div>
                                    <div class="mt-1 text-2xl font-semibold text-slate-900">{{ periodTotals.totalJamLembur }}</div>
                                </div>
                            </div>

                            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Hari Hadir</div>
                                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ periodTotals.totalHariHadir }}</div>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Hari Lembur</div>
                                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ periodTotals.totalHariLembur }}</div>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Izin/Cuti</div>
                                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ periodTotals.totalIzinCuti }}</div>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Tidak Hadir</div>
                                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ periodTotals.totalTidakHadir }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
