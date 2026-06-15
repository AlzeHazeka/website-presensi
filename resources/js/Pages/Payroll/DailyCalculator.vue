<script setup>
import { computed, ref, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import DatePickerField from '../../Components/Forms/DatePickerField.vue';
import AlertMessage from '../../Components/UI/AlertMessage.vue';
import EmptyState from '../../Components/UI/EmptyState.vue';
import StatusBadge from '../../Components/UI/StatusBadge.vue';
import { route } from '../../lib/route';

const page = usePage();

const props = defineProps({
    employees: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    selectedEmployee: {
        type: Object,
        default: null,
    },
    result: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    karyawan_id: props.filters?.karyawan_id ?? '',
    tanggal_mulai: props.filters?.tanggal_mulai ?? '',
    tanggal_selesai: props.filters?.tanggal_selesai ?? '',
    gaji_per_hari: props.filters?.gaji_per_hari ?? '',
    overtime_hourly_rate: props.filters?.overtime_hourly_rate ?? '',
    mode: props.filters?.mode ?? 'preview',
});

const selectedEmployee = computed(() => {
    if (props.selectedEmployee) return props.selectedEmployee;
    return props.employees.find((employee) => String(employee.user_id) === String(form.karyawan_id)) ?? null;
});

const attendanceRows = computed(() => props.result?.attendance ?? []);
const summary = computed(() => props.result?.summary ?? null);
const leaveSummary = computed(() => props.result?.leave_summary ?? { total_items: 0, total_days: 0, items: [] });
const overtimeSummary = computed(() => props.result?.overtime_summary ?? { total_items: 0, total_valid_items: 0, total_overtime_hours_label: '0 jam 0 menit', payable_overtime_hours: 0, items: [] });
const payroll = computed(() => props.result?.payroll ?? null);
const hasResult = computed(() => Boolean(props.result));
const dailyWageNumber = computed(() => Number.parseFloat(String(form.gaji_per_hari || '0')) || 0);
const hourlyWagePreview = computed(() => dailyWageNumber.value > 0 ? dailyWageNumber.value / 8 : null);
const overtimeRateNumber = computed(() => Number.parseFloat(String(form.overtime_hourly_rate || '0')) || 0);
const overtimeRatePreview = computed(() => overtimeRateNumber.value > 0 ? overtimeRateNumber.value : hourlyWagePreview.value);
const canShowSlip = computed(() => Boolean(hasResult.value && payroll.value?.is_calculated));
const printForm = ref(null);
const csrfToken = computed(() => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '');
const localAlert = ref(null);
const isPrinting = ref(false);
const validationMessages = computed(() => {
    const errors = form.errors ?? {};

    return [...new Set(Object.values(errors).flatMap((message) => Array.isArray(message) ? message : [message]).filter(Boolean))];
});
const flashError = computed(() => page.props.flash?.error ?? '');
const flashSuccess = computed(() => page.props.flash?.success ?? '');
const pageAlert = computed(() => {
    if (localAlert.value) return localAlert.value;
    if (validationMessages.value.length > 0) {
        return {
            type: 'error',
            title: 'Terdapat kesalahan:',
            messages: validationMessages.value,
        };
    }
    if (flashError.value) return { type: 'error', message: flashError.value };
    if (flashSuccess.value) return { type: 'success', message: flashSuccess.value };

    return null;
});
const dailyWageInputWarning = computed(() => {
    if (form.gaji_per_hari === '' || form.gaji_per_hari === null || form.gaji_per_hari === undefined) return '';
    const value = Number(form.gaji_per_hari);
    if (!Number.isFinite(value)) return 'Nominal gaji per hari harus berupa angka.';
    if (value < 0) return 'Gaji per hari tidak boleh negatif.';
    if (value === 0) return 'Gaji per hari harus lebih dari 0.';

    return '';
});
const overtimeInputWarning = computed(() => {
    if (form.overtime_hourly_rate === '' || form.overtime_hourly_rate === null || form.overtime_hourly_rate === undefined) return '';
    const value = Number(form.overtime_hourly_rate);
    if (!Number.isFinite(value)) return 'Tarif lembur per jam harus berupa angka.';
    if (value < 0) return 'Tarif lembur per jam tidak boleh negatif.';

    return '';
});
const attendanceCalculationWarning = computed(() => {
    if (!hasResult.value || attendanceRows.value.length === 0) return '';
    if ((summary.value?.total_valid_attendance_days ?? 0) > 0) return '';

    return 'Tidak ada presensi lengkap dan valid yang dapat dihitung pada periode ini.';
});

function submit(mode) {
    if (!validateForm(mode)) return;

    form.mode = mode;
    localAlert.value = null;
    form.post(route('payroll.daily.calculate'), {
        preserveScroll: true,
        onSuccess: () => {
            localAlert.value = null;
        },
    });
}

function resetPage() {
    localAlert.value = null;
    router.get(route('payroll.daily.index'));
}

function printSlip() {
    if (!canShowSlip.value) {
        setLocalWarning('Hitung gaji terlebih dahulu sebelum mencetak slip.');
        return;
    }
    if (!validateForm('calculate')) return;

    localAlert.value = null;
    isPrinting.value = true;
    printForm.value?.submit();
    window.setTimeout(() => {
        isPrinting.value = false;
    }, 1500);
}

function setLocalWarning(message) {
    localAlert.value = { type: 'warning', message };
}

function validateForm(mode) {
    if (!form.karyawan_id) {
        setLocalWarning('Silakan pilih karyawan terlebih dahulu.');
        return false;
    }
    if (!form.tanggal_mulai) {
        setLocalWarning('Silakan pilih tanggal mulai.');
        return false;
    }
    if (!form.tanggal_selesai) {
        setLocalWarning('Silakan pilih tanggal selesai.');
        return false;
    }
    if (new Date(`${form.tanggal_selesai}T00:00:00`) < new Date(`${form.tanggal_mulai}T00:00:00`)) {
        setLocalWarning('Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
        return false;
    }

    if (mode === 'calculate') {
        if (form.gaji_per_hari === '' || form.gaji_per_hari === null || form.gaji_per_hari === undefined) {
            setLocalWarning('Silakan isi nominal gaji per hari terlebih dahulu.');
            return false;
        }

        const dailyWage = Number(form.gaji_per_hari);
        if (!Number.isFinite(dailyWage)) {
            setLocalWarning('Nominal gaji per hari harus berupa angka.');
            return false;
        }
        if (dailyWage <= 0) {
            setLocalWarning('Gaji per hari harus lebih dari 0.');
            return false;
        }
    }

    if (form.overtime_hourly_rate !== '' && form.overtime_hourly_rate !== null && form.overtime_hourly_rate !== undefined) {
        const overtimeRate = Number(form.overtime_hourly_rate);
        if (!Number.isFinite(overtimeRate)) {
            setLocalWarning('Tarif lembur per jam harus berupa angka.');
            return false;
        }
        if (overtimeRate < 0) {
            setLocalWarning('Tarif lembur per jam tidak boleh negatif.');
            return false;
        }
    }

    return true;
}

function formatCurrency(value) {
    const number = Number(value ?? 0);
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(number);
}

function formatDate(value) {
    if (!value) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(new Date(`${value}T00:00:00`));
}

function employeeLabel(employee) {
    const nik = employee?.nik ? `${employee.nik} - ` : '';
    return `${nik}${employee?.nama ?? '-'}`;
}

watch(
    () => [form.karyawan_id, form.tanggal_mulai, form.tanggal_selesai, form.gaji_per_hari, form.overtime_hourly_rate],
    () => {
        localAlert.value = null;
        form.clearErrors();
    },
);
</script>

<template>
    <AppLayout>
        <template #header>
            <div class="print:hidden">
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Hitung Gaji Karyawan Harian</h2>
                <p class="mt-1 text-sm text-slate-600">Kalkulasi dinamis berdasarkan presensi lengkap pada rentang tanggal.</p>
            </div>
        </template>

	        <div class="mx-auto max-w-7xl space-y-6 print:max-w-none print:space-y-4">
	            <AlertMessage
	                v-if="pageAlert"
	                :type="pageAlert.type"
	                :title="pageAlert.title"
	                :message="pageAlert.message"
	                :messages="pageAlert.messages ?? []"
	                class="print:hidden"
	            />

	            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm print:hidden">
	                <form class="p-6 sm:p-8" @submit.prevent="submit('preview')">
                    <div class="grid gap-4 lg:grid-cols-[minmax(240px,1fr)_190px_190px_auto] lg:items-end">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-900" for="karyawan_id">Karyawan</label>
                            <select
                                id="karyawan_id"
                                v-model="form.karyawan_id"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                            >
                                <option value="">Pilih karyawan harian</option>
                                <option v-for="employee in employees" :key="employee.user_id" :value="employee.user_id">
                                    {{ employeeLabel(employee) }}
                                </option>
                            </select>
                            <div v-if="form.errors.karyawan_id" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.karyawan_id }}</div>
                        </div>

                        <DatePickerField id="tanggal_mulai" v-model="form.tanggal_mulai" label="Tanggal Mulai" :required="true" />
                        <DatePickerField id="tanggal_selesai" v-model="form.tanggal_selesai" label="Tanggal Selesai" :required="true" />

                        <button
                            type="submit"
                            class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-sky-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-500 disabled:cursor-not-allowed disabled:opacity-60 lg:w-auto"
                            :disabled="form.processing"
	                        >
	                            {{ form.processing ? 'Memproses...' : 'Tampilkan Presensi' }}
	                        </button>
	                    </div>

                    <div v-if="form.errors.tanggal_mulai || form.errors.tanggal_selesai" class="mt-3 grid gap-1 text-xs font-medium text-rose-700 sm:grid-cols-2">
                        <div v-if="form.errors.tanggal_mulai">{{ form.errors.tanggal_mulai }}</div>
                        <div v-if="form.errors.tanggal_selesai">{{ form.errors.tanggal_selesai }}</div>
                    </div>
                </form>
            </section>

            <section v-if="selectedEmployee && hasResult" class="grid gap-6 lg:grid-cols-12">
                <div class="space-y-6 lg:col-span-7">
                    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="p-6 sm:p-8">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Informasi Karyawan</div>
                                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ selectedEmployee.nama }}</div>
                                    <div class="mt-1 text-sm text-slate-600">{{ selectedEmployee.posisi ?? '-' }}</div>
                                </div>
                                <StatusBadge label="Harian" tone="info" />
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Periode</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-900">
                                        {{ formatDate(form.tanggal_mulai) }} s/d {{ formatDate(form.tanggal_selesai) }}
                                    </div>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Hari Hadir</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ summary?.total_hari_hadir ?? 0 }} hari</div>
	                                </div>
	                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 sm:col-span-2">
	                                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Jam Aktual</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-900">
                                        {{ summary?.total_work_hours_label ?? '0 jam 0 menit' }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-600">
                                        Jam dibayar: {{ summary?.rounded_payable_hours ?? 0 }} jam
	                                    </div>
	                                </div>
	                            </div>
	                            <AlertMessage
	                                v-if="attendanceCalculationWarning"
	                                type="warning"
	                                :message="attendanceCalculationWarning"
	                                class="mt-5"
	                            />
	                        </div>
	                    </section>

	                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
	                        <div class="border-b border-slate-200 px-6 py-4 sm:px-8">
	                            <div class="text-base font-semibold text-slate-900">Tabel Presensi</div>
	                            <div class="mt-1 text-sm text-slate-600">Hanya presensi lengkap dan valid yang masuk total jam kerja.</div>
                        </div>

                        <div v-if="attendanceRows.length === 0" class="p-6 sm:p-8">
                            <EmptyState title="Tidak ada data presensi pada periode ini." />
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-[780px] w-full">
                                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3 text-left">No</th>
                                        <th class="px-4 py-3 text-left">Tanggal</th>
                                        <th class="px-4 py-3 text-left">Jam Masuk</th>
                                        <th class="px-4 py-3 text-left">Jam Keluar</th>
                                        <th class="px-4 py-3 text-left">Durasi Kerja</th>
                                        <th class="px-4 py-3 text-left">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="(row, index) in attendanceRows" :key="row.id_presensi" class="text-sm">
                                        <td class="px-4 py-3 text-slate-600">{{ index + 1 }}</td>
                                        <td class="px-4 py-3 font-medium text-slate-900">{{ row.tanggal_human }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ row.jam_masuk ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ row.jam_keluar ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ row.duration_text }}</td>
                                        <td class="px-4 py-3">
                                            <StatusBadge :label="row.status" :tone="row.status_tone" />
                                        </td>
                                    </tr>
                                </tbody>
	                            </table>
	                        </div>
	                    </section>

	                    <section v-if="hasResult" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
	                        <div class="border-b border-slate-200 px-6 py-4 sm:px-8">
	                            <div class="text-base font-semibold text-slate-900">Izin / Cuti Periode Ini</div>
	                            <div class="mt-1 text-sm text-slate-600">Izin/cuti disetujui hanya ditampilkan sebagai informasi.</div>
	                        </div>

	                        <div class="grid gap-3 p-6 sm:grid-cols-2 sm:p-8">
	                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
	                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Pengajuan Disetujui</div>
	                                <div class="mt-1 text-sm font-semibold text-slate-900">{{ leaveSummary.total_items }} pengajuan</div>
	                            </div>
	                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
	                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Hari Izin/Cuti</div>
	                                <div class="mt-1 text-sm font-semibold text-slate-900">{{ leaveSummary.total_days }} hari</div>
	                            </div>
	                        </div>

	                        <div v-if="(leaveSummary.items?.length ?? 0) === 0" class="px-6 pb-6 sm:px-8 sm:pb-8">
	                            <EmptyState title="Tidak ada izin/cuti disetujui pada periode ini." />
	                        </div>
	                        <div v-else class="overflow-x-auto px-6 pb-6 sm:px-8 sm:pb-8">
	                            <table class="min-w-[620px] w-full rounded-xl border border-slate-200 text-sm">
	                                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
	                                    <tr>
	                                        <th class="px-3 py-2 text-left">No</th>
	                                        <th class="px-3 py-2 text-left">Tanggal</th>
	                                        <th class="px-3 py-2 text-left">Jenis</th>
	                                        <th class="px-3 py-2 text-left">Alasan</th>
	                                        <th class="px-3 py-2 text-left">Status</th>
	                                    </tr>
	                                </thead>
	                                <tbody class="divide-y divide-slate-100">
	                                    <tr v-for="(item, index) in leaveSummary.items" :key="item.id_izin">
	                                        <td class="px-3 py-2">{{ index + 1 }}</td>
	                                        <td class="px-3 py-2 font-medium text-slate-900">{{ item.date_label }}</td>
	                                        <td class="px-3 py-2">{{ item.type }}</td>
	                                        <td class="px-3 py-2">{{ item.reason }}</td>
	                                        <td class="px-3 py-2"><StatusBadge :label="item.status" tone="success" /></td>
	                                    </tr>
	                                </tbody>
	                            </table>
	                        </div>
	                    </section>

	                    <section v-if="hasResult" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
	                        <div class="border-b border-slate-200 px-6 py-4 sm:px-8">
	                            <div class="text-base font-semibold text-slate-900">Lembur Periode Ini</div>
	                            <div class="mt-1 text-sm text-slate-600">Lembur lengkap dan valid masuk sebagai tambahan pendapatan.</div>
	                        </div>

	                        <div class="grid gap-3 p-6 sm:grid-cols-3 sm:p-8">
	                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
	                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Lembur Disetujui</div>
	                                <div class="mt-1 text-sm font-semibold text-slate-900">{{ overtimeSummary.total_items }} data</div>
	                            </div>
	                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
	                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Jam Aktual</div>
	                                <div class="mt-1 text-sm font-semibold text-slate-900">{{ overtimeSummary.total_overtime_hours_label }}</div>
	                            </div>
	                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
	                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Jam Dibayar</div>
	                                <div class="mt-1 text-sm font-semibold text-slate-900">{{ overtimeSummary.payable_overtime_hours }} jam</div>
	                            </div>
	                        </div>

	                        <div v-if="(overtimeSummary.items?.length ?? 0) === 0" class="px-6 pb-6 sm:px-8 sm:pb-8">
	                            <EmptyState title="Tidak ada lembur disetujui pada periode ini." />
	                        </div>
	                        <div v-else class="overflow-x-auto px-6 pb-6 sm:px-8 sm:pb-8">
	                            <table class="min-w-[760px] w-full rounded-xl border border-slate-200 text-sm">
	                                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
	                                    <tr>
	                                        <th class="px-3 py-2 text-left">No</th>
	                                        <th class="px-3 py-2 text-left">Tanggal</th>
	                                        <th class="px-3 py-2 text-left">Jam Mulai</th>
	                                        <th class="px-3 py-2 text-left">Jam Selesai</th>
	                                        <th class="px-3 py-2 text-left">Durasi Lembur</th>
	                                        <th class="px-3 py-2 text-left">Status</th>
	                                    </tr>
	                                </thead>
	                                <tbody class="divide-y divide-slate-100">
	                                    <tr v-for="(item, index) in overtimeSummary.items" :key="item.id_lembur">
	                                        <td class="px-3 py-2">{{ index + 1 }}</td>
	                                        <td class="px-3 py-2 font-medium text-slate-900">{{ item.tanggal_human }}</td>
	                                        <td class="px-3 py-2">{{ item.jam_mulai ?? '-' }}</td>
	                                        <td class="px-3 py-2">{{ item.jam_selesai ?? '-' }}</td>
	                                        <td class="px-3 py-2">{{ item.duration_label }}</td>
	                                        <td class="px-3 py-2"><StatusBadge :label="item.status" :tone="item.status_tone" /></td>
	                                    </tr>
	                                </tbody>
	                            </table>
	                        </div>
	                    </section>
	                </div>

	                <div class="space-y-6 lg:col-span-5">
                    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm print:hidden">
                        <div class="p-6 sm:p-8">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Input Gaji</div>
                            <div class="mt-1 text-lg font-semibold text-slate-900">Gaji Harian</div>

                            <div class="mt-5 space-y-4">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-900" for="gaji_per_hari">Gaji per Hari</label>
                                    <input
                                        id="gaji_per_hari"
                                        v-model="form.gaji_per_hari"
                                        type="number"
                                        min="0"
                                        step="1"
                                        placeholder="Contoh: 120000"
                                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
	                                    />
	                                    <div class="mt-1 text-xs text-slate-500">
	                                        Nominal ini hanya digunakan untuk simulasi slip dan tidak disimpan ke data karyawan.
	                                    </div>
	                                    <div v-if="form.errors.gaji_per_hari" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.gaji_per_hari }}</div>
	                                    <div v-else-if="dailyWageInputWarning" class="mt-1 text-xs font-medium text-amber-700">{{ dailyWageInputWarning }}</div>
	                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Standar Jam Kerja</div>
                                        <div class="mt-1 text-sm font-semibold text-slate-900">8 jam/hari</div>
                                    </div>
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Gaji per Jam</div>
                                        <div class="mt-1 text-sm font-semibold text-slate-900">
                                            {{ hourlyWagePreview ? formatCurrency(hourlyWagePreview) : '-' }}
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-900" for="overtime_hourly_rate">Tarif Lembur per Jam</label>
                                    <input
                                        id="overtime_hourly_rate"
                                        v-model="form.overtime_hourly_rate"
                                        type="number"
                                        min="0"
                                        step="1"
                                        placeholder="Kosongkan untuk ikut gaji per jam"
                                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
	                                    />
	                                    <div class="mt-1 text-xs text-slate-500">
	                                        Jika kosong, tarif lembur memakai gaji per jam.
	                                        Tarif aktif: {{ overtimeRatePreview ? formatCurrency(overtimeRatePreview) : '-' }}
	                                    </div>
	                                    <div v-if="form.errors.overtime_hourly_rate" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.overtime_hourly_rate }}</div>
	                                    <div v-else-if="overtimeInputWarning" class="mt-1 text-xs font-medium text-amber-700">{{ overtimeInputWarning }}</div>
	                                </div>

                                <div v-if="hasResult" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                    <div class="flex items-start gap-2 font-semibold">
                                        <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs ring-1 ring-amber-300">i</span>
                                        <span>Pembulatan jam dibayar</span>
                                    </div>
                                    <div class="mt-1 text-xs leading-relaxed">
                                        Pembulatan jam dibayar: sisa 0-30 menit dibulatkan ke bawah, 31-59 menit dibulatkan ke atas. Berlaku untuk jam kerja dan jam lembur.
                                    </div>
                                    <div class="mt-1 text-xs leading-relaxed">
                                        {{ summary?.total_work_hours_label ?? '0 jam 0 menit' }} aktual menjadi
                                        {{ summary?.rounded_payable_hours ?? 0 }} jam dibayar.
                                    </div>
                                    <div class="mt-1 text-xs leading-relaxed">
                                        {{ overtimeSummary.total_overtime_hours_label }} lembur aktual menjadi
                                        {{ overtimeSummary.payable_overtime_hours }} jam lembur dibayar.
                                    </div>
                                </div>

	                                <div v-if="hasResult" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs leading-relaxed text-slate-600">
	                                    Presensi tanpa jam masuk dan jam keluar lengkap tidak dihitung dalam penggajian. Lembur hanya dihitung jika status disetujui dan waktu lembur lengkap.
	                                </div>

                                <button
                                    type="button"
                                    class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-slate-900 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                                    :disabled="form.processing"
                                    @click="submit('calculate')"
	                                >
	                                    {{ form.processing ? 'Memproses...' : 'Hitung Gaji' }}
	                                </button>
                            </div>
                        </div>
                    </section>

	                    <section v-if="canShowSlip" class="rounded-2xl border border-slate-300 bg-white shadow-sm print:rounded-none print:border-slate-400 print:shadow-none">
                        <div class="border-b border-slate-200 p-6 text-center sm:p-8">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Slip Gaji Karyawan Harian</div>
                            <div class="mt-2 text-2xl font-bold text-slate-950">{{ formatCurrency(payroll.gross_total) }}</div>
                        </div>

                        <div class="space-y-5 p-6 sm:p-8">
                            <div class="grid gap-2 text-sm">
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500">Nama Karyawan</span>
                                    <span class="text-right font-semibold text-slate-900">{{ selectedEmployee.nama }}</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500">NIK</span>
                                    <span class="text-right font-semibold text-slate-900">{{ selectedEmployee.nik ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500">Posisi</span>
                                    <span class="text-right font-semibold text-slate-900">{{ selectedEmployee.posisi ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500">Tipe Gaji</span>
                                    <span class="text-right font-semibold text-slate-900">Harian</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500">Periode</span>
                                    <span class="text-right font-semibold text-slate-900">{{ formatDate(form.tanggal_mulai) }} s/d {{ formatDate(form.tanggal_selesai) }}</span>
                                </div>
                            </div>

                            <div class="border-t border-slate-200 pt-5">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rincian Presensi</div>
                                <div class="mt-3 grid gap-2 text-sm">
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Total Hari Hadir</span>
                                        <span class="font-semibold text-slate-900">{{ summary.total_hari_hadir }} hari</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Total Jam Aktual</span>
                                        <span class="font-semibold text-slate-900">{{ summary.total_work_hours_label }}</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Total Jam Dibayar</span>
                                        <span class="font-semibold text-slate-900">{{ summary.rounded_payable_hours }} jam</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Standar Jam/Hari</span>
                                        <span class="font-semibold text-slate-900">{{ payroll.standard_hours_per_day }} jam</span>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-slate-200 pt-5">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rincian Izin / Cuti</div>
                                <div class="mt-3 grid gap-2 text-sm">
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Total Izin/Cuti</span>
                                        <span class="font-semibold text-slate-900">{{ leaveSummary.total_items }} pengajuan</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Total Hari Izin/Cuti</span>
                                        <span class="font-semibold text-slate-900">{{ leaveSummary.total_days }} hari</span>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-slate-200 pt-5">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rincian Lembur</div>
                                <div class="mt-3 grid gap-2 text-sm">
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Total Lembur Disetujui</span>
                                        <span class="font-semibold text-slate-900">{{ overtimeSummary.total_items }} data</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Total Jam Lembur Aktual</span>
                                        <span class="font-semibold text-slate-900">{{ overtimeSummary.total_overtime_hours_label }}</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Total Jam Lembur Bayar</span>
                                        <span class="font-semibold text-slate-900">{{ overtimeSummary.payable_overtime_hours }} jam</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Tarif Lembur per Jam</span>
                                        <span class="font-semibold text-slate-900">{{ formatCurrency(payroll.overtime_hourly_rate) }}</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Pendapatan Lembur</span>
                                        <span class="font-semibold text-slate-900">{{ formatCurrency(payroll.overtime_total) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-slate-200 pt-5">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rincian Gaji</div>
                                <div class="mt-3 grid gap-2 text-sm">
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Gaji Per Hari</span>
                                        <span class="font-semibold text-slate-900">{{ formatCurrency(payroll.daily_wage) }}</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Gaji Per Jam</span>
                                        <span class="font-semibold text-slate-900">{{ formatCurrency(payroll.hourly_wage) }}</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Jam Kerja Dibayar</span>
                                        <span class="font-semibold text-slate-900">{{ payroll.payable_hours }} jam</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Gaji Utama</span>
                                        <span class="font-semibold text-slate-900">{{ formatCurrency(payroll.attendance_gross_total) }}</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Pendapatan Lembur</span>
                                        <span class="font-semibold text-slate-900">{{ formatCurrency(payroll.overtime_total) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl bg-slate-950 px-5 py-4 text-white print:border print:border-slate-900 print:bg-white print:text-slate-950">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-300 print:text-slate-500">Total Gaji</div>
                                <div class="mt-1 text-3xl font-bold">{{ formatCurrency(payroll.gross_total) }}</div>
                            </div>

                            <p class="text-xs leading-relaxed text-slate-500">
                                Catatan: Perhitungan berdasarkan presensi dan lembur lengkap serta valid. Izin/cuti ditampilkan sebagai informasi.
                            </p>
                        </div>
                    </section>

                    <section v-if="hasResult" class="flex flex-col gap-2 sm:flex-row print:hidden">
                        <button
                            type="button"
                            class="inline-flex h-11 flex-1 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
	                            :disabled="!canShowSlip || isPrinting"
	                            :title="!canShowSlip ? 'Hitung gaji terlebih dahulu sebelum mencetak slip.' : ''"
	                            @click="printSlip"
	                        >
	                            {{ isPrinting ? 'Menyiapkan...' : 'Cetak / Print' }}
	                        </button>
                        <button
                            type="button"
                            class="inline-flex h-11 flex-1 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-5 text-sm font-semibold text-rose-700 shadow-sm transition hover:bg-rose-100"
                            @click="resetPage"
                        >
                            Reset
                        </button>
                    </section>
                </div>
            </section>

            <section v-else class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 print:hidden">
                <EmptyState title="Pilih karyawan dan periode untuk menampilkan presensi." description="Gunakan filter di atas untuk memulai perhitungan gaji harian." />
            </section>

            <form
                ref="printForm"
                class="hidden"
                method="POST"
                target="_blank"
                :action="route('payroll.daily.print')"
            >
                <input type="hidden" name="_token" :value="csrfToken" />
                <input type="hidden" name="mode" value="calculate" />
                <input type="hidden" name="karyawan_id" :value="form.karyawan_id" />
                <input type="hidden" name="tanggal_mulai" :value="form.tanggal_mulai" />
                <input type="hidden" name="tanggal_selesai" :value="form.tanggal_selesai" />
                <input type="hidden" name="gaji_per_hari" :value="form.gaji_per_hari" />
                <input type="hidden" name="overtime_hourly_rate" :value="form.overtime_hourly_rate" />
            </form>
        </div>
    </AppLayout>
</template>
