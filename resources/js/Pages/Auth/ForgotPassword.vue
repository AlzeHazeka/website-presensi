<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';
import InputError from '../../Components/InputError.vue';
import InputLabel from '../../Components/InputLabel.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import TextInput from '../../Components/TextInput.vue';
import { route } from '../../lib/route';

defineProps({
    status: {
        type: String,
        default: '',
    },
});

const form = useForm({
    email: '',
});

function submit() {
    form.post(route('password.email'));
}
</script>

<template>
    <GuestLayout title="Lupa Password">
        <template #heading>
            <h1 class="text-3xl font-semibold tracking-tight text-white">Lupa Password</h1>
            <p class="mt-2 text-sm text-slate-200/90">Kami akan mengirim tautan reset ke email Anda.</p>
        </template>

        <div class="mb-4 text-sm text-slate-200/90">
            Masukkan email Anda dan kami akan mengirimkan link untuk reset password.
        </div>

        <div v-if="status" class="mb-4 rounded-lg bg-emerald-500/20 px-4 py-3 text-sm text-emerald-100 ring-1 ring-emerald-400/30">
            {{ status }}
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-2 block w-full" required autocomplete="email" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex items-center justify-end">
                <PrimaryButton :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                    Kirim Link Reset
                </PrimaryButton>
            </div>
        </form>

        <template #footer>
            <Link class="font-semibold text-sky-200 hover:text-white underline" :href="route('login')">Kembali ke Login</Link>
        </template>
    </GuestLayout>
</template>
