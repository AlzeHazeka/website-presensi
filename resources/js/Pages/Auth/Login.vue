<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';
import InputError from '../../Components/InputError.vue';
import InputLabel from '../../Components/InputLabel.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import TextInput from '../../Components/TextInput.vue';
import { route } from '../../lib/route';

const showPassword = ref(false);

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const canResetPassword = computed(() => {
    try {
        route('password.request');
        return true;
    } catch {
        return false;
    }
});

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <GuestLayout title="Login">
        <template #heading>
            <h1 class="text-3xl font-semibold tracking-tight text-white">Selamat Datang</h1>
            <p class="mt-3 max-w-md text-sm leading-relaxed text-slate-200/90">
                Sistem Presensi Karyawan Internal. Silakan login untuk melanjutkan.
            </p>
        </template>

        <div class="mb-8">
            <h2 class="text-2xl font-semibold tracking-tight text-white">Login</h2>
            <p class="mt-2 text-sm text-slate-200/85">
                Gunakan email atau username yang diberikan administrator.
            </p>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <div>
                <InputLabel for="login" value="Email atau Username" />
                <TextInput
                    id="login"
                    v-model="form.login"
                    type="text"
                    class="mt-2 block w-full"
                    required
                    autocomplete="username"
                    placeholder="contoh: budi / budi@perusahaan.com"
                />
                <InputError class="mt-2" :message="form.errors.login" />
            </div>

            <div>
                <InputLabel for="password" value="Password" />
                <div class="relative mt-2">
                    <TextInput
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        class="block w-full pr-10"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-200/80 hover:text-white"
                        @click="showPassword = !showPassword"
                    >
                        <span class="text-sm">{{ showPassword ? 'Sembunyikan' : 'Lihat' }}</span>
                    </button>
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-white/20 bg-white/10 text-sky-400 focus:ring-sky-400/60"
                    />
                    <span class="ms-2 text-sm text-slate-100">Ingat saya</span>
                </label>

                <div v-if="canResetPassword" class="text-sm">
                    <Link :href="route('password.request')" class="font-medium text-sky-200 hover:text-white underline underline-offset-4">
                        Lupa password?
                    </Link>
                </div>
            </div>

            <div class="flex items-center justify-end">
                <PrimaryButton :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                    Login
                </PrimaryButton>
            </div>
        </form>

        <template #footer>
            <span class="text-slate-200/90">
                Belum punya akses? Hubungi admin untuk dibuatkan akun.
            </span>
        </template>
    </GuestLayout>
</template>
