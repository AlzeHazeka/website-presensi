<script setup>
import { computed, onMounted } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '../../../Layouts/AppLayout.vue';
import { route } from '../../../lib/route';

const props = defineProps({
    presensi: {
        type: Object,
        required: true,
    },
});

const lokasiKantor = '-7.161892,112.621675';

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? '');

const form = useForm({
    previous_url: props.presensi.previous_url ?? '',
    jam_masuk: props.presensi.jam_masuk ?? '',
    lokasi_masuk: props.presensi.lokasi_masuk ?? '',
    jam_keluar: props.presensi.jam_keluar ?? '',
    lokasi_keluar: props.presensi.lokasi_keluar ?? '',
});

function gunakanLokasiMasuk() {
    form.lokasi_masuk = lokasiKantor;
}

function gunakanLokasiKeluar() {
    form.lokasi_keluar = lokasiKantor;
}

async function submit() {
    const result = await Swal.fire({
        title: 'Simpan perubahan presensi?',
        text: 'Pastikan jam masuk/keluar dan lokasi sudah benar.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0EA5E9',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal',
    });

    if (!result.isConfirmed) return;

    form.post(route('admin.presensi.update', props.presensi.id_presensi), {
        preserveScroll: true,
        onSuccess: () => {
            const message = flashSuccess.value || 'Presensi berhasil diperbarui!';
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: message,
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
            });
        },
    });
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
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Edit Presensi</h2>
                <p class="mt-1 text-sm text-slate-600">Perbarui jam dan lokasi presensi untuk koreksi admin.</p>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8 space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Nama karyawan</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ presensi.nama ?? '-' }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Tanggal</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ presensi.tanggal_human ?? '-' }}</div>
                        </div>
                    </div>

                    <form class="space-y-5" @submit.prevent="submit">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="jam_masuk" class="block text-sm font-semibold text-slate-700">Jam masuk</label>
                                <input
                                    id="jam_masuk"
                                    v-model="form.jam_masuk"
                                    type="time"
                                    class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                />
                            </div>
                            <div>
                                <label for="jam_keluar" class="block text-sm font-semibold text-slate-700">Jam keluar</label>
                                <input
                                    id="jam_keluar"
                                    v-model="form.jam_keluar"
                                    type="time"
                                    class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <label for="lokasi_masuk" class="block text-sm font-semibold text-slate-700">Lokasi masuk</label>
                                <div class="flex items-stretch gap-2">
                                    <input
                                        id="lokasi_masuk"
                                        v-model="form.lokasi_masuk"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                        placeholder="lat, lng atau catatan lokasi"
                                    />
                                    <button
                                        type="button"
                                        class="inline-flex h-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/30"
                                        @click="gunakanLokasiMasuk"
                                    >
                                        Gunakan Lokasi Kantor
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="lokasi_keluar" class="block text-sm font-semibold text-slate-700">Lokasi keluar</label>
                                <div class="flex items-stretch gap-2">
                                    <input
                                        id="lokasi_keluar"
                                        v-model="form.lokasi_keluar"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                        placeholder="lat, lng atau catatan lokasi"
                                    />
                                    <button
                                        type="button"
                                        class="inline-flex h-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/30"
                                        @click="gunakanLokasiKeluar"
                                    >
                                        Gunakan Lokasi Kantor
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-sky-500 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-sky-400 active:bg-sky-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40 disabled:opacity-60 sm:w-auto"
                                :disabled="form.processing"
                            >
                                Simpan Perubahan
                            </button>
                            <Link
                                :href="presensi.previous_url || route('admin.presensi.by-date')"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/40 sm:w-auto"
                            >
                                Batal
                            </Link>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
