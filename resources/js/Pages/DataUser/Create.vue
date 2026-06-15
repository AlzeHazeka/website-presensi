<script setup>
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import DatePickerField from '../../Components/Forms/DatePickerField.vue';
import { route } from '../../lib/route';

const props = defineProps({
    defaultRole: {
        type: String,
        default: 'Karyawan',
    },
    defaultStatus: {
        type: String,
        default: 'aktif',
    },
});

const page = usePage();
const availableRoles = computed(() => page.props.userRoles ?? ['Super Admin', 'Admin', 'Karyawan']);

const form = useForm({
    nik: '',
    nama: '',
    email: '',
    username: '',
    alamat: '',
    telepon: '',
    posisi: '',
    tanggal_lahir: '',
    tanggal_masuk: '',
    gaji: '',
    tipe_gaji: 'harian',
    status: props.defaultStatus,
    role: props.defaultRole,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post(route('data-user.store'));
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-slate-900">Tambah User</h2>
                    <p class="mt-1 text-sm text-slate-600">Centralized control: hanya Super Admin yang bisa membuat user.</p>
                </div>
                <Link
                    :href="route('data-user.index')"
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                >
                    Kembali
                </Link>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            <form class="space-y-6" @submit.prevent="submit">
                <!-- SECTION 1 — IDENTITAS -->
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-6 sm:p-8">
                        <div class="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Identitas</div>
                                <div class="mt-1 text-lg font-semibold text-slate-900">Data Dasar</div>
                                <p class="mt-1 text-sm text-slate-600">Lengkapi identitas user untuk kebutuhan HR operasional.</p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-500/10 ring-1 ring-sky-500/15 text-sky-700">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-sm font-semibold text-slate-900" for="nik">NIK</label>
                                <input
                                    id="nik"
                                    v-model="form.nik"
                                    type="text"
                                    inputmode="numeric"
                                    class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                    placeholder="00001"
                                />
                                <div v-if="form.errors.nik" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.nik }}</div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-900" for="nama">Nama</label>
                                <input
                                    id="nama"
                                    v-model="form.nama"
                                    type="text"
                                    class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                    required
                                />
                                <div v-if="form.errors.nama" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.nama }}</div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-900" for="username">Username</label>
                                <input
                                    id="username"
                                    v-model="form.username"
                                    type="text"
                                    class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                    required
                                />
                                <div v-if="form.errors.username" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.username }}</div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-900" for="email">Email</label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                    required
                                />
                                <div v-if="form.errors.email" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.email }}</div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-900" for="telepon">Telepon</label>
                                <input
                                    id="telepon"
                                    v-model="form.telepon"
                                    type="text"
                                    class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                />
                                <div v-if="form.errors.telepon" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.telepon }}</div>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="text-sm font-semibold text-slate-900" for="alamat">Alamat</label>
                                <textarea
                                    id="alamat"
                                    v-model="form.alamat"
                                    rows="3"
                                    class="mt-2 w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                />
                                <div v-if="form.errors.alamat" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.alamat }}</div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 2 — INFORMASI PEKERJAAN -->
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-6 sm:p-8">
                        <div class="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Pekerjaan</div>
                                <div class="mt-1 text-lg font-semibold text-slate-900">Informasi Kerja</div>
                                <p class="mt-1 text-sm text-slate-600">Role memakai Spatie (hybrid) dan akan tersinkron otomatis.</p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 ring-1 ring-slate-200 text-slate-600">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v14l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z" />
                                </svg>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-sm font-semibold text-slate-900" for="posisi">Posisi</label>
                                <input
                                    id="posisi"
                                    v-model="form.posisi"
                                    type="text"
                                    class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                />
                                <div v-if="form.errors.posisi" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.posisi }}</div>
                            </div>

                            <DatePickerField
                                id="tanggal-masuk"
                                v-model="form.tanggal_masuk"
                                label="Tanggal masuk"
                                helper="Tanggal mulai bekerja (opsional)."
                            />

                            <div>
                                <label class="text-sm font-semibold text-slate-900" for="status">Status</label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                    required
                                >
                                    <option value="aktif">Aktif</option>
                                    <option value="tidak aktif">Tidak aktif</option>
                                </select>
                                <div v-if="form.errors.status" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.status }}</div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-900" for="role">Role</label>
                                <select
                                    id="role"
                                    v-model="form.role"
                                    class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                    required
                                >
                                    <option v-for="role in availableRoles" :key="role" :value="role">{{ role }}</option>
                                </select>
                                <div v-if="form.errors.role" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.role }}</div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-900" for="gaji">Gaji (opsional)</label>
                                <input
                                    id="gaji"
                                    v-model="form.gaji"
                                    type="number"
                                    class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                />
                                <div v-if="form.errors.gaji" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.gaji }}</div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-slate-900" for="tipe_gaji">Tipe gaji</label>
                                <select
                                    id="tipe_gaji"
                                    v-model="form.tipe_gaji"
                                    class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                    required
                                >
                                    <option value="harian">Harian</option>
                                    <option value="bulanan">Bulanan</option>
                                </select>
                                <div v-if="form.errors.tipe_gaji" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.tipe_gaji }}</div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 3 — INFORMASI PERSONAL -->
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-6 sm:p-8">
                        <div class="mb-5">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Personal</div>
                            <div class="mt-1 text-lg font-semibold text-slate-900">Informasi Personal</div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <DatePickerField
                                id="tanggal-lahir"
                                v-model="form.tanggal_lahir"
                                label="Tanggal lahir"
                                helper="Opsional untuk kebutuhan internal."
                            />
                        </div>
                    </div>
                </section>

                <!-- SECTION 4 — AKUN -->
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-6 sm:p-8">
                        <div class="mb-5">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Akun</div>
                            <div class="mt-1 text-lg font-semibold text-slate-900">Kredensial</div>
                            <p class="mt-1 text-sm text-slate-600">Password wajib diisi saat create user.</p>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-sm font-semibold text-slate-900" for="password">Password</label>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                    required
                                />
                                <div v-if="form.errors.password" class="mt-1 text-xs font-medium text-rose-700">{{ form.errors.password }}</div>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-900" for="password_confirmation">Konfirmasi password</label>
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                    required
                                />
                            </div>
                        </div>
                    </div>
                </section>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                    <button
                        type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-sky-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-500 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Menyimpan…</span>
                        <span v-else>Buat User</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
