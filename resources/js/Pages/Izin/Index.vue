<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '../../Layouts/AppLayout.vue';
import ActivityList from '../../Components/Activity/ActivityList.vue';
import DatePickerField from '../../Components/Forms/DatePickerField.vue';
import MonthNavigation from '../../Components/Navigation/MonthNavigation.vue';
import EmptyState from '../../Components/UI/EmptyState.vue';
import StatusBadge from '../../Components/UI/StatusBadge.vue';
import { route } from '../../lib/route';
import { addMonths, formatMonthLabel } from '../../lib/month';
import { useIzinEligibility } from '../../composables/useIzinEligibility';

const props = defineProps({
    ucapan: { type: String, required: true },
    tanggalHariIni: { type: String, required: true },
    tanggalHariIniHuman: { type: String, required: true },
    timezoneLabel: { type: String, default: 'Asia/Jakarta (WIB)' },
    eligibilityHariIni: { type: Object, required: true },
    activeMonth: { type: String, required: true }, // yyyy-MM
    riwayatIzin: { type: Array, default: () => [] },
});

const page = usePage();
const nama = computed(() => page.props.auth?.user?.nama ?? 'User');

const tanggalIzin = ref(props.tanggalHariIni);
const keterangan = ref('');
const maxKeterangan = 255;
const isSubmitting = ref(false);

const { isChecking, blockedByActivity, hasIzin, infoMessage } = useIzinEligibility(tanggalIzin, {
    initialEligibility: props.eligibilityHariIni,
});

function formatYmdToHuman(ymd, options) {
    if (!ymd) return '-';
    const parts = ymd.split('-').map((p) => Number.parseInt(p, 10));
    if (parts.length !== 3 || parts.some((p) => Number.isNaN(p))) return ymd;
    const [year, month, day] = parts;
    return new Intl.DateTimeFormat('id-ID', options).format(new Date(year, month - 1, day));
}

function formatIsoToHuman(iso, options) {
    if (!iso) return '-';
    const date = new Date(iso);
    if (Number.isNaN(date.getTime())) return '-';
    return new Intl.DateTimeFormat('id-ID', options).format(date);
}

const monthLabel = computed(() => formatMonthLabel(props.activeMonth, 'id-ID'));

