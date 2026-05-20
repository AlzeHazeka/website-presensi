<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DangerButton from '../../../Components/DangerButton.vue';
import DialogModal from '../../../Components/DialogModal.vue';
import InputError from '../../../Components/InputError.vue';
import InputLabel from '../../../Components/InputLabel.vue';
import SecondaryButton from '../../../Components/SecondaryButton.vue';
import TextInput from '../../../Components/TextInput.vue';
import { route } from '../../../lib/route';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

function confirmUserDeletion() {
    confirmingUserDeletion.value = true;
    setTimeout(() => passwordInput.value?.focus?.(), 250);
}

function deleteUser() {
    form.delete(route('current-user.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus?.(),
        onFinish: () => form.reset(),
    });
}

function closeModal() {
    confirmingUserDeletion.value = false;
    form.reset();
}
</script>

<template>
    <div>
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-red-700">Hapus Akun</h3>
            <p class="text-sm text-gray-600">Hapus akun secara permanen.</p>
        </div>

        <div class="max-w-2xl text-sm text-gray-600">
            Setelah akun dihapus, data dan akses akun akan hilang secara permanen. Pastikan Anda yakin sebelum melanjutkan.
        </div>

        <div class="mt-5">
            <DangerButton type="button" @click="confirmUserDeletion">
                Delete Account
            </DangerButton>
        </div>

        <DialogModal :show="confirmingUserDeletion" @close="closeModal">
            <template #title>
                Delete Account
            </template>

            <template #content>
                Masukkan password untuk konfirmasi penghapusan akun.

                <div class="mt-4">
                    <InputLabel for="delete_password" value="Password" />
                    <TextInput
                        id="delete_password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        autocomplete="current-password"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="closeModal">
                    Batal
                </SecondaryButton>

                <DangerButton
                    class="ms-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="deleteUser"
                >
                    Delete Account
                </DangerButton>
            </template>
        </DialogModal>
    </div>
</template>

