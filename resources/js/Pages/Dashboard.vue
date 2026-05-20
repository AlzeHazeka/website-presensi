<script setup>
import AppLayout from '../Layouts/AppLayout.vue';
import Welcome from '../Components/Welcome.vue';
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Dashboard</h2>
                <p class="mt-1 text-sm text-slate-600">Ringkasan aktivitas dan status presensi.</p>
            </div>
        </template>

        <div class="max-w-7xl mx-auto">
            <div class="rounded-2xl bg-white border border-slate-200 shadow-sm">
                <div class="p-6 sm:p-8">
                    <Welcome />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
