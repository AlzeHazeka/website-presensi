<script setup>
import { computed, ref, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import ConfirmsPassword from '../../../Components/ConfirmsPassword.vue';
import DangerButton from '../../../Components/DangerButton.vue';
import InputError from '../../../Components/InputError.vue';
import InputLabel from '../../../Components/InputLabel.vue';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import SecondaryButton from '../../../Components/SecondaryButton.vue';
import TextInput from '../../../Components/TextInput.vue';
import { route } from '../../../lib/route';

const props = defineProps({
    requiresConfirmation: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);

const confirmationForm = useForm({
    code: '',
});

const twoFactorEnabled = computed(() => !enabling.value && page.props.auth?.user?.two_factor_enabled);

watch(twoFactorEnabled, () => {
    if (!twoFactorEnabled.value) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
    }
});

function showQrCode() {
    return window.axios.get(route('two-factor.qr-code')).then((response) => {
        qrCode.value = response.data?.svg ?? null;
    });
}

function showSetupKey() {
    return window.axios.get(route('two-factor.secret-key')).then((response) => {
        setupKey.value = response.data?.secretKey ?? null;
    });
}

function showRecoveryCodes() {
    return window.axios.get(route('two-factor.recovery-codes')).then((response) => {
        recoveryCodes.value = response.data ?? [];
    });
}

function enableTwoFactorAuthentication() {
    enabling.value = true;

    router.post(route('two-factor.enable'), {}, {
        preserveScroll: true,
        onSuccess: () => Promise.all([showQrCode(), showSetupKey(), showRecoveryCodes()]),
        onFinish: () => {
            enabling.value = false;
            confirming.value = props.requiresConfirmation;
        },
    });
}

function confirmTwoFactorAuthentication() {
    confirmationForm.post(route('two-factor.confirm'), {
        errorBag: 'confirmTwoFactorAuthentication',
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            qrCode.value = null;
            setupKey.value = null;
        },
    });
}

function regenerateRecoveryCodes() {
    window.axios.post(route('two-factor.recovery-codes')).then(() => showRecoveryCodes());
}

function disableTwoFactorAuthentication() {
    disabling.value = true;

    router.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
            qrCode.value = null;
            setupKey.value = null;
            recoveryCodes.value = [];
        },
        onFinish: () => {
            disabling.value = false;
        },
    });
}
</script>

<template>
    <div>
        <div class="mb-4">
            <h3 class="text-lg font-semibold">Two Factor Authentication</h3>
            <p class="text-sm text-gray-600">Tambahkan keamanan ekstra menggunakan autentikasi dua faktor.</p>
        </div>

        <h4 v-if="twoFactorEnabled && !confirming" class="text-sm font-semibold text-gray-900">
            Two factor authentication sudah aktif.
        </h4>
        <h4 v-else-if="twoFactorEnabled && confirming" class="text-sm font-semibold text-gray-900">
            Selesaikan aktivasi two factor authentication.
        </h4>
        <h4 v-else class="text-sm font-semibold text-gray-900">
            Two factor authentication belum aktif.
        </h4>

        <div class="mt-3 max-w-2xl text-sm text-gray-600">
            Saat 2FA aktif, Anda akan diminta kode OTP saat login. Kode dapat diambil dari aplikasi authenticator.
        </div>

        <div v-if="twoFactorEnabled" class="mt-5">
            <div v-if="qrCode" class="space-y-4">
                <div class="max-w-2xl text-sm text-gray-600">
                    <p v-if="confirming" class="font-semibold">
                        Scan QR code berikut dengan aplikasi authenticator, atau masukkan setup key lalu input OTP.
                    </p>
                    <p v-else>
                        2FA aktif. Scan QR code berikut atau masukkan setup key.
                    </p>
                </div>

                <div class="inline-block rounded bg-white p-2" v-html="qrCode" />

                <div v-if="setupKey" class="max-w-2xl text-sm text-gray-600">
                    <p class="font-semibold">
                        Setup Key: <span class="font-mono">{{ setupKey }}</span>
                    </p>
                </div>

                <div v-if="confirming" class="max-w-sm">
                    <InputLabel for="code" value="OTP Code" />
                    <TextInput
                        id="code"
                        v-model="confirmationForm.code"
                        type="text"
                        class="block mt-1 w-full"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        @keyup.enter="confirmTwoFactorAuthentication"
                    />
                    <InputError :message="confirmationForm.errors.code" class="mt-2" />
                </div>
            </div>

            <div v-if="recoveryCodes.length > 0 && !confirming" class="mt-6">
                <div class="max-w-2xl text-sm text-gray-600">
                    <p class="font-semibold">
                        Simpan recovery codes ini di tempat aman. Ini bisa digunakan jika device 2FA hilang.
                    </p>
                </div>

                <div class="mt-4 grid gap-1 max-w-xl rounded-lg bg-gray-100 px-4 py-4 font-mono text-sm">
                    <div v-for="code in recoveryCodes" :key="code">
                        {{ code }}
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <template v-if="!twoFactorEnabled">
                <ConfirmsPassword @confirmed="enableTwoFactorAuthentication">
                    <PrimaryButton type="button" :class="{ 'opacity-25': enabling }" :disabled="enabling">
                        Enable
                    </PrimaryButton>
                </ConfirmsPassword>
            </template>

            <template v-else>
                <ConfirmsPassword v-if="confirming" @confirmed="confirmTwoFactorAuthentication">
                    <PrimaryButton
                        type="button"
                        :class="{ 'opacity-25': enabling || confirmationForm.processing }"
                        :disabled="enabling || confirmationForm.processing"
                    >
                        Confirm
                    </PrimaryButton>
                </ConfirmsPassword>

                <ConfirmsPassword v-if="recoveryCodes.length > 0 && !confirming" @confirmed="regenerateRecoveryCodes">
                    <SecondaryButton>
                        Regenerate Recovery Codes
                    </SecondaryButton>
                </ConfirmsPassword>

                <ConfirmsPassword v-if="recoveryCodes.length === 0 && !confirming" @confirmed="showRecoveryCodes">
                    <SecondaryButton>
                        Show Recovery Codes
                    </SecondaryButton>
                </ConfirmsPassword>

                <ConfirmsPassword v-if="confirming" @confirmed="disableTwoFactorAuthentication">
                    <SecondaryButton :class="{ 'opacity-25': disabling }" :disabled="disabling">
                        Cancel
                    </SecondaryButton>
                </ConfirmsPassword>

                <ConfirmsPassword v-if="!confirming" @confirmed="disableTwoFactorAuthentication">
                    <DangerButton :class="{ 'opacity-25': disabling }" :disabled="disabling">
                        Disable
                    </DangerButton>
                </ConfirmsPassword>
            </template>
        </div>
    </div>
</template>

