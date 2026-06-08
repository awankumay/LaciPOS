<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useFormatCurrency } from '@/composables/useFormatCurrency';

defineProps({
    appName: String,
});

const { formatRupiah } = useFormatCurrency();
const testPrice = formatRupiah(28000); // Harus menghasilkan "Rp 28.000"
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h1 class="text-2xl font-semibold text-slate-900">Dashboard</h1>
        </template>

        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <p class="text-slate-600">Test format: {{ testPrice }}</p>
        </div>
    </AppLayout>
</template>
