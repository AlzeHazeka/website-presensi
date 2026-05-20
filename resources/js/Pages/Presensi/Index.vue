<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '../../Layouts/AppLayout.vue';
import { route } from '../../lib/route';

const props = defineProps({
    ucapan: {
        type: String,
        required: true,
    },
    tanggalHariIni: {
        type: String,
        required: true,
    },
    tanggalHariIniHuman: {
        type: String,
        required: true,
    },
    timezoneLabel: {
        type: String,
        default: 'Asia/Jakarta (WIB)',
    },
    presensi: {
        type: Object,
        default: null,
    },
    izinHariIni: {
        type: Object,
        default: null,
    },
    summary: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const nama = computed(() => page.props.auth?.user?.nama ?? 'User');

const isLocating = ref(false);
const isSubmitting = ref(false);
const locateStatus = ref('');
const locateWarning = ref('');

const clock = ref('');
let clockTimer = null;

function updateClock() {
    const now = new Date();
    clock.value = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    }).format(now);
}

function showToast(message, type = 'success') {
    Swal.fire({
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 3000,
    });
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function getHumanLocationError(error) {
    const code = error?.code;
    if (code === 1) return 'Izin lokasi ditolak. Silakan izinkan akses lokasi di browser.';
    if (code === 2) return 'Lokasi tidak tersedia. Pastikan GPS/perizinan lokasi aktif.';
    if (code === 3) return 'Timeout mengambil lokasi. Coba lagi saat sinyal lebih stabil.';
    return 'Gagal mendapatkan lokasi. Izinkan akses lokasi lalu coba lagi.';
}

function getBestPosition({
    maxWaitMs = 10_000,
    desiredAccuracyMeters = 60,
} = {}) {
    return new Promise((resolve, reject) => {
        let bestPosition = null;
        let watchId = null;
        let settled = false;

        const options = {
            enableHighAccuracy: true,
            timeout: 15_000,
            maximumAge: 0,
        };

        const stop = () => {
            if (watchId !== null && navigator.geolocation?.clearWatch) {
                navigator.geolocation.clearWatch(watchId);
            }
        };

        const finish = (position) => {
            if (settled) return;
            settled = true;
            stop();
            resolve(position);
        };

        const fail = (error) => {
            if (settled) return;
            settled = true;
            stop();
            reject(error);
        };

        const consider = (position) => {
            if (!position?.coords) return;
            if (!bestPosition || (position.coords.accuracy ?? Infinity) < (bestPosition.coords.accuracy ?? Infinity)) {
                bestPosition = position;

                const accuracy = Math.round(bestPosition.coords.accuracy ?? 0);
                locateStatus.value = accuracy
                    ? `Mengambil lokasi... (akurasi terbaik saat ini ~${accuracy}m)`
                    : 'Mengambil lokasi...';
            }

            const bestAccuracy = bestPosition?.coords?.accuracy;
            if (typeof bestAccuracy === 'number' && bestAccuracy <= desiredAccuracyMeters) {
                finish(bestPosition);
            }
        };

        const watchError = (error) => {
            // Jika sudah punya posisi terbaik, jangan gagal total—pakai yang ada.
            if (bestPosition) {
                finish(bestPosition);
                return;
            }
            fail(error);
        };

        // First try: getCurrentPosition (fast path)
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                consider(pos);
                // Jika belum cukup akurat, lanjutkan watchPosition beberapa detik
                watchId = navigator.geolocation.watchPosition(consider, watchError, options);
            },
            fail,
            options,
        );

        // Hard stop: pakai best position yang ada
        setTimeout(() => {
            if (settled) return;
            if (bestPosition) {
                finish(bestPosition);
                return;
            }
            fail({ code: 3 });
        }, maxWaitMs);
    });
}

