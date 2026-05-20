<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import InputError from '../../../Components/InputError.vue';
import InputLabel from '../../../Components/InputLabel.vue';
import PrimaryButton from '../../../Components/PrimaryButton.vue';
import ActionMessage from '../../../Components/ActionMessage.vue';
import TextInput from '../../../Components/TextInput.vue';
import { route } from '../../../lib/route';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

function toDateInput(value) {
    if (!value) return '';
    return String(value).slice(0, 10);
}

const page = usePage();
const authz = computed(() => page.props.authz ?? {});
const canManageProfile = computed(() => !!authz.value?.canManageProfile);
const canViewPayroll = computed(() => !!authz.value?.canViewPayroll);
const canManagePayroll = computed(() => !!authz.value?.canManagePayroll);
const availableRoles = computed(() => page.props.userRoles ?? ['Admin', 'Karyawan']);

const form = useForm({
    nama: props.user.nama ?? '',
    username: props.user.username ?? '',
    email: props.user.email ?? '',
    alamat: props.user.alamat ?? '',
    telepon: props.user.telepon ?? '',
    tanggal_lahir: toDateInput(props.user.tanggal_lahir),
    posisi: props.user.posisi ?? '',
    tanggal_masuk: toDateInput(props.user.tanggal_masuk),
    gaji: props.user?.payroll?.gaji ?? '',
    tipe_gaji: props.user?.payroll?.tipe_gaji ?? 'harian',
    status: props.user.status ?? 'aktif',
    role: props.user.role ?? 'Karyawan',
});

function submit() {
    form.put(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
    });
}
</script>

<template>
    <div>
        <div class="mb-4">
            <h3 class="text-lg font-semibold">Informasi Profil Karyawan</h3>
            <p class="text-sm text-gray-600">Perbarui profil Anda di kolom ini.</p>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <InputLabel for="nama" value="Nama" />
                    <TextInput id="nama" v-model="form.nama" class="block mt-1 w-full" required autocomplete="name" />
                    <InputError class="mt-2" :message="form.errors.nama" />
                </div>

                <div>
                    <InputLabel for="username" value="Username" />
                    <TextInput id="username" v-model="form.username" class="block mt-1 w-full" required autocomplete="username" />
                    <InputError class="mt-2" :message="form.errors.username" />
                </div>

                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput id="email" type="email" v-model="form.email" class="block mt-1 w-full" required autocomplete="email" />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="alamat" value="Alamat" />
                    <TextInput id="alamat" v-model="form.alamat" class="block mt-1 w-full" autocomplete="street-address" />
                    <InputError class="mt-2" :message="form.errors.alamat" />
                </div>

                <div>
                    <InputLabel for="telepon" value="Nomor Telepon" />
                    <TextInput id="telepon" v-model="form.telepon" class="block mt-1 w-full" autocomplete="tel" />
                    <InputError class="mt-2" :message="form.errors.telepon" />
                </div>

                <div>
                    <InputLabel for="tanggal_lahir" value="Tanggal Lahir" />
                    <TextInput id="tanggal_lahir" type="date" v-model="form.tanggal_lahir" class="block mt-1 w-full" />
                    <InputError class="mt-2" :message="form.errors.tanggal_lahir" />
                </div>

                <div>
                    <InputLabel for="posisi" value="Posisi" />
                    <TextInput id="posisi" v-model="form.posisi" class="block mt-1 w-full" :disabled="!canManageProfile" />
                    <InputError class="mt-2" :message="form.errors.posisi" />
                </div>

                <div>
                    <InputLabel for="tanggal_masuk" value="Tanggal Masuk" />
                    <TextInput id="tanggal_masuk" type="date" v-model="form.tanggal_masuk" class="block mt-1 w-full" :disabled="!canManageProfile" />
                    <InputError class="mt-2" :message="form.errors.tanggal_masuk" />
                </div>

                <template v-if="canViewPayroll">
                    <div>
                        <InputLabel for="gaji" value="Gaji" />
                        <TextInput id="gaji" type="number" v-model="form.gaji" class="block mt-1 w-full" :disabled="!canManagePayroll" />
                        <InputError class="mt-2" :message="form.errors.gaji" />
                    </div>

                    <div>
                        <InputLabel for="tipe_gaji" value="Tipe Gaji" />
                        <select
                            id="tipe_gaji"
                            v-model="form.tipe_gaji"
                            class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            :disabled="!canManagePayroll"
                        >
                            <option value="harian">Harian</option>
                            <option value="bulanan">Bulanan</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.tipe_gaji" />
                    </div>
                </template>

                <div>
                    <InputLabel for="status" value="Status" />
                    <select
                        id="status"
                        v-model="form.status"
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        :disabled="!canManageProfile"
                    >
                        <option value="aktif">Aktif</option>
                        <option value="tidak aktif">Tidak Aktif</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.status" />
                </div>

                <div>
                    <InputLabel for="role" value="Role" />
                    <select
                        id="role"
                        v-model="form.role"
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        :disabled="!canManageProfile"
                    >
                        <option v-for="role in availableRoles" :key="role" :value="role">
                            {{ role }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.role" />
                </div>
            </div>

            <div class="flex items-center gap-3">
                <PrimaryButton :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                    Simpan
                </PrimaryButton>

                <ActionMessage :on="form.recentlySuccessful">
                    Tersimpan.
                </ActionMessage>
            </div>
        </form>
    </div>
</template>
