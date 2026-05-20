<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { route } from '../lib/route';

const page = usePage();

const name = computed(() => page.props.auth?.user?.nama ?? 'User');

const clock = ref('');
const hariIni = ref('');

const jumlahHadir = ref('-');
const totalHariKerja = ref('-');
const persentaseKehadiran = ref('-');

const statusHtml = ref('⏳ Memuat data...');
const statusClass = ref('text-gray-700');
const showPresensiButton = ref(false);

let clockTimer = null;

function getJakartaHour(date = new Date()) {
    const hourString = new Intl.DateTimeFormat('en-US', {
        timeZone: 'Asia/Jakarta',
        hour: '2-digit',
        hour12: false,
    }).format(date);

    return Number.parseInt(hourString, 10);
}

const greeting = computed(() => {
    const hour = getJakartaHour();
    if (hour < 12) return `Selamat Pagi, ${name.value}!`;
    if (hour < 18) return `Selamat Siang, ${name.value}!`;
    return `Selamat Malam, ${name.value}!`;
});

function updateClockAndDate() {
    const now = new Date();

    clock.value = now.toLocaleTimeString('id-ID', {
        timeZone: 'Asia/Jakarta',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });

    hariIni.value = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(now);
}

async function fetchPresensiStatus() {
    try {
        const response = await fetch(route('presensi.status'), {
            headers: {
                Accept: 'application/json',
            },
        });

        const data = await response.json();

        if (!data.sudahPresensiMasuk && !data.sudahPresensiKeluar) {
            statusHtml.value = '⚠ Anda belum melakukan presensi hari ini!';
            statusClass.value = 'text-red-600';
            showPresensiButton.value = true;
        } else if (data.sudahPresensiMasuk && !data.sudahPresensiKeluar) {
            statusHtml.value = `✅ Anda sudah melakukan presensi masuk pada pukul <strong>${data.jamMasuk}</strong>, tetapi belum melakukan presensi keluar.`;
            statusClass.value = 'text-yellow-600';
            showPresensiButton.value = true;
        } else if (data.sudahPresensiMasuk && data.sudahPresensiKeluar) {
            statusHtml.value = `✅ Anda telah presensi masuk pada pukul <strong>${data.jamMasuk}</strong> dan keluar pada pukul <strong>${data.jamKeluar}</strong>.`;
            statusClass.value = 'text-green-600';
            showPresensiButton.value = false;
        }

        jumlahHadir.value = data.jumlahHadir;
        totalHariKerja.value = data.totalHariKerja;
        persentaseKehadiran.value = `${data.persentaseKehadiran}%`;
    } catch (error) {
        console.error('Gagal mengambil data presensi:', error);
    }
}

onMounted(() => {
    updateClockAndDate();
    clockTimer = setInterval(updateClockAndDate, 1000);
    fetchPresensiStatus();
});

onUnmounted(() => {
    if (clockTimer) clearInterval(clockTimer);
});
</script>

<template>
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-6 sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-slate-900">{{ greeting }}</h2>
                    <p class="mt-1 text-sm text-slate-600">Semoga aktivitas hari ini lancar.</p>
                    <div class="mt-4 text-sm text-slate-700">
                        <div>
                            <span class="text-slate-500">Hari ini</span>:
                            <span class="font-medium text-slate-900">{{ hariIni }}</span>
                        </div>
                        <div class="mt-1">
                            <span class="text-slate-500">Waktu</span>:
                            <span class="font-mono text-base text-slate-900">{{ clock }}</span>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl bg-sky-500/10 ring-1 ring-sky-500/15 px-4 py-3">
                    <div class="text-xs font-semibold tracking-wide text-slate-700">Quick Tip</div>
                    <div class="mt-1 text-xs text-slate-600">Pastikan presensi masuk & keluar tercatat dengan benar.</div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Status Presensi</h3>
                    <p class="mt-2 text-sm font-medium" :class="statusClass" v-html="statusHtml" />
                </div>
                <Link
                    v-show="showPresensiButton"
                    :href="route('presensi.index')"
                    class="inline-flex items-center justify-center rounded-xl bg-sky-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-sky-400 active:bg-sky-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-sky-500"
                >
                    Buka Presensi
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Kehadiran Bulan Ini</div>
                <div class="mt-3 text-3xl font-semibold text-slate-900">{{ jumlahHadir }}</div>
                <div class="mt-1 text-sm text-slate-600">Hari hadir</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Total Hari Kerja</div>
                <div class="mt-3 text-3xl font-semibold text-slate-900">{{ totalHariKerja }}</div>
                <div class="mt-1 text-sm text-slate-600">Hari kerja</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Persentase Kehadiran</div>
                <div class="mt-3 text-3xl font-semibold text-slate-900">{{ persentaseKehadiran }}</div>
                <div class="mt-1 text-sm text-slate-600">Dari total hari kerja</div>
            </div>
        </div>
    </div>
</template>
