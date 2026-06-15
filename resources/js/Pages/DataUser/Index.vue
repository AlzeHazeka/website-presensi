<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Pagination from '../../Components/Pagination.vue';
import EmptyState from '../../Components/UI/EmptyState.vue';
import StatusBadge from '../../Components/UI/StatusBadge.vue';
import { route } from '../../lib/route';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success ?? '');
const roleOptions = computed(() => page.props.userRoles ?? ['Admin', 'Karyawan']);
const authz = computed(() => page.props.authz ?? {});
const canManageUsers = computed(() => !!authz.value?.canManageUsers);

const selectedRole = ref(props.filters?.role ?? '');
const selectedStatus = ref(props.filters?.status ?? '');
const searchQuery = ref(props.filters?.q ?? '');
const isLoading = ref(false);

let searchTimer = null;
watch(
    () => props.filters?.role,
    (value) => {
        selectedRole.value = value ?? '';
    }
);
watch(
    () => props.filters?.status,
    (value) => {
        selectedStatus.value = value ?? '';
    }
);
watch(
    () => props.filters?.q,
    (value) => {
        searchQuery.value = value ?? '';
    }
);

function applyFilters({ immediate = true } = {}) {
    const role = selectedRole.value || null;
    const status = selectedStatus.value || null;
    const q = searchQuery.value || null;

    if (!immediate) {
        if (searchTimer) clearTimeout(searchTimer);
        searchTimer = setTimeout(() => applyFilters({ immediate: true }), 350);
        return;
    }

    isLoading.value = true;
    router.get(
        route('data-user.index'),
        { role, status, q },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        }
    );
}

function ucfirst(value) {
    if (!value) return '-';
    const str = String(value);
    return str.charAt(0).toUpperCase() + str.slice(1);
}

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

function destroyUser(userId) {
    if (!confirm('Apakah Anda yakin ingin menghapus user ini?')) return;

    router.delete(route('data-user.destroy', userId), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout>
        <div class="container mx-auto p-4">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">User Management</h2>
                    <p class="text-sm text-slate-600">
                        <template v-if="canManageUsers">Centralized control: hanya Super Admin yang bisa mengubah data.</template>
                        <template v-else>View-only: Anda hanya dapat melihat data user.</template>
                    </p>
                </div>
                <div class="flex w-full flex-col gap-2 sm:w-auto">
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                        <div class="sm:col-span-1">
                            <label class="mb-1 block text-xs font-semibold text-slate-600">Search</label>
                            <input
                                v-model="searchQuery"
                                type="text"
                                class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                placeholder="NIK / Nama / Username / Email…"
                                @input="applyFilters({ immediate: false })"
                            />
                        </div>
                        <div class="sm:col-span-1">
                            <label class="mb-1 block text-xs font-semibold text-slate-600">Role</label>
                            <select
                                v-model="selectedRole"
                                class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                @change="applyFilters()"
                            >
                                <option value="">Semua</option>
                                <option v-for="role in roleOptions" :key="role" :value="role">
                                    {{ role }}
                                </option>
                            </select>
                        </div>
                        <div class="sm:col-span-1">
                            <label class="mb-1 block text-xs font-semibold text-slate-600">Status</label>
                            <select
                                v-model="selectedStatus"
                                class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                                @change="applyFilters()"
                            >
                                <option value="">Semua</option>
                                <option value="aktif">Aktif</option>
                                <option value="tidak aktif">Tidak aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                        <div v-if="isLoading" class="text-xs font-medium text-slate-500">Memuat…</div>
                        <Link
                            v-if="canManageUsers"
                            :href="route('data-user.create')"
                            class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-sky-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-500 sm:w-auto"
                        >
                            + Tambah User
                        </Link>
                    </div>
                </div>
            </div>

            <div v-if="successMessage" class="alert alert-success mb-4 p-4 bg-green-100 text-green-700 rounded">
                {{ successMessage }}
            </div>

            <div v-if="(users?.data?.length ?? 0) === 0" class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <EmptyState title="Tidak ada data user." description="Coba ubah filter atau kata kunci pencarian." />
            </div>

            <div v-else>
                <!-- Mobile: stacked cards -->
                <div class="space-y-3 sm:hidden">
                    <div
                        v-for="user in users.data"
                        :key="user.user_id"
                        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                    >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-700 ring-1 ring-slate-200">
                                {{ initials(user.nama) }}
                            </div>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-slate-900">{{ user.nama }}</div>
                                <div class="truncate text-xs text-slate-600">{{ user.email }}</div>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <StatusBadge :label="user.role" :tone="roleTone(user.role)" />
                                    <StatusBadge :label="ucfirst(user.status)" :tone="statusTone(user.status)" />
                                </div>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <Link
                                :href="route('data-user.show', user.user_id)"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50"
                                title="Lihat"
                            >
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </Link>

                            <Link
                                v-if="canManageUsers"
                                :href="route('data-user.edit', user.user_id)"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50"
                                title="Edit"
                            >
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </Link>

                            <button
                                v-if="canManageUsers"
                                type="button"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-700 shadow-sm hover:bg-rose-100"
                                title="Hapus"
                                @click="destroyUser(user.user_id)"
                            >
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200">
                            <div class="font-semibold text-slate-600">NIK</div>
                            <div class="mt-0.5 font-semibold text-slate-900">{{ user.nik ?? '-' }}</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200">
                            <div class="font-semibold text-slate-600">Posisi</div>
                            <div class="mt-0.5 font-semibold text-slate-900">{{ user.posisi ?? '-' }}</div>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Desktop: table -->
                <div class="hidden sm:block overflow-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <table class="min-w-full">
                    <thead class="sticky top-0 z-10 bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold tracking-wider text-slate-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold tracking-wider text-slate-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold tracking-wider text-slate-500 uppercase">Posisi</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold tracking-wider text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold tracking-wider text-slate-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in users.data"
                            :key="user.user_id"
                            class="border-t border-slate-100 hover:bg-slate-50/70 odd:bg-white even:bg-slate-50/30"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-xs font-bold text-slate-700 ring-1 ring-slate-200">
                                        {{ initials(user.nama) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-slate-900">{{ user.nama }}</div>
                                        <div class="truncate text-xs text-slate-600">{{ user.nik ?? '-' }} • {{ user.username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ user.email }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ user.posisi ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">
                                <StatusBadge :label="ucfirst(user.status)" :tone="statusTone(user.status)" />
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <StatusBadge :label="user.role" :tone="roleTone(user.role)" />
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('data-user.show', user.user_id)"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50"
                                        title="Lihat"
                                    >
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </Link>

                                    <Link
                                        v-if="canManageUsers"
                                        :href="route('data-user.edit', user.user_id)"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50"
                                        title="Edit"
                                    >
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>

                                    <button
                                        v-if="canManageUsers"
                                        type="button"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-700 shadow-sm hover:bg-rose-100"
                                        title="Hapus"
                                        @click="destroyUser(user.user_id)"
                                    >
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>

            <div class="mt-4">
                <Pagination :links="users.links" />
            </div>
        </div>
    </AppLayout>
</template>
