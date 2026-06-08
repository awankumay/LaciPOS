<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    appName: String,
});
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h1 class="text-2xl font-semibold text-slate-900">Dashboard</h1>
        </template>

        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <p class="text-slate-600">
                Selamat datang di {{ appName }}! Layout dengan sidebar berhasil.
            </p>
        </div>
    </AppLayout>
</template>
