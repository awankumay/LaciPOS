<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ReportTabs from '@/Components/ReportTabs.vue';
import { Download, FileText, Calendar, Filter, Info } from 'lucide-vue-next';

const props = defineProps({
    totalTax: [String, Number],
    totalSc: [String, Number],
    details: Object,
    filters: Object,
});

const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

const applyFilter = () => {
    router.get(
        '/reports/tax-summary',
        {
            start_date: startDate.value,
            end_date: endDate.value,
        },
        { preserveState: true, replace: true }
    );
};

const sanitizeDate = (val) => /^\d{4}-\d{2}-\d{2}$/.test(val) ? val : '';
const exportUrl = computed(() => `start_date=${sanitizeDate(startDate.value)}&end_date=${sanitizeDate(endDate.value)}`);

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value || 0);
};

const formatDate = (dateString) => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};
</script>

<template>
    <AppLayout title="Laporan Pajak & Service Charge">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between w-full">
                <div>
                    <h1 class="text-lg font-semibold text-[#001e2b]">Laporan Pajak & Service Charge</h1>
                    <p class="text-xs text-[#7c8c9a]">Rekap uang titipan negara (pajak) dan jasa karyawan (service charge)</p>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="`/reports/tax-summary/export-pdf?${exportUrl}`" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-[#e1e5e8] bg-white px-3 py-1.5 text-xs font-semibold text-[#3d4f5b] hover:bg-slate-50 transition-colors">
                        <Download class="h-3.5 w-3.5" />
                        Export PDF
                    </a>
                    <a :href="`/reports/tax-summary/export-csv?${exportUrl}`" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-[#e1e5e8] bg-white px-3 py-1.5 text-xs font-semibold text-[#3d4f5b] hover:bg-slate-50 transition-colors">
                        <FileText class="h-3.5 w-3.5" />
                        Export CSV
                    </a>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <ReportTabs />

            <!-- Info banner -->
            <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800 flex items-start gap-3">
                <Info class="h-5 w-5 shrink-0 text-blue-500 mt-0.5" />
                <div>
                    <p class="font-semibold">Uang Titipan Negara & Karyawan</p>
                    <p class="mt-1 text-xs">Pajak merupakan titipan yang disetorkan ke negara, Service Charge merupakan hak karyawan. Keduanya bukan hak milik toko.</p>
                </div>
            </div>

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

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="rounded-2xl border border-[#e1e5e8] bg-white p-6 shadow-[0_1px_2px_rgba(0,30,43,0.04)] flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-50">
                        <FileText class="h-7 w-7 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-[#7c8c9a]">Total Pajak Dipungut</p>
                        <p class="text-2xl font-bold text-blue-700">{{ formatRupiah(totalTax) }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-[#e1e5e8] bg-white p-6 shadow-[0_1px_2px_rgba(0,30,43,0.04)] flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-purple-50">
                        <FileText class="h-7 w-7 text-purple-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-[#7c8c9a]">Total Service Charge Terkumpul</p>
                        <p class="text-2xl font-bold text-purple-700">{{ formatRupiah(totalSc) }}</p>
                    </div>
                </div>
            </div>

            <!-- Detail Table -->
            <div class="rounded-2xl border border-[#e1e5e8] bg-white shadow-[0_1px_2px_rgba(0,30,43,0.04)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-[#3d4f5b]">
                        <thead class="bg-[#f4f7f6] text-xs uppercase text-[#7c8c9a] border-b border-[#e1e5e8]">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold whitespace-nowrap">Tanggal</th>
                                <th scope="col" class="px-6 py-4 font-semibold whitespace-nowrap">No. Invoice</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-right whitespace-nowrap">Subtotal</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-right whitespace-nowrap">Tax Rate</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-right whitespace-nowrap">Tax Amount</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-right whitespace-nowrap">Service Charge</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-right whitespace-nowrap">Grand Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e1e5e8]">
                            <tr v-if="details.data.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-[#7c8c9a]">
                                    <div class="flex flex-col items-center justify-center">
                                        <FileText class="mb-2 h-8 w-8 text-[#c1ccd6]" />
                                        <p>Tidak ada data transaksi pada rentang tanggal ini.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="row in details.data" :key="row.order_number" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(row.date) }}</td>
                                <td class="px-6 py-4 font-medium text-[#001e2b] whitespace-nowrap">{{ row.order_number }}</td>
                                <td class="px-6 py-4 text-right">{{ formatRupiah(row.subtotal) }}</td>
                                <td class="px-6 py-4 text-right">{{ row.tax_rate }}</td>
                                <td class="px-6 py-4 text-right font-medium text-blue-600">{{ formatRupiah(row.tax_amount) }}</td>
                                <td class="px-6 py-4 text-right font-medium text-purple-600">{{ formatRupiah(row.service_charge_amount) }}</td>
                                <td class="px-6 py-4 text-right font-bold text-[#001e2b]">{{ formatRupiah(row.grand_total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="details.last_page > 1" class="flex items-center justify-center gap-2 mt-4">
                <template v-for="link in details.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="rounded-md px-3 py-1 text-sm"
                        :class="link.active ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                        v-html="link.label"
                        preserve-state
                    />
                    <span v-else class="px-3 py-1 text-sm text-slate-300" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>