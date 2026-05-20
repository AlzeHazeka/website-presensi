<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';
import InputError from '../../Components/InputError.vue';
import InputLabel from '../../Components/InputLabel.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import TextInput from '../../Components/TextInput.vue';
import { route } from '../../lib/route';

const form = useForm({
    password: '',
});

function submit() {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <GuestLayout title="Konfirmasi Password">
        <template #heading>
            <h1 class="text-3xl font-semibold tracking-tight text-white">Konfirmasi Password</h1>
            <p class="mt-2 text-sm text-slate-200/90">Untuk keamanan, konfirmasi password sebelum melanjutkan.</p>
        </template>

        <div class="mb-4 text-sm text-slate-200/90">
            Ini adalah area aman. Silakan konfirmasi password Anda sebelum melanjutkan.
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Password" />
                <TextInput id="password" v-model="form.password" type="password" class="mt-2 block w-full" required autocomplete="current-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-end">
                <PrimaryButton :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                    Konfirmasi
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
