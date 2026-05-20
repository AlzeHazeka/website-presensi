<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';
import InputError from '../../Components/InputError.vue';
import InputLabel from '../../Components/InputLabel.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import TextInput from '../../Components/TextInput.vue';
import { route } from '../../lib/route';

const form = useForm({
    nama: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <GuestLayout title="Register">
        <template #heading>
            <h1 class="text-3xl font-bold text-white">Buat Akun</h1>
        </template>

        <div class="text-sm text-slate-200/90">
            Pembuatan akun dinonaktifkan untuk publik. Silakan hubungi admin untuk dibuatkan akses.
        </div>

        <template #footer>
            <Link class="font-semibold text-sky-200 hover:text-white underline" :href="route('login')">Kembali ke Login</Link>
        </template>
    </GuestLayout>
</template>
