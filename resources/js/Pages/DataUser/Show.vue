<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import StatusBadge from '../../Components/UI/StatusBadge.vue';
import { route } from '../../lib/route';

const props = defineProps({
    user: { type: Object, required: true },
});

const page = usePage();
const authz = computed(() => page.props.authz ?? {});
const canManageUsers = computed(() => !!authz.value?.canManageUsers);
const canViewPayroll = computed(() => !!authz.value?.canViewPayroll);

function roleTone(role) {
    if (role === 'Super Admin') return 'info';
    if (role === 'Admin') return 'warning';
    return 'slate';
}

function statusTone(status) {
    if (status === 'aktif') return 'success';
    return 'danger';
}

function initials(name) {
    const str = String(name ?? '').trim();
    if (!str) return 'U';
    const parts = str.split(/\s+/).filter(Boolean);
    const first = parts[0]?.[0] ?? 'U';
    const second = parts.length > 1 ? (parts[1]?.[0] ?? '') : (parts[0]?.[1] ?? '');
    return (first + second).toUpperCase();
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-slate-900">Detail User</h2>
                    <p class="mt-1 text-sm text-slate-600">Employee identity profile (read-only untuk Admin).</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('data-user.index')"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                    >
                        Kembali
                    </Link>
                    <Link
                        v-if="canManageUsers"
                        :href="route('data-user.edit', props.user.user_id)"
                        class="inline-flex h-10 items-center justify-center rounded-xl bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                    >
                        Edit
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-base font-bold text-slate-700 ring-1 ring-slate-200">
                            {{ initials(props.user.nama) }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-slate-600">User</div>
                            <div class="mt-0.5 truncate text-2xl font-semibold tracking-tight text-slate-900">
                                {{ props.user.nama }}
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <StatusBadge :label="props.user.role" :tone="roleTone(props.user.role)" />
                                <StatusBadge :label="props.user.status" :tone="statusTone(props.user.status)" />
                            </div>
                        </div>
                    </div>

                    <div class="grid w-full gap-3 sm:w-auto sm:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">NIK</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ props.user.nik ?? '-' }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Username</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ props.user.username ?? '-' }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Email</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ props.user.email ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-12">
                <section class="lg:col-span-8 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-6 sm:p-8">
                        <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Identitas</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">Data Karyawan</div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Telepon</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900">{{ props.user.telepon ?? '-' }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Alamat</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900">{{ props.user.alamat ?? '-' }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Tanggal lahir</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900">{{ props.user.tanggal_lahir ?? '-' }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Tanggal masuk</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900">{{ props.user.tanggal_masuk ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="lg:col-span-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-6 sm:p-8">
                        <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Pekerjaan</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">Informasi Kerja</div>
                        <div class="mt-4 space-y-3">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Posisi</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900">{{ props.user.posisi ?? '-' }}</div>
                            </div>
                            <div v-if="canViewPayroll" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Payroll</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900">
                                    {{ props.user.gaji ?? '-' }} • {{ props.user.tipe_gaji ?? '-' }}
                                </div>
                            </div>
                            <div v-else class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Payroll</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900">Hidden</div>
                                <div class="mt-1 text-xs text-slate-600">Hanya Super Admin yang dapat melihat payroll.</div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