async function ambilLokasi(jenis) {
    if (props.izinHariIni) {
        showToast('Anda sedang izin hari ini, tidak bisa melakukan presensi.', 'error');
        return;
    }

    if (!navigator.geolocation) {
        showToast('Browser tidak mendukung Geolocation!', 'error');
        return;
    }

    if (isLocating.value || isSubmitting.value) return;

    isLocating.value = true;
    locateWarning.value = '';
    locateStatus.value = 'Mengambil lokasi...';

    try {
        const position = await getBestPosition();
        const latitude = position.coords.latitude;
        const longitude = position.coords.longitude;
        const accuracy = typeof position.coords.accuracy === 'number' ? Math.round(position.coords.accuracy) : null;

        locateStatus.value = accuracy ? `Lokasi didapat (akurasi ~${accuracy}m)` : 'Lokasi didapat';

        // Warning ringan (tidak memblok), agar user bisa menunggu lokasi lebih stabil jika ingin
        if (typeof accuracy === 'number' && accuracy > 150) {
            locateWarning.value =
                'Akurasi lokasi terdeteksi rendah. Jika memungkinkan, tunggu sebentar dan coba lagi agar titik lebih presisi.';
        }

        await kirimPresensi(jenis, { latitude, longitude, accuracy });
    } catch (error) {
        showToast(getHumanLocationError(error), 'error');
    } finally {
        isLocating.value = false;
        locateStatus.value = '';
    }
}

