<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '../../../Layouts/AppLayout.vue';
import { route } from '../../../lib/route';
import DatePickerField from '../../../Components/Forms/DatePickerField.vue';
import TimePickerField from '../../../Components/Forms/TimePickerField.vue';
import StatusBadge from '../../../Components/UI/StatusBadge.vue';
import EmployeeSelectorModal from '../../../Components/Selector/EmployeeSelectorModal.vue';
import { MANUAL_LOCATION_PRESETS } from '../../../lib/locations/manualLocationPresets';

const props = defineProps({
    users: {
        type: Array,
        required: true,
    },
    officeLocation: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const flashError = computed(() => page.props.flash?.error ?? '');
const flashSuccess = computed(() => page.props.flash?.success ?? '');

const operationType = ref('presensi'); // presensi | izin | lembur
const isEmployeeModalOpen = ref(false);
const showAdvancedLocation = ref(false);

const form = useForm({
    operation_type: operationType.value,
    user_id: '',
    tanggal: '',

    // presensi
    jam_masuk: '',
    jam_keluar: '',
    lokasi_masuk: '',
    lokasi_keluar: '',
    latitude_masuk: null,
    longitude_masuk: null,
    accuracy_masuk: null,
    latitude_keluar: null,
    longitude_keluar: null,
    accuracy_keluar: null,

    // izin/cuti
    keterangan: '',

    // lembur
    jam_mulai: '',
    jam_selesai: '',
    lokasi_mulai: '',
    lokasi_selesai: '',
    latitude_mulai: null,
    longitude_mulai: null,
    accuracy_mulai: null,
    latitude_selesai: null,
    longitude_selesai: null,
    accuracy_selesai: null,
});

const selectedUser = computed(() => props.users.find((u) => String(u.user_id) === String(form.user_id)) ?? null);

function setOperationType(type) {
    operationType.value = type;
    form.operation_type = type;
}

function openEmployeeModal() {
    isEmployeeModalOpen.value = true;
}

function closeEmployeeModal() {
    isEmployeeModalOpen.value = false;
}

function handleSelectEmployee(user) {
    form.user_id = user?.user_id ?? '';
}

function applyLocation(target, location) {
    const lokasiString = location?.lokasi_string ?? '';
    const lat = location?.lat ?? null;
    const lng = location?.lng ?? null;
    const accuracy = location?.accuracy ?? null;

    if (target === 'masuk') {
        form.lokasi_masuk = lokasiString;
        form.latitude_masuk = lat;
        form.longitude_masuk = lng;
        form.accuracy_masuk = accuracy;
        return;
    }
    if (target === 'keluar') {
        form.lokasi_keluar = lokasiString;
        form.latitude_keluar = lat;
        form.longitude_keluar = lng;
        form.accuracy_keluar = accuracy;
        return;
    }
    if (target === 'mulai') {
        form.lokasi_mulai = lokasiString;
        form.latitude_mulai = lat;
        form.longitude_mulai = lng;
        form.accuracy_mulai = accuracy;
        return;
    }
    if (target === 'selesai') {
        form.lokasi_selesai = lokasiString;
        form.latitude_selesai = lat;
        form.longitude_selesai = lng;
        form.accuracy_selesai = accuracy;
        return;
    }
}

function applyOfficeLocation(target) {
    applyLocation(target, props.officeLocation ?? {});
}

const manualLocationPreset = MANUAL_LOCATION_PRESETS?.[0] ?? null;

function applyPresetLocation(target) {
    applyLocation(target, manualLocationPreset);
}

function applyPresetLocationBulk(targets) {
    if (!Array.isArray(targets)) return;
    targets.forEach((target) => applyPresetLocation(target));
}

const operationMeta = computed(() => {
    if (operationType.value === 'izin') {
        return {
            title: 'Input Manual — Izin/Cuti',
            description: 'Gunakan untuk kondisi khusus (kendala device/GPS). Izin/cuti bersifat eksklusif.',
            submitLabel: 'Simpan Izin/Cuti',
            submitConfirmTitle: 'Simpan izin/cuti manual?',
        };
    }
    if (operationType.value === 'lembur') {
        return {
            title: 'Input Manual — Lembur',
            description: 'Gunakan untuk kondisi khusus (kendala device/GPS). Pastikan jam lembur valid.',
            submitLabel: 'Simpan Lembur',
            submitConfirmTitle: 'Simpan lembur manual?',
        };
    }
    return {
        title: 'Input Manual — Presensi',
        description: 'Gunakan untuk kondisi khusus (kendala device/GPS). Presensi manual tetap mengikuti aturan izin/cuti.',
        submitLabel: 'Simpan Presensi',
        submitConfirmTitle: 'Simpan presensi manual?',
    };
});

const canUseOfficeLocation = computed(() => Boolean(props.officeLocation?.configured && props.officeLocation?.lokasi_string));
const canUsePresetLocation = computed(() => Boolean(manualLocationPreset?.lokasi_string));
const showOfficeLocationWarning = computed(() => !canUseOfficeLocation.value && !canUsePresetLocation.value);

const canSubmit = computed(() => Boolean(form.user_id) && Boolean(form.tanggal));

function toast(message, type = 'success') {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
}

async function confirmSubmit() {
    if (!canSubmit.value) {
        toast('Pilih karyawan dan tanggal terlebih dahulu.', 'warning');
        return;
    }

    const result = await Swal.fire({
        title: operationMeta.value.submitConfirmTitle,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0EA5E9',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Ya, simpan!',
        cancelButtonText: 'Batal',
    });

    if (!result.isConfirmed) return;

    form.post(route('admin.presensi.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            operationType.value = 'presensi';
            form.operation_type = 'presensi';
            showAdvancedLocation.value = false;
        },
    });
}

onMounted(() => {
    if (flashError.value) toast(flashError.value, 'error');
    if (flashSuccess.value) toast(flashSuccess.value, 'success');
});

watch(flashError, (message) => {
    if (message) toast(message, 'error');
});

watch(flashSuccess, (message) => {
    if (message) toast(message, 'success');
});
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Unified Manual Operational Input</h2>
                <p class="mt-1 text-sm text-slate-600">Tool admin untuk input manual Presensi / Izin/Cuti / Lembur.</p>
            </div>
        </template>

        <EmployeeSelectorModal
            :show="isEmployeeModalOpen"
            :users="users"
            :selected-user-id="form.user_id"
            title="Pilih Karyawan"
            @close="closeEmployeeModal"
            @select="handleSelectEmployee"
        />

        <div class="max-w-7xl mx-auto space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8 space-y-5">
                    <div v-if="flashSuccess" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                        {{ flashSuccess }}
                    </div>
                    <div v-if="flashError" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                        {{ flashError }}
                    </div>

                    <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                        ℹ️ Manual input digunakan untuk kondisi khusus seperti kendala device atau GPS. Semua input tetap mengikuti business rules.
                    </div>

                    <!-- Jenis Input -->
                    <div class="space-y-2">
                        <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Jenis Input</div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <button
                                type="button"
                                class="rounded-2xl border p-4 text-left shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40"
                                :class="operationType === 'presensi' ? 'border-sky-200 bg-sky-50' : 'border-slate-200 bg-white'"
                                @click="setOperationType('presensi')"
                            >
                                <div class="text-sm font-semibold text-slate-900">Presensi</div>
                                <div class="mt-1 text-xs text-slate-600">Masuk + keluar dengan lokasi.</div>
                            </button>
                            <button
                                type="button"
                                class="rounded-2xl border p-4 text-left shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40"
                                :class="operationType === 'izin' ? 'border-amber-200 bg-amber-50' : 'border-slate-200 bg-white'"
                                @click="setOperationType('izin')"
                            >
                                <div class="text-sm font-semibold text-slate-900">Izin/Cuti</div>
                                <div class="mt-1 text-xs text-slate-600">Tanpa GPS/jam.</div>
                            </button>
                            <button
                                type="button"
                                class="rounded-2xl border p-4 text-left shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40"
                                :class="operationType === 'lembur' ? 'border-violet-200 bg-violet-50' : 'border-slate-200 bg-white'"
                                @click="setOperationType('lembur')"
                            >
                                <div class="text-sm font-semibold text-slate-900">Lembur</div>
                                <div class="mt-1 text-xs text-slate-600">Mulai + selesai lembur.</div>
                            </button>
                        </div>
                    </div>

                    <div class="h-px bg-slate-100" />

                    <form class="space-y-6" @submit.prevent="confirmSubmit">
                        <input type="hidden" name="operation_type" :value="operationType" />

                        <!-- Pilih Karyawan -->
                        <section class="space-y-3">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Karyawan</div>
                                    <div class="mt-1 text-base font-semibold text-slate-900">Pilih karyawan</div>
                                </div>
                                <StatusBadge v-if="selectedUser?.status" :label="selectedUser.status" :tone="String(selectedUser.status).toLowerCase() === 'aktif' ? 'success' : 'slate'" />
                            </div>

                            <button
                                type="button"
                                class="inline-flex h-11 w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/30"
                                @click="openEmployeeModal"
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
                            <div v-if="selectedUser?.posisi || selectedUser?.role" class="text-xs text-slate-600">
                                {{ selectedUser.posisi || selectedUser.role }}
                            </div>
                        </section>

                        <!-- Tanggal -->
                        <section class="space-y-3">
                            <div>
                                <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Tanggal</div>
                                <div class="mt-1 text-base font-semibold text-slate-900">Pilih tanggal</div>
                            </div>
                            <DatePickerField id="tanggal" v-model="form.tanggal" label="Tanggal" :required="true" />
                        </section>

                        <!-- Detail Dinamis -->
                        <section class="space-y-4">
                            <div>
                                <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Detail</div>
                                <div class="mt-1 text-base font-semibold text-slate-900">{{ operationMeta.title }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ operationMeta.description }}</div>
                            </div>

                            <div v-if="operationType === 'izin'" class="grid gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700">Keterangan (opsional)</label>
                                    <textarea
                                        v-model="form.keterangan"
                                        rows="3"
                                        class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-400/30"
                                        placeholder="Contoh: HP rusak / GPS error / lupa presensi…"
                                    />
                                </div>
                            </div>

                            <div v-else-if="operationType === 'lembur'" class="grid gap-4 sm:grid-cols-2">
                                <TimePickerField id="jam_mulai" v-model="form.jam_mulai" label="Jam mulai lembur" :required="true" />
                                <TimePickerField id="jam_selesai" v-model="form.jam_selesai" label="Jam selesai lembur" :required="true" />

                                <div class="sm:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-slate-900">Lokasi lembur</div>
                                            <div class="mt-1 text-xs text-slate-600">Gunakan lokasi kantor untuk input cepat.</div>
                                        </div>
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            <button
                                                v-if="canUseOfficeLocation"
                                                type="button"
                                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                                                @click="applyOfficeLocation('mulai'); applyOfficeLocation('selesai')"
                                            >
                                                Gunakan Lokasi Kantor
                                            </button>
                                            <button
                                                v-if="canUsePresetLocation"
                                                type="button"
                                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                                                @click="applyPresetLocationBulk(['mulai', 'selesai'])"
                                            >
                                                Isi Lokasi Preset
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Lokasi mulai</label>
                                            <input
                                                v-model="form.lokasi_mulai"
                                                type="text"
                                                class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-400/30"
                                                placeholder="lat, lng atau catatan lokasi"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Lokasi selesai</label>
                                            <input
                                                v-model="form.lokasi_selesai"
                                                type="text"
                                                class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-400/30"
                                                placeholder="lat, lng atau catatan lokasi"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="grid gap-4 sm:grid-cols-2">
                                <TimePickerField id="jam_masuk" v-model="form.jam_masuk" label="Jam masuk" :required="true" />
                                <TimePickerField id="jam_keluar" v-model="form.jam_keluar" label="Jam keluar" :required="true" />

                                <div class="sm:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-slate-900">Lokasi presensi</div>
                                            <div class="mt-1 text-xs text-slate-600">Gunakan lokasi kantor untuk input cepat.</div>
                                        </div>
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            <button
                                                v-if="canUseOfficeLocation"
                                                type="button"
                                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                                                @click="applyOfficeLocation('masuk'); applyOfficeLocation('keluar')"
                                            >
                                                Gunakan Lokasi Kantor
                                            </button>
                                            <button
                                                v-if="canUsePresetLocation"
                                                type="button"
                                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                                                @click="applyPresetLocationBulk(['masuk', 'keluar'])"
                                            >
                                                Isi Lokasi Preset
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Lokasi masuk</label>
                                            <input
                                                v-model="form.lokasi_masuk"
                                                type="text"
                                                class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-400/30"
                                                placeholder="lat, lng atau catatan lokasi"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Lokasi keluar</label>
                                            <input
                                                v-model="form.lokasi_keluar"
                                                type="text"
                                                class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-400/30"
                                                placeholder="lat, lng atau catatan lokasi"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="text-xs text-slate-600">Metadata lokasi (lat/lng/accuracy) bersifat opsional.</div>
                                <button
                                    v-if="operationType !== 'izin'"
                                    type="button"
                                    class="text-xs font-semibold text-sky-700 hover:text-sky-800 underline underline-offset-4"
                                    @click="showAdvancedLocation = !showAdvancedLocation"
                                >
                                    {{ showAdvancedLocation ? 'Sembunyikan metadata' : 'Tampilkan metadata' }}
                                </button>
                            </div>

                            <div v-if="showAdvancedLocation && operationType !== 'izin'" class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-sm font-semibold text-slate-900">Metadata lokasi (opsional)</div>
                                <div class="mt-3 grid gap-4 sm:grid-cols-3">
                                    <template v-if="operationType === 'presensi'">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600">Lat masuk</label>
                                            <input v-model="form.latitude_masuk" type="number" step="0.0000001" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600">Lng masuk</label>
                                            <input v-model="form.longitude_masuk" type="number" step="0.0000001" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600">Accuracy masuk (m)</label>
                                            <input v-model="form.accuracy_masuk" type="number" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600">Lat keluar</label>
                                            <input v-model="form.latitude_keluar" type="number" step="0.0000001" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600">Lng keluar</label>
                                            <input v-model="form.longitude_keluar" type="number" step="0.0000001" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600">Accuracy keluar (m)</label>
                                            <input v-model="form.accuracy_keluar" type="number" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm" />
                                        </div>
                                    </template>

                                    <template v-else>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600">Lat mulai</label>
                                            <input v-model="form.latitude_mulai" type="number" step="0.0000001" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600">Lng mulai</label>
                                            <input v-model="form.longitude_mulai" type="number" step="0.0000001" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600">Accuracy mulai (m)</label>
                                            <input v-model="form.accuracy_mulai" type="number" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600">Lat selesai</label>
                                            <input v-model="form.latitude_selesai" type="number" step="0.0000001" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600">Lng selesai</label>
                                            <input v-model="form.longitude_selesai" type="number" step="0.0000001" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600">Accuracy selesai (m)</label>
                                            <input v-model="form.accuracy_selesai" type="number" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm" />
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </section>

                        <!-- Action -->
                        <section class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-400 active:bg-emerald-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400/40 disabled:opacity-60 sm:w-auto"
                                :disabled="form.processing"
                            >
                                {{ operationMeta.submitLabel }}
                            </button>
                            <Link
                                :href="route('admin.presensi.by-date')"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40 sm:w-auto"
                            >
                                Kembali
                            </Link>
                        </section>
                    </form>

                    <div v-if="showOfficeLocationWarning" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        ⚠️ Office location belum dikonfigurasi. Set env `OFFICE_LATITUDE` dan `OFFICE_LONGITUDE` agar tombol “Gunakan Lokasi Kantor” aktif.
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8">
                    <div class="text-sm font-semibold text-slate-900">Catatan</div>
                    <div class="mt-1 text-sm text-slate-600">
                        Manual input akan divalidasi oleh server. Jika data bertabrakan (mis. izin vs presensi/lembur), penyimpanan akan ditolak.
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
