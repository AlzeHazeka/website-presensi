<script setup>
import { computed } from 'vue';
import AttendanceStatusBadge from './AttendanceStatusBadge.vue';
import { ATTENDANCE_STATUS_KEYS, toDateKey } from '../../lib/attendance/statusSystem';

const props = defineProps({
    dayData: {
        type: Object,
        default: null,
    },
});

function parseDateKey(dateKey) {
    const key = toDateKey(dateKey);
    if (!key) return null;
    const [y, m, d] = key.split('-').map((v) => Number(v));
    if (!y || !m || !d) return null;
    return new Date(y, m - 1, d);
}

const dateLabel = computed(() => {
    const date = parseDateKey(props.dayData?.dateKey);
    if (!date) return 'Pilih tanggal';
    return new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }).format(date);
});

const hasSelection = computed(() => Boolean(props.dayData?.dateKey));

const statusKey = computed(() => props.dayData?.statusKey ?? 'unknown');
const lemburStatusKey = ATTENDANCE_STATUS_KEYS.LEMBUR;

const presensi = computed(() => props.dayData?.presensi ?? null);
const izinCuti = computed(() => props.dayData?.izinCuti ?? null);
const lembur = computed(() => props.dayData?.lembur ?? null);
const hasLembur = computed(() => Boolean(props.dayData?.hasLembur));

const isExclusiveIzinCuti = computed(() => Boolean(izinCuti.value));
const hasConflict = computed(() => Boolean(izinCuti.value && (presensi.value || lembur.value)));

function mapsHref(value) {
    const location = String(value ?? '').trim();
    if (!location) return '';
    return `https://www.google.com/maps?q=${encodeURIComponent(location)}`;
}

function formatAccuracy(value) {
    if (value === null || value === undefined || value === '') return '-';
    const n = Number(value);
    if (!Number.isFinite(n)) return '-';
    return `± ${Math.round(n)}m`;
}
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <div class="text-sm font-semibold text-slate-900">Detail Presensi</div>
                <div class="mt-0.5 text-xs text-slate-600">{{ dateLabel }}</div>
            </div>
            <div v-if="hasSelection" class="shrink-0">
                <AttendanceStatusBadge :status-key="statusKey" size="md" />
            </div>
        </div>

        <div class="p-5">
            <div v-if="!hasSelection" class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600 ring-1 ring-slate-200">
                Klik tanggal pada kalender untuk melihat detail presensi di hari tersebut.
            </div>

            <div v-else class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 lg:col-span-2">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 text-xs font-semibold tracking-wider text-slate-500 uppercase">
                            <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Presensi
                        </div>
                        <AttendanceStatusBadge :status-key="statusKey" />
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                            <div class="text-xs font-semibold text-slate-600">Masuk</div>
                            <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                                <span class="text-slate-600">Waktu</span>
                                <span class="font-semibold text-slate-900">{{ presensi?.jam_masuk ?? '-' }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                                <span class="text-slate-600">Lokasi</span>
                                <a
                                    v-if="presensi?.lokasi_masuk"
                                    class="max-w-[60%] truncate font-semibold text-sky-700 underline underline-offset-4 hover:text-sky-600"
                                    :href="mapsHref(presensi?.lokasi_masuk)"
                                    target="_blank"
                                >
                                    {{ presensi?.lokasi_masuk }}
                                </a>
                                <span v-else class="font-semibold text-slate-900">-</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">Akurasi</span>
                                <span class="font-medium text-slate-700">{{ formatAccuracy(presensi?.accuracy_masuk) }}</span>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                            <div class="text-xs font-semibold text-slate-600">Keluar</div>
                            <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                                <span class="text-slate-600">Waktu</span>
                                <span class="font-semibold text-slate-900">{{ presensi?.jam_keluar ?? '-' }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                                <span class="text-slate-600">Lokasi</span>
                                <a
                                    v-if="presensi?.lokasi_keluar"
                                    class="max-w-[60%] truncate font-semibold text-sky-700 underline underline-offset-4 hover:text-sky-600"
                                    :href="mapsHref(presensi?.lokasi_keluar)"
                                    target="_blank"
                                >
                                    {{ presensi?.lokasi_keluar }}
                                </a>
                                <span v-else class="font-semibold text-slate-900">-</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">Akurasi</span>
                                <span class="font-medium text-slate-700">{{ formatAccuracy(presensi?.accuracy_keluar) }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="isExclusiveIzinCuti || hasConflict" class="mt-4 rounded-xl bg-slate-50 p-3 text-xs text-slate-600 ring-1 ring-slate-200">
                        <template v-if="hasConflict">
                            Catatan: hari ini tercatat sebagai <span class="font-semibold text-slate-900">Izin/Cuti</span>. Secara aturan, presensi/lembur tidak semestinya ada di tanggal yang sama.
                        </template>
                        <template v-else>
                            Hari ini tercatat sebagai <span class="font-semibold text-slate-900">Izin/Cuti</span>.
                        </template>
                    </div>
                </div>

                <div v-if="hasLembur" class="rounded-2xl border border-slate-200 bg-white p-4 lg:col-span-2">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 text-xs font-semibold tracking-wider text-slate-500 uppercase">
                            <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                            </svg>
                            Lembur
                        </div>
                        <AttendanceStatusBadge :status-key="lemburStatusKey" />
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                            <div class="text-xs font-semibold text-slate-600">Mulai</div>
                            <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                                <span class="text-slate-600">Waktu</span>
                                <span class="font-semibold text-slate-900">{{ lembur?.jam_mulai_lembur ?? '-' }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                                <span class="text-slate-600">Lokasi</span>
                                <a
                                    v-if="lembur?.lokasi_mulai_lembur"
                                    class="max-w-[60%] truncate font-semibold text-sky-700 underline underline-offset-4 hover:text-sky-600"
                                    :href="mapsHref(lembur?.lokasi_mulai_lembur)"
                                    target="_blank"
                                >
                                    {{ lembur?.lokasi_mulai_lembur }}
                                </a>
                                <span v-else class="font-semibold text-slate-900">-</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">Akurasi</span>
                                <span class="font-medium text-slate-700">{{ formatAccuracy(lembur?.accuracy_mulai_lembur) }}</span>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                            <div class="text-xs font-semibold text-slate-600">Selesai</div>
                            <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                                <span class="text-slate-600">Waktu</span>
                                <span class="font-semibold text-slate-900">{{ lembur?.jam_pulang_lembur ?? '-' }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                                <span class="text-slate-600">Lokasi</span>
                                <a
                                    v-if="lembur?.lokasi_pulang_lembur"
                                    class="max-w-[60%] truncate font-semibold text-sky-700 underline underline-offset-4 hover:text-sky-600"
                                    :href="mapsHref(lembur?.lokasi_pulang_lembur)"
                                    target="_blank"
                                >
                                    {{ lembur?.lokasi_pulang_lembur }}
                                </a>
                                <span v-else class="font-semibold text-slate-900">-</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">Akurasi</span>
                                <span class="font-medium text-slate-700">{{ formatAccuracy(lembur?.accuracy_selesai_lembur) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
