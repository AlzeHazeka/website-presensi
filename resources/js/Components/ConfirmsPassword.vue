<script setup>
import { nextTick, reactive, ref } from 'vue';
import DialogModal from './DialogModal.vue';
import InputError from './InputError.vue';
import PrimaryButton from './PrimaryButton.vue';
import SecondaryButton from './SecondaryButton.vue';
import TextInput from './TextInput.vue';
import { route } from '../lib/route';

const emit = defineEmits(['confirmed']);

defineProps({
    title: {
        type: String,
        default: 'Konfirmasi Password',
    },
    content: {
        type: String,
        default: 'Untuk keamanan, silakan konfirmasi password Anda untuk melanjutkan.',
    },
    button: {
        type: String,
        default: 'Konfirmasi',
    },
});

const confirmingPassword = ref(false);
const passwordInput = ref(null);

const form = reactive({
    password: '',
    error: '',
    processing: false,
});

function startConfirmingPassword() {
    window.axios
        .get(route('password.confirmation'))
        .then((response) => {
            if (response.data?.confirmed) {
                emit('confirmed');
                return;
            }

            confirmingPassword.value = true;
            setTimeout(() => passwordInput.value?.focus?.(), 250);
        })
        .catch(() => {
            confirmingPassword.value = true;
            setTimeout(() => passwordInput.value?.focus?.(), 250);
        });
}

function confirmPassword() {
    form.processing = true;

    window.axios
        .post(route('password.confirm'), { password: form.password })
        .then(() => {
            form.processing = false;
            closeModal();
            nextTick().then(() => emit('confirmed'));
        })
        .catch((error) => {
            form.processing = false;
            form.error = error?.response?.data?.errors?.password?.[0] ?? 'Password tidak sesuai.';
            passwordInput.value?.focus?.();
        });
}

function closeModal() {
    confirmingPassword.value = false;
    form.password = '';
    form.error = '';
}
</script>

<template>
    <span>
        <span @click="startConfirmingPassword">
            <slot />
        </span>

        <DialogModal :show="confirmingPassword" @close="closeModal">
            <template #title>
                {{ title }}
            </template>

            <template #content>
                {{ content }}

                <div class="mt-4">
                    <TextInput
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Password"
                        autocomplete="current-password"
                        @keyup.enter="confirmPassword"
                    />

                    <InputError :message="form.error" class="mt-2" />
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="closeModal">
                    Batal
                </SecondaryButton>

                <PrimaryButton
                    class="ms-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="confirmPassword"
                >
                    {{ button }}
                </PrimaryButton>
            </template>
        </DialogModal>
    </span>
</template>

