<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '../../Layouts/AppLayout.vue';
import { route } from '../../lib/route';

const props = defineProps({
    ucapan: { type: String, required: true },
    tanggalHariIni: { type: String, required: true },
    tanggalHariIniHuman: { type: String, required: true },
    timezoneLabel: { type: String, default: 'Asia/Jakarta (WIB)' },
    izinHariIni: { type: Object, default: null },
    lembur: { type: Object, default: null },
});

const page = usePage();
const nama = computed(() => page.props.auth?.user?.nama ?? 'User');

const isLocating = ref(false);
const isSubmitting = ref(false);

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

function getHumanLocationError(error) {
    const code = error?.code;
    if (code === 1) return 'Izin lokasi ditolak. Silakan izinkan akses lokasi di browser.';
    if (code === 2) return 'Lokasi tidak tersedia. Pastikan GPS/perizinan lokasi aktif.';
    if (code === 3) return 'Timeout mengambil lokasi. Coba lagi saat sinyal lebih stabil.';
    return 'Gagal mendapatkan lokasi. Izinkan akses lokasi lalu coba lagi.';
}

async function ambilLokasi(jenis) {
    if (!navigator.geolocation) {
        showToast('Browser tidak mendukung Geolocation!', 'error');
        return;
    }
    if (isLocating.value || isSubmitting.value) return;

    isLocating.value = true;
    try {
        const position = await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 15_000,
                maximumAge: 0,
            });
        });

        const latitude = position.coords.latitude;
        const longitude = position.coords.longitude;
        const accuracy = typeof position.coords.accuracy === 'number' ? Math.round(position.coords.accuracy) : null;

        await kirimLembur(jenis, { latitude, longitude, accuracy });
    } catch (error) {
        showToast(getHumanLocationError(error), 'error');
    } finally {
        isLocating.value = false;
    }
}

async function kirimLembur(jenis, position) {
    try {
        isSubmitting.value = true;

        const lokasi = `${position.latitude}, ${position.longitude}`;
        const url = jenis === 'mulai' ? route('lembur.mulai') : route('lembur.pulang');

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
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
            showToast(data.message ?? 'Berhasil!', 'success');
            setTimeout(() => window.location.reload(), 900);
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

const isIzinHariIni = computed(() => Boolean(props.izinHariIni));
const sudahMulai = computed(() => Boolean(props.lembur?.jam_mulai_lembur));
const sudahSelesai = computed(() => Boolean(props.lembur?.jam_pulang_lembur));

const disableMulai = computed(() => isIzinHariIni.value || sudahMulai.value || isLocating.value || isSubmitting.value);
const disableSelesai = computed(
    () => isIzinHariIni.value || !sudahMulai.value || sudahSelesai.value || isLocating.value || isSubmitting.value,
);
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Lembur</h2>
                <p class="mt-1 text-sm text-slate-600">Mulai dan selesaikan lembur untuk hari ini.</p>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8 space-y-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
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
                    </div>

                    <div
                        v-if="isIzinHariIni"
                        class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
                    >
                        ⚠️ Anda sedang izin hari ini dan tidak dapat melakukan lembur.
                    </div>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-white p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-base font-semibold text-slate-900">Mulai Lembur</div>
                                    <p class="mt-1 text-sm text-slate-600">
                                        <template v-if="sudahMulai">✅ Sudah mulai lembur</template>
                                        <template v-else>❌ Belum mulai lembur</template>
                                    </p>
                                </div>
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 ring-1 ring-slate-200">
                                    <svg class="h-6 w-6 text-slate-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="mt-4 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600">Waktu mulai</span>
                                    <span class="font-semibold text-slate-900">{{ props.lembur?.jam_mulai_lembur ?? '-' }} WIB</span>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-400 active:bg-emerald-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400/40 disabled:opacity-60"
                                :disabled="disableMulai"
                                @click="ambilLokasi('mulai')"
                            >
                                {{ isLocating || isSubmitting ? 'Memproses...' : 'Mulai Lembur' }}
                            </button>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-base font-semibold text-slate-900">Selesai Lembur</div>
                                    <p class="mt-1 text-sm text-slate-600">
                                        <template v-if="sudahSelesai">✅ Sudah selesai lembur</template>
                                        <template v-else>❌ Belum selesai lembur</template>
                                    </p>
                                </div>
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 ring-1 ring-slate-200">
                                    <svg class="h-6 w-6 text-slate-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="mt-4 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600">Waktu selesai</span>
                                    <span class="font-semibold text-slate-900">{{ props.lembur?.jam_pulang_lembur ?? '-' }} WIB</span>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-rose-500 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-rose-400 active:bg-rose-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400/40 disabled:opacity-60"
                                :disabled="disableSelesai"
                                @click="ambilLokasi('pulang')"
                            >
                                {{ isLocating || isSubmitting ? 'Memproses...' : 'Selesai Lembur' }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

