<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import StatusBadge from '../../Components/UI/StatusBadge.vue';
import LogoutOtherBrowserSessionsForm from './Partials/LogoutOtherBrowserSessionsForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

const props = defineProps({
    confirmsTwoFactorAuthentication: {
        type: Boolean,
        default: false,
    },
    sessions: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const user = computed(() => page.props.auth?.user ?? null);
const authz = computed(() => page.props.authz ?? {});
const canManageProfile = computed(() => !!authz.value?.canManageProfile);
const canResetPassword = computed(() => !!authz.value?.canResetPassword);

function roleTone(role) {
    if (role === 'Super Admin') return 'info';
    if (role === 'Admin') return 'warning';
    return 'slate';
}

function statusTone(status) {
    if (status === 'aktif') return 'success';
    return 'danger';
}

const initials = computed(() => {
    const name = String(user.value?.nama ?? '').trim();
    if (!name) return 'U';
    const parts = name.split(/\s+/).filter(Boolean);
    const first = parts[0]?.[0] ?? 'U';
    const second = parts.length > 1 ? (parts[1]?.[0] ?? '') : (parts[0]?.[1] ?? '');
    return (first + second).toUpperCase();
});
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Employee Identity Profile</h2>
                <p class="mt-1 text-sm text-slate-600">Informasi akun internal untuk kebutuhan operasional.</p>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="relative h-14 w-14 overflow-hidden rounded-2xl bg-slate-100 ring-1 ring-slate-200">
                            <img
                                v-if="user?.profile_photo_url"
                                :src="user.profile_photo_url"
                                alt="Profile photo"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center text-base font-bold text-slate-700">
                                {{ initials }}
                            </div>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-slate-600">Akun</div>
                            <div class="mt-0.5 truncate text-2xl font-semibold tracking-tight text-slate-900">
                                {{ user?.nama ?? '-' }}
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <StatusBadge v-if="user?.role" :label="user.role" :tone="roleTone(user.role)" />
                                <StatusBadge v-if="user?.status" :label="user.status" :tone="statusTone(user.status)" />
                            </div>
                        </div>
                    </div>

                    <div class="grid w-full gap-3 sm:w-auto sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Posisi</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ user?.posisi ?? '-' }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Tanggal masuk</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ user?.tanggal_masuk ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8">
                    <div class="mb-4">
                        <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Identitas</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">Informasi Akun</div>
                        <p class="mt-1 text-sm text-slate-600">
                            <template v-if="canManageProfile">Anda memiliki akses untuk mengubah data profil.</template>
                            <template v-else>Profil bersifat read-only untuk keamanan operasional.</template>
                        </p>
                    </div>

                    <div v-if="canManageProfile && user" class="rounded-2xl border border-slate-200 bg-white">
                        <div class="p-5 sm:p-6">
                            <UpdateProfileInformationForm :user="user" />
                        </div>
                    </div>

                    <div v-else class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Email</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ user?.email ?? '-' }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Username</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ user?.username ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="canResetPassword" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8">
                    <UpdatePasswordForm />
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8">
                    <LogoutOtherBrowserSessionsForm :sessions="props.sessions" />
                </div>
            </section>
        </div>
    </AppLayout>
</template>
