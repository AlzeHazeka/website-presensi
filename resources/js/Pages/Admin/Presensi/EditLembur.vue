<script setup>
import { computed, onMounted } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AppLayout from '../../../Layouts/AppLayout.vue';
import { route } from '../../../lib/route';

const props = defineProps({
    lembur: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? '');
const showStatusField = computed(() => Boolean(props.lembur.status_field && props.lembur.status_options?.length));
const showNoteField = computed(() => Boolean(props.lembur.note_field));

const form = useForm({
    jam_mulai: props.lembur.jam_mulai ?? '',
    jam_selesai: props.lembur.jam_selesai ?? '',
    status: props.lembur.status ?? '',
    note: props.lembur.note ?? '',
});

async function submit() {
    const result = await Swal.fire({
        title: 'Simpan perubahan lembur?',
        text: 'Pastikan jam mulai, jam selesai, dan status lembur sudah benar.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0EA5E9',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal',
    });

    if (!result.isConfirmed) return;

    form.post(route('admin.presensi.lembur.update', props.lembur.id_lembur), {
        preserveScroll: true,
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
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Edit Lembur</h2>
                <p class="mt-1 text-sm text-slate-600">Perbarui data lembur secara terpisah dari presensi.</p>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8 space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Nama karyawan</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ lembur.nama ?? '-' }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Tanggal</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ lembur.tanggal_human ?? '-' }}</div>
                        </div>
                    </div>

                    <form class="space-y-5" @submit.prevent="submit">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="jam_mulai" class="block text-sm font-semibold text-slate-700">Jam mulai lembur</label>
                                <input
                                    id="jam_mulai"
                                    v-model="form.jam_mulai"
                                    type="time"
                                    class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                    required
                                />
                                <div v-if="form.errors.jam_mulai" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.jam_mulai }}</div>
                            </div>
                            <div>
                                <label for="jam_selesai" class="block text-sm font-semibold text-slate-700">Jam selesai lembur</label>
                                <input
                                    id="jam_selesai"
                                    v-model="form.jam_selesai"
                                    type="time"
                                    class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                />
                                <div class="mt-1 text-xs text-slate-500">Boleh dikosongkan jika lembur belum selesai.</div>
                                <div v-if="form.errors.jam_selesai" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.jam_selesai }}</div>
                            </div>
                        </div>

                        <div v-if="showStatusField || showNoteField" class="grid gap-4 sm:grid-cols-2">
                            <div v-if="showStatusField">
                                <label for="status" class="block text-sm font-semibold text-slate-700">Status lembur</label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                >
                                    <option value="">-</option>
                                    <option v-for="option in lembur.status_options" :key="option" :value="option">
                                        {{ option }}
                                    </option>
                                </select>
                                <div v-if="form.errors.status" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.status }}</div>
                            </div>

                            <div v-if="showNoteField">
                                <label for="note" class="block text-sm font-semibold text-slate-700">Catatan</label>
                                <input
                                    id="note"
                                    v-model="form.note"
                                    type="text"
                                    class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                    placeholder="Catatan lembur"
                                />
                                <div v-if="form.errors.note" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.note }}</div>
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
                                :href="lembur.back_url || route('admin.presensi.by-date')"
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
