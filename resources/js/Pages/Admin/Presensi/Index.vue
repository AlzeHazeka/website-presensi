<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { route } from '../../../lib/route';
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Presensi</h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Pilih Cara Melihat Presensi:</h3>
                    <div class="flex space-x-4">
                        <Link :href="route('admin.presensi.by-date')" class="px-4 py-2 bg-blue-500 text-white rounded">
                            Berdasarkan Tanggal
                        </Link>
                        <Link :href="route('admin.presensi.by-user')" class="px-4 py-2 bg-green-500 text-white rounded">
                            Berdasarkan Karyawan
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

