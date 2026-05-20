<script setup>
import { nextTick, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';
import InputError from '../../Components/InputError.vue';
import InputLabel from '../../Components/InputLabel.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import TextInput from '../../Components/TextInput.vue';
import { route } from '../../lib/route';

const recovery = ref(false);

const codeInput = ref(null);
const recoveryInput = ref(null);

const form = useForm({
    code: '',
    recovery_code: '',
});

function toggleRecovery(value) {
    recovery.value = value;

    nextTick(() => {
        if (recovery.value) {
            recoveryInput.value?.focus?.();
            return;
        }

        codeInput.value?.focus?.();
    });
}

function submit() {
    form.post(route('two-factor.login'), {
        onFinish: () => form.reset('code', 'recovery_code'),
    });
}
</script>

<template>
    <GuestLayout title="Two Factor Challenge">
        <template #heading>
            <h1 class="text-3xl font-bold text-white">Two-Factor Challenge</h1>
        </template>

        <div v-if="!recovery" class="mb-4 text-sm text-gray-600 dark:text-gray-200">
            Silakan konfirmasi akses dengan memasukkan kode autentikasi dari aplikasi authenticator.
        </div>

        <div v-else class="mb-4 text-sm text-gray-600 dark:text-gray-200">
            Silakan konfirmasi akses dengan memasukkan salah satu recovery code Anda.
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <div v-if="!recovery">
                <InputLabel for="code" value="Code" />
                <TextInput
                    id="code"
                    ref="codeInput"
                    v-model="form.code"
                    type="text"
                    inputmode="numeric"
                    class="mt-2 block w-full"
                    autofocus
                    autocomplete="one-time-code"
                />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div v-else>
                <InputLabel for="recovery_code" value="Recovery Code" />
                <TextInput
                    id="recovery_code"
                    ref="recoveryInput"
                    v-model="form.recovery_code"
                    type="text"
                    class="mt-2 block w-full"
                    autocomplete="one-time-code"
                />
                <InputError class="mt-2" :message="form.errors.recovery_code" />
            </div>

            <div class="flex items-center justify-between">
                <button
                    v-if="!recovery"
                    type="button"
                    class="text-sm text-indigo-200 hover:text-white underline"
                    @click="toggleRecovery(true)"
                >
                    Gunakan recovery code
                </button>

                <button
                    v-else
                    type="button"
                    class="text-sm text-indigo-200 hover:text-white underline"
                    @click="toggleRecovery(false)"
                >
                    Gunakan authentication code
                </button>

                <PrimaryButton :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                    Login
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