async function kirimPresensi(jenis, position) {
    try {
        isSubmitting.value = true;

        const lokasi = `${position.latitude}, ${position.longitude}`;
        const response = await fetch(jenis === 'masuk' ? route('presensi.masuk') : route('presensi.keluar'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
            // SERVER TIME AUTHORITATIVE: backend memakai now() sebagai jam resmi presensi
            body: JSON.stringify({
                lokasi,
                latitude: position.latitude,
                longitude: position.longitude,
                accuracy: position.accuracy,
            }),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const message =
                data?.message ||
                (data?.errors ? Object.values(data.errors).flat().join(' ') : null) ||
                'Terjadi kesalahan!';
            showToast(message, 'error');
            return;
        }

        if (data.status === 'success') {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
            return;
        }

        showToast(data.message ?? 'Terjadi kesalahan!', 'error');
    } catch (error) {
        console.error(error);
        showToast('Terjadi kesalahan!', 'error');
    } finally {
        isSubmitting.value = false;
    }
}

const sudahMasuk = computed(() => Boolean(props.presensi?.jam_masuk));
const sudahKeluar = computed(() => Boolean(props.presensi?.jam_keluar));
const isIzinHariIni = computed(() => Boolean(props.izinHariIni));

const masukInfo = computed(() => ({
    jam: props.presensi?.jam_masuk ?? null,
    lokasi: props.presensi?.lokasi_masuk ?? null,
    lat: props.presensi?.lat_masuk ?? null,
    lng: props.presensi?.lng_masuk ?? null,
    accuracy: props.presensi?.accuracy_masuk ?? null,
}));

const keluarInfo = computed(() => ({
    jam: props.presensi?.jam_keluar ?? null,
    lokasi: props.presensi?.lokasi_keluar ?? null,
    lat: props.presensi?.lat_keluar ?? null,
    lng: props.presensi?.lng_keluar ?? null,
    accuracy: props.presensi?.accuracy_keluar ?? null,
}));

function formatAccuracy(meters) {
    if (meters === null || meters === undefined || Number.isNaN(Number(meters))) return '-';
    return `± ${Math.round(Number(meters))} meter`;
}

function formatLocation(lat, lng, fallback) {
    if (typeof lat === 'number' && typeof lng === 'number') {
        return `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
    }
    if (fallback) return String(fallback);
    return '-';
}

function formatAvgMinutes(minutes) {
    if (minutes === null || minutes === undefined) return '-';
    const total = Number(minutes);
    if (!Number.isFinite(total) || total <= 0) return '-';
    const h = Math.floor(total / 60);
    const m = total % 60;
    if (h <= 0) return `${m}m`;
    return `${h}j ${m}m`;
}

onMounted(() => {
    updateClock();
    clockTimer = setInterval(updateClock, 1000);
});

onUnmounted(() => {
    if (clockTimer) clearInterval(clockTimer);
});
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Presensi</h2>
                <p class="mt-1 text-sm text-slate-600">Kelola presensi masuk dan pulang Anda hari ini.</p>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- HERO -->
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="grid grid-cols-1 gap-4 p-5 sm:gap-6 sm:p-8 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                            <div>
                                <div class="text-sm font-medium text-slate-600">{{ ucapan }},</div>
                                <div class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                                    {{ nama }} <span class="align-middle text-slate-400">👋</span>
                                </div>
                                <p class="mt-2 max-w-xl text-sm leading-relaxed text-slate-600">
                                    Pastikan presensi Anda sesuai waktu kerja yang berlaku dan lokasi berhasil diambil dengan akurat.
                                </p>
                            </div>
                            <Link
                                :href="route('presensi.riwayat')"
                                class="inline-flex w-full shrink-0 items-center justify-center rounded-xl bg-sky-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-sky-400 active:bg-sky-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-sky-500 sm:w-auto"
                            >
                                Riwayat
                            </Link>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                            <div class="inline-flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200">
                                <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="font-medium text-slate-800">{{ tanggalHariIniHuman }}</span>
                            </div>
                            <div class="inline-flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200">
                                <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ timezoneLabel }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- CLOCK CARD -->
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 sm:p-6">
                        <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Waktu Sekarang</div>
                        <div class="mt-3 font-mono text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">
                            {{ clock }}
                        </div>
                        <div class="mt-2 inline-flex items-center rounded-full bg-sky-500/10 px-3 py-1 text-xs font-semibold text-sky-700 ring-1 ring-sky-500/15">
                            WIB • {{ timezoneLabel }}
                        </div>
                        <div class="mt-4 text-sm text-slate-600">
                            Sistem memakai waktu server sebagai sumber waktu resmi presensi.
                        </div>
                    </div>
                </div>
            </section>

            <!-- PRESENSI MAIN CARD -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-1 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500/10 ring-1 ring-sky-500/15">
                            <svg class="h-5 w-5 text-sky-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-base font-semibold text-slate-900">Presensi Hari Ini</div>
                            <div class="text-sm text-slate-600">Kelola presensi masuk dan pulang Anda hari ini.</div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 sm:px-8 sm:py-8">
                    <div
                        v-if="isIzinHariIni"
                        class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
                    >
                        ⚠️ Anda sedang izin hari ini. Presensi dinonaktifkan.
                    </div>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <!-- MASUK -->
                        <div class="rounded-2xl border border-slate-200 bg-white p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1"
                                         :class="sudahMasuk ? 'bg-emerald-500/10 text-emerald-700 ring-emerald-500/15' : 'bg-slate-50 text-slate-600 ring-slate-200'">
                                        PRESENSI MASUK
                                    </div>
                                </div>
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl ring-1"
                                     :class="sudahMasuk ? 'bg-emerald-500/10 ring-emerald-500/15' : 'bg-slate-50 ring-slate-200'">
                                    <svg v-if="sudahMasuk" class="h-6 w-6 text-emerald-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else class="h-6 w-6 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m0 0l3-3m-3 3l-3-3M4 4h16" />
                                    </svg>
                                </div>
                            </div>

                            <div class="mt-6">
                                <div class="text-lg font-semibold text-slate-900">
                                    <template v-if="sudahMasuk">Anda sudah presensi masuk</template>
                                    <template v-else>Anda belum presensi masuk</template>
                                </div>
                                <div class="mt-4 space-y-3 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-2 text-slate-600">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200">
                                                <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
                                                </svg>
                                            </span>
                                            <span>Waktu masuk</span>
                                        </div>
                                        <div class="font-semibold text-slate-900">{{ masukInfo.jam ?? '-' }} WIB</div>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-2 text-slate-600">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200">
                                                <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2c3.866 0 7 3.134 7 7 0 5.25-7 13-7 13S5 14.25 5 9c0-3.866 3.134-7 7-7z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11a2 2 0 100-4 2 2 0 000 4z" />
                                                </svg>
                                            </span>
                                            <span>Lokasi</span>
                                        </div>
                                        <div class="font-semibold text-slate-900">
                                            {{ formatLocation(masukInfo.lat, masukInfo.lng, masukInfo.lokasi) }}
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-2 text-slate-600">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200">
                                                <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-2v13" />
                                                    <circle cx="6" cy="18" r="3" />
                                                    <circle cx="18" cy="16" r="3" />
                                                </svg>
                                            </span>
                                            <span>Akurasi</span>
                                        </div>
                                        <div class="font-semibold text-slate-900">{{ formatAccuracy(masukInfo.accuracy) }}</div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed"
                                    :class="(sudahMasuk || isIzinHariIni)
                                        ? 'bg-emerald-600/70 text-white'
                                        : 'bg-emerald-600 text-white hover:bg-emerald-500 active:bg-emerald-700 focus-visible:ring-emerald-600'"
                                    :disabled="sudahMasuk || isIzinHariIni || isLocating || isSubmitting"
                                    @click="ambilLokasi('masuk')"
                                >
                                    <svg v-if="sudahMasuk" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m0 0l3-3m-3 3l-3-3M4 4h16" />
                                    </svg>
                                    <span v-if="isLocating || isSubmitting">Memproses...</span>
                                    <span v-else-if="sudahMasuk">Sudah Presensi Masuk</span>
                                    <span v-else>Presensi Masuk</span>
                                </button>
                            </div>
                        </div>

                        <!-- KELUAR -->
                        <div class="rounded-2xl border border-slate-200 bg-white p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1"
                                         :class="sudahKeluar ? 'bg-emerald-500/10 text-emerald-700 ring-emerald-500/15' : 'bg-rose-500/10 text-rose-700 ring-rose-500/15'">
                                        PRESENSI KELUAR
                                    </div>
                                </div>
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl ring-1"
                                     :class="sudahKeluar ? 'bg-emerald-500/10 ring-emerald-500/15' : 'bg-rose-500/10 ring-rose-500/15'">
                                    <svg v-if="sudahKeluar" class="h-6 w-6 text-emerald-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else class="h-6 w-6 text-rose-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m0 0l3-3m-3 3l-3-3M4 4h16" />
                                    </svg>
                                </div>
                            </div>

                            <div class="mt-6">
                                <div class="text-lg font-semibold text-slate-900">
                                    <template v-if="sudahKeluar">Anda sudah presensi keluar</template>
                                    <template v-else>Belum presensi keluar</template>
                                </div>
                                <div class="mt-2 text-sm text-slate-600" v-if="!sudahMasuk">
                                    Presensi keluar tersedia setelah Anda melakukan presensi masuk.
                                </div>

                                <div class="mt-4 space-y-3 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-2 text-slate-600">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200">
                                                <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
                                                </svg>
                                            </span>
                                            <span>Waktu keluar</span>
                                        </div>
                                        <div class="font-semibold text-slate-900">{{ keluarInfo.jam ?? '-' }} WIB</div>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-2 text-slate-600">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200">
                                                <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2c3.866 0 7 3.134 7 7 0 5.25-7 13-7 13S5 14.25 5 9c0-3.866 3.134-7 7-7z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11a2 2 0 100-4 2 2 0 000 4z" />
                                                </svg>
                                            </span>
                                            <span>Lokasi</span>
                                        </div>
                                        <div class="font-semibold text-slate-900">
                                            {{ formatLocation(keluarInfo.lat, keluarInfo.lng, keluarInfo.lokasi) }}
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-2 text-slate-600">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200">
                                                <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-2v13" />
                                                    <circle cx="6" cy="18" r="3" />
                                                    <circle cx="18" cy="16" r="3" />
                                                </svg>
                                            </span>
                                            <span>Akurasi</span>
                                        </div>
                                        <div class="font-semibold text-slate-900">{{ formatAccuracy(keluarInfo.accuracy) }}</div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed"
                                    :class="(sudahKeluar || isIzinHariIni)
                                        ? 'bg-emerald-600/70 text-white'
                                        : 'bg-rose-600 text-white hover:bg-rose-500 active:bg-rose-700 focus-visible:ring-rose-600'"
                                    :disabled="!props.presensi || sudahKeluar || isIzinHariIni || isLocating || isSubmitting"
                                    @click="ambilLokasi('keluar')"
                                >
                                    <svg v-if="sudahKeluar" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m0 0l3-3m-3 3l-3-3M4 4h16" />
                                    </svg>
                                    <span v-if="isLocating || isSubmitting">Memproses...</span>
                                    <span v-else-if="sudahKeluar">Sudah Presensi Keluar</span>
                                    <span v-else>Presensi Keluar</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="isLocating || locateWarning" class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div v-if="isLocating" class="text-sm font-medium text-slate-700">
                            {{ locateStatus || 'Mengambil lokasi...' }}
                        </div>
                        <div v-if="locateWarning" class="mt-2 text-sm text-amber-700">
                            {{ locateWarning }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- SUMMARY -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                    <div class="text-base font-semibold text-slate-900">Ringkasan Presensi</div>
                    <div class="mt-1 text-sm text-slate-600">Ringkasan ringan untuk bulan berjalan.</div>
                </div>

                <div class="grid grid-cols-1 gap-4 px-6 py-6 sm:grid-cols-2 sm:px-8 lg:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500/10 ring-1 ring-sky-500/15">
                                <svg class="h-5 w-5 text-sky-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Total Hadir (Bulan Ini)</div>
                                <div class="mt-1 text-2xl font-semibold text-slate-900">
                                    {{ summary?.totalHadirBulanIni ?? '-' }} Hari
                                </div>
                                <div class="text-xs text-slate-600">dari {{ summary?.totalHariBulanIni ?? '-' }} hari</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 ring-1 ring-emerald-500/15">
                                <svg class="h-5 w-5 text-emerald-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Presensi Lengkap</div>
                                <div class="mt-1 text-2xl font-semibold text-slate-900">
                                    {{ summary?.presensiLengkapBulanIni ?? '-' }} Hari
                                </div>
                                <div class="text-xs text-slate-600">masuk + keluar</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 ring-1 ring-amber-500/15">
                                <svg class="h-5 w-5 text-amber-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Rata-rata Jam Kerja</div>
                                <div class="mt-1 text-2xl font-semibold text-slate-900">
                                    {{ formatAvgMinutes(summary?.avgMinutesBekerja) }}
                                </div>
                                <div class="text-xs text-slate-600">per hari (presensi lengkap)</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/10 ring-1 ring-violet-500/15">
                                <svg class="h-5 w-5 text-violet-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2c3.866 0 7 3.134 7 7 0 5.25-7 13-7 13S5 14.25 5 9c0-3.866 3.134-7 7-7z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11a2 2 0 100-4 2 2 0 000 4z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Lokasi Terakhir</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900">
                                    {{
                                        summary?.lokasiTerakhir
                                            ? formatLocation(summary.lokasiTerakhir.lat_masuk, summary.lokasiTerakhir.lng_masuk, summary.lokasiTerakhir.lokasi_masuk)
                                            : '-'
                                    }}
                                </div>
                                <div class="mt-1 text-xs text-slate-600">
                                    <template v-if="summary?.lokasiTerakhir">
                                        {{ summary.lokasiTerakhir.tanggal }} • {{ summary.lokasiTerakhir.jam_masuk }} WIB
                                    </template>
                                    <template v-else>-</template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 pb-6 sm:px-8">
                    <div class="rounded-2xl bg-sky-500/10 ring-1 ring-sky-500/15 p-4 text-sm text-slate-700">
                        Pastikan lokasi Anda akurat sebelum melakukan presensi untuk data yang valid.
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
