<script setup>
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import DatePickerField from '../../Components/Forms/DatePickerField.vue';
import { route } from '../../lib/route';

const props = defineProps({
    user: { type: Object, required: true },
});

const page = usePage();
const availableRoles = computed(() => page.props.userRoles ?? ['Super Admin', 'Admin', 'Karyawan']);
const successMessage = computed(() => page.props.flash?.success ?? null);

function toDateInput(value) {
    if (!value) return '';
    return String(value).slice(0, 10);
}

const form = useForm({
    nik: props.user.nik ?? '',
    nama: props.user.nama ?? '',
    email: props.user.email ?? '',
    username: props.user.username ?? '',
    alamat: props.user.alamat ?? '',
    telepon: props.user.telepon ?? '',
    posisi: props.user.posisi ?? '',
    tanggal_lahir: toDateInput(props.user.tanggal_lahir),
    tanggal_masuk: toDateInput(props.user.tanggal_masuk),
    gaji: props.user.gaji ?? '',
    tipe_gaji: props.user.tipe_gaji ?? 'harian',
    status: props.user.status ?? 'aktif',
    role: props.user.role ?? 'Karyawan',
});

const passwordForm = useForm({
    password: '',
    password_confirmation: '',
});

function submit() {
    form.put(route('data-user.update', props.user.user_id), { preserveScroll: true });
}

function submitPassword() {
    passwordForm.put(route('update-password', props.user.user_id), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-slate-900">Edit User</h2>
                    <p class="mt-1 text-sm text-slate-600">Perubahan tersinkron otomatis (legacy enum ↔ Spatie roles).</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('data-user.show', props.user.user_id)"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                    >
                        Detail
                    </Link>
                    <Link
                        :href="route('data-user.index')"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                    >
                        Kembali
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            <div
                v-if="successMessage"
                class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-sm"
                role="status"
            >
                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <div class="text-sm font-semibold">Berhasil</div>
                    <div class="mt-0.5 text-sm text-emerald-700">{{ successMessage }}</div>
                </div>
            </div>

            <form class="space-y-6" @submit.prevent="submit">
                <!-- SECTION 1 — IDENTITAS -->
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-6 sm:p-8">
                        <div class="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Identitas</div>
                                <div class="mt-1 text-lg font-semibold text-slate-900">Data Dasar</div>
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

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                    <button
                        type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-sky-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-500 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Menyimpan…</span>
                        <span v-else>Update User</span>
                    </button>
                </div>
            </form>

            <!-- RESET PASSWORD -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8">
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Akun</div>
                            <div class="mt-1 text-lg font-semibold text-slate-900">Reset Password</div>
                            <p class="mt-1 text-sm text-slate-600">Reset password user tanpa mengetahui password lama.</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/10 ring-1 ring-amber-500/15 text-amber-700">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.657 1.343-3 3-3s3 1.343 3 3v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11V9a5 5 0 0110 0v2" />
                            </svg>
                        </div>
                    </div>

                    <form class="grid grid-cols-1 gap-4 sm:grid-cols-2" @submit.prevent="submitPassword">
                        <div>
                            <label class="text-sm font-semibold text-slate-900" for="new_password">Password baru</label>
                            <input
                                id="new_password"
                                v-model="passwordForm.password"
                                type="password"
                                class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                required
                            />
                            <div v-if="passwordForm.errors.password" class="mt-1 text-xs font-medium text-rose-700">{{ passwordForm.errors.password }}</div>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-900" for="new_password_confirmation">Konfirmasi password</label>
                            <input
                                id="new_password_confirmation"
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                required
                            />
                        </div>

                        <div class="sm:col-span-2 flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-slate-900 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                                :disabled="passwordForm.processing"
                            >
                                <span v-if="passwordForm.processing">Menyimpan…</span>
                                <span v-else>Reset Password</span>
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