function gotoMonth(delta) {
    const next = addMonths(props.activeMonth, delta);
    if (!next) return;
    router.get(
        route('izin.index'),
        { month: next },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
}

const historyItems = computed(() => {
    const today = props.tanggalHariIni;
    return (props.riwayatIzin ?? []).map((row) => {
        const tanggal = row?.tanggal_izin ?? '';
        const compare = today && tanggal ? tanggal.localeCompare(today) : 0;

        let badgeLabel = 'Terjadwal';
        let badgeTone = 'warning';
        if (compare < 0) {
            badgeLabel = 'Selesai';
            badgeTone = 'slate';
        } else if (compare === 0) {
            badgeLabel = 'Hari ini';
            badgeTone = 'warning';
        }

        return {
            id: row?.id_izin ?? `${tanggal}-${row?.tanggal_pengajuan ?? ''}`,
            title: formatYmdToHuman(tanggal, { day: '2-digit', month: 'long', year: 'numeric' }),
            subtitle: row?.keterangan ? String(row.keterangan) : `Tanggal: ${tanggal}`,
            badgeLabel,
            badgeTone,
            metaRight: `Diajukan ${formatIsoToHuman(row?.tanggal_pengajuan, { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}`,
        };
    });
});

const submitDisabled = computed(() => isSubmitting.value || isChecking.value || blockedByActivity.value || hasIzin.value);
const keteranganCount = computed(() => String(keterangan.value ?? '').length);
const keteranganRemaining = computed(() => Math.max(0, maxKeterangan - keteranganCount.value));

const infoTone = computed(() => {
    if (blockedByActivity.value) return 'warning';
    if (hasIzin.value) return 'info';
    return 'slate';
});

function showToast(message, type = 'success') {
    Swal.fire({
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 2500,
    });
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

async function ajukanIzin() {
    if (isSubmitting.value || submitDisabled.value) return;

    isSubmitting.value = true;
    try {
        const response = await fetch(route('izin.ajukan'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ tanggal_izin: tanggalIzin.value, keterangan: keterangan.value }),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const message =
                data?.message ||
                (data?.errors ? Object.values(data.errors).flat().join(' ') : null) ||
                'Gagal mengajukan izin.';
            showToast(message, 'error');
            return;
        }

        showToast(data?.message ?? 'Izin berhasil diajukan.', data?.status ?? 'success');
        if (data?.status === 'success') {
            keterangan.value = '';
            setTimeout(() => window.location.reload(), 800);
        }
    } catch (error) {
        console.error(error);
        showToast('Terjadi kesalahan saat mengajukan izin.', 'error');
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Izin</h2>
                <p class="mt-1 text-sm text-slate-600">Ajukan izin untuk hari ini atau tanggal mendatang.</p>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header / Greeting -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-slate-600">{{ props.ucapan }},</div>
                            <div class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">
                                {{ nama }}
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                                <div class="inline-flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200">
                                    <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="font-medium text-slate-800">{{ props.tanggalHariIniHuman }}</span>
                                </div>
                                <div class="inline-flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200">
                                    <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ props.timezoneLabel }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <StatusBadge v-if="props.eligibilityHariIni?.blocked_by_activity" label="Aktivitas tercatat" tone="warning" />
                            <StatusBadge v-else-if="props.eligibilityHariIni?.has_izin" label="Izin hari ini" tone="warning" />
                            <StatusBadge v-else label="Siap ajukan" tone="info" />
                        </div>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Form Pengajuan Izin -->
                <section class="lg:col-span-7 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-6 sm:p-8 space-y-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Form</div>
                                <div class="mt-1 text-lg font-semibold text-slate-900">Pengajuan Izin/Cuti</div>
                                <p class="mt-1 text-sm text-slate-600">
                                    Pilih tanggal. Pengajuan akan otomatis nonaktif jika sudah ada presensi/lembur pada tanggal tersebut.
                                </p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-500/10 ring-1 ring-sky-500/15 text-sky-700">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>

                        <div
                            v-if="infoMessage"
                            class="rounded-2xl border px-4 py-3 text-sm"
                            :class="
                                infoTone === 'warning'
                                    ? 'border-amber-200 bg-amber-50 text-amber-900'
                                    : 'border-sky-200 bg-sky-50 text-sky-900'
                            "
                        >
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5">
                                    <svg v-if="infoTone === 'warning'" class="h-5 w-5 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <svg v-else class="h-5 w-5 text-sky-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 18a9 9 0 110-18 9 9 0 010 18z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold">
                                        <template v-if="infoTone === 'warning'">Tidak tersedia</template>
                                        <template v-else>Info</template>
                                    </div>
                                    <div class="mt-0.5 text-sm">
                                        {{ infoMessage }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <DatePickerField
                                id="tanggal-izin"
                                v-model="tanggalIzin"
                                label="Tanggal izin/cuti"
                                :min="props.tanggalHariIni"
                                :disabled="isSubmitting"
                                helper="Pilih tanggal mulai hari ini. Sistem akan mengecek presensi/lembur secara otomatis."
                            />

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Status tanggal dipilih</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900">
                                    {{ formatYmdToHuman(tanggalIzin, { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' }) }}
                                </div>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <StatusBadge v-if="isChecking" label="Memeriksa..." tone="slate" />
                                    <StatusBadge v-else-if="blockedByActivity" label="Presensi/Lembur" tone="warning" />
                                    <StatusBadge v-else-if="hasIzin" label="Sudah izin" tone="info" />
                                    <StatusBadge v-else label="Tersedia" tone="success" />
                                </div>
                                <div class="mt-2 text-xs text-slate-600">
                                    Izin/cuti bersifat eksklusif: jika sudah ada presensi atau lembur pada tanggal ini, pengajuan dinonaktifkan.
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-end justify-between gap-3">
                                <label for="keterangan" class="text-sm font-semibold text-slate-900">Keterangan (opsional)</label>
                                <div class="text-xs text-slate-500">{{ keteranganCount }}/{{ maxKeterangan }}</div>
                            </div>
                            <textarea
                                id="keterangan"
                                v-model="keterangan"
                                :maxlength="maxKeterangan"
                                :disabled="submitDisabled"
                                rows="3"
                                class="mt-2 w-full resize-none rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20 disabled:cursor-not-allowed disabled:bg-slate-50"
                                placeholder="Contoh: keperluan keluarga, izin sakit, urusan penting, dll."
                            />
                            <div class="mt-1 text-xs text-slate-600">
                                Isi singkat untuk kebutuhan operasional. Sisa karakter: {{ keteranganRemaining }}.
                            </div>
                        </div>

                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-amber-400 active:bg-amber-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400/40 disabled:opacity-60"
                            :disabled="submitDisabled"
                            @click="ajukanIzin"
                        >
                            {{ isSubmitting ? 'Mengajukan...' : 'Ajukan Izin/Cuti' }}
                        </button>
                    </div>
                </section>

                <!-- Riwayat Izin Bulan Ini -->
                <section class="lg:col-span-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-6 sm:p-8 space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Riwayat</div>
                                <div class="mt-1 text-lg font-semibold text-slate-900">Riwayat Izin Bulan Ini</div>
                                <p class="mt-1 text-sm text-slate-600">Daftar izin berdasarkan bulan aktif.</p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 ring-1 ring-slate-200 text-slate-600">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v14l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z" />
                                </svg>
                            </div>
                        </div>

                        <MonthNavigation :label="monthLabel" @prev="gotoMonth(-1)" @next="gotoMonth(1)" />

                        <div v-if="historyItems.length" class="pt-1">
                            <ActivityList :items="historyItems" />
                        </div>
                        <EmptyState
                            v-else
                            title="Belum ada riwayat izin pada bulan ini."
                            description="Riwayat akan muncul setelah Anda mengajukan izin/cuti."
                        />
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
