<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Download, FileText, Calendar, Filter } from 'lucide-vue-next';

const props = defineProps({
    dailyRevenue: Array,
    grandTotal: [String, Number],
    filters: Object,
});

const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

const applyFilter = () => {
    router.get(
        '/reports/revenue',
        {
            start_date: startDate.value,
            end_date: endDate.value,
        },
        { preserveState: true, replace: true }
    );
};

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value || 0);
};

const formatDate = (dateString) => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};
</script>

<template>
    <AppLayout title="Laporan Pendapatan">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between w-full">
                <div>
                    <h1 class="text-lg font-semibold text-[#001e2b]">Laporan Pendapatan</h1>
                    <p class="text-xs text-[#7c8c9a]">Pantau omzet harian bisnis Anda</p>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="`/reports/revenue/export-pdf?start_date=${startDate}&end_date=${endDate}`" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-[#e1e5e8] bg-white px-3 py-1.5 text-xs font-semibold text-[#3d4f5b] hover:bg-slate-50 transition-colors">
                        <Download class="h-3.5 w-3.5" />
                        Export PDF
                    </a>
                    <a :href="`/reports/revenue/export-csv?start_date=${startDate}&end_date=${endDate}`" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-[#e1e5e8] bg-white px-3 py-1.5 text-xs font-semibold text-[#3d4f5b] hover:bg-slate-50 transition-colors">
                        <FileText class="h-3.5 w-3.5" />
                        Export CSV
                    </a>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filters -->
            <div class="rounded-2xl border border-[#e1e5e8] bg-white p-5 shadow-[0_1px_2px_rgba(0,30,43,0.04)]">
                <div class="flex flex-col sm:flex-row items-end gap-4">
                    <div class="w-full sm:w-auto">
                        <label for="start_date" class="mb-1.5 block text-xs font-semibold text-[#3d4f5b]">Tanggal Mulai</label>
                        <div class="relative">
                            <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[#7c8c9a]" />
                            <input
                                id="start_date"
                                type="date"
                                v-model="startDate"
                                class="w-full sm:w-48 rounded-lg border-[#e1e5e8] pl-9 text-sm focus:border-[#00ed64] focus:ring focus:ring-[#00ed64] focus:ring-opacity-20 transition-all"
                            />
                        </div>
                    </div>
                    <div class="w-full sm:w-auto">
                        <label for="end_date" class="mb-1.5 block text-xs font-semibold text-[#3d4f5b]">Tanggal Selesai</label>
                        <div class="relative">
                            <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[#7c8c9a]" />
                            <input
                                id="end_date"
                                type="date"
                                v-model="endDate"
                                class="w-full sm:w-48 rounded-lg border-[#e1e5e8] pl-9 text-sm focus:border-[#00ed64] focus:ring focus:ring-[#00ed64] focus:ring-opacity-20 transition-all"
                            />
                        </div>
                    </div>
                    <button
                        @click="applyFilter"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-[#001e2b] px-5 py-2 text-sm font-semibold text-white hover:bg-[#1c2d38] transition-colors"
                    >
                        <Filter class="h-4 w-4" />
                        Terapkan Filter
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-2xl border border-[#e1e5e8] bg-white shadow-[0_1px_2px_rgba(0,30,43,0.04)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-[#3d4f5b]">
                        <thead class="bg-[#f4f7f6] text-xs uppercase text-[#7c8c9a] border-b border-[#e1e5e8]">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Tanggal</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-right">Jumlah Transaksi</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-right">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e1e5e8]">
                            <tr v-if="dailyRevenue.length === 0">
                                <td colspan="3" class="px-6 py-8 text-center text-[#7c8c9a]">
                                    <div class="flex flex-col items-center justify-center">
                                        <FileText class="mb-2 h-8 w-8 text-[#c1ccd6]" />
                                        <p>Tidak ada data transaksi pada rentang tanggal ini.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="row in dailyRevenue" :key="row.date" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-[#001e2b]">
                                    {{ formatDate(row.date) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    {{ row.total_transactions }}
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-[#00684a]">
                                    {{ formatRupiah(row.total_revenue) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-[#001e2b] text-white">
                            <tr>
                                <td colspan="2" class="px-6 py-4 text-right font-bold uppercase text-xs tracking-wider">
                                    Grand Total
                                </td>
                                <td class="px-6 py-4 text-right text-lg font-bold text-[#00ed64]">
                                    {{ formatRupiah(grandTotal) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
