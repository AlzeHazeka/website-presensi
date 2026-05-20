<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '../../../Components/ActionMessage.vue';
import DialogModal from '../../../Components/DialogModal.vue';
import InputError from '../../../Components/InputError.vue';
import InputLabel from '../../../Components/InputLabel.vue';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import SecondaryButton from '../../../Components/SecondaryButton.vue';
import TextInput from '../../../Components/TextInput.vue';
import { route } from '../../../lib/route';

const props = defineProps({
    sessions: {
        type: Array,
        default: () => [],
    },
});

const confirmingLogout = ref(false);
const passwordInput = ref(null);

const sessions = computed(() => Array.isArray(props.sessions) ? props.sessions : []);

const form = useForm({
    password: '',
});

function confirmLogout() {
    confirmingLogout.value = true;
    setTimeout(() => passwordInput.value?.focus?.(), 250);
}

function logoutOtherBrowserSessions() {
    form.delete(route('other-browser-sessions.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus?.(),
        onFinish: () => form.reset(),
    });
}

function closeModal() {
    confirmingLogout.value = false;
    form.reset();
}
</script>

<template>
    <div>
        <div class="mb-4">
            <h3 class="text-lg font-semibold">Browser Sessions</h3>
            <p class="text-sm text-gray-600">Kelola dan logout sesi aktif di device lain.</p>
        </div>

        <div class="max-w-2xl text-sm text-gray-600">
            Jika diperlukan, Anda bisa logout dari sesi browser lain di semua perangkat. Daftar sesi di bawah mungkin tidak lengkap.
        </div>

        <div v-if="sessions.length > 0" class="mt-5 space-y-4">
            <div v-for="(session, index) in sessions" :key="index" class="flex items-center">
                <div class="text-xs text-gray-700">
                    <div class="font-semibold">
                        {{ session.agent?.platform ?? 'Unknown' }} - {{ session.agent?.browser ?? 'Unknown' }}
                    </div>
                    <div class="text-gray-500">
                        {{ session.ip_address }},
                        <span v-if="session.is_current_device" class="text-green-600 font-semibold">This device</span>
                        <span v-else>Last active {{ session.last_active }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <PrimaryButton type="button" @click="confirmLogout">
                Log Out Other Browser Sessions
            </PrimaryButton>

            <ActionMessage :on="form.recentlySuccessful">
                Done.
            </ActionMessage>
        </div>

        <DialogModal :show="confirmingLogout" @close="closeModal">
            <template #title>
                Log Out Other Browser Sessions
            </template>

            <template #content>
                Silakan masukkan password untuk konfirmasi logout sesi di device lain.

                <div class="mt-4">
                    <InputLabel for="logout_password" value="Password" />
                    <TextInput
                        id="logout_password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        autocomplete="current-password"
                        @keyup.enter="logoutOtherBrowserSessions"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
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
                    @click="logoutOtherBrowserSessions"
                >
                    Logout
                </PrimaryButton>
            </template>
        </DialogModal>
    </div>
</template>

