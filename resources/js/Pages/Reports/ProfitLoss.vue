<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ReportTabs from '@/Components/ReportTabs.vue';
import { Calendar, Filter, Wallet, ArrowDownCircle, ArrowUpCircle, Info, Download, FileText } from 'lucide-vue-next';

const props = defineProps({
    netRevenue: [String, Number],
    totalCogs: [String, Number],
    grossProfit: [String, Number],
    totalMdr: [String, Number],
    netAfterMdr: [String, Number],
    marginPercentage: [String, Number],
    totalTax: [String, Number],
    totalSc: [String, Number],
    filters: Object,
});

const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

const applyFilter = () => {
    router.get(
        '/reports/profit-loss',
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
</script>

<template>
    <AppLayout title="Laporan Laba/Rugi">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between w-full">
                <div>
                    <h1 class="text-lg font-semibold text-[#001e2b]">Laporan Laba/Rugi (Kotor)</h1>
                    <p class="text-xs text-[#7c8c9a]">Hitung selisih harga jual dan harga modal</p>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="`/reports/profit-loss/export-pdf?${exportUrl}`" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-[#e1e5e8] bg-white px-3 py-1.5 text-xs font-semibold text-[#3d4f5b] hover:bg-slate-50 transition-colors">
                        <Download class="h-3.5 w-3.5" />
                        Export PDF
                    </a>
                    <a :href="`/reports/profit-loss/export-csv?${exportUrl}`" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-[#e1e5e8] bg-white px-3 py-1.5 text-xs font-semibold text-[#3d4f5b] hover:bg-slate-50 transition-colors">
                        <FileText class="h-3.5 w-3.5" />
                        Export CSV
                    </a>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <ReportTabs />

            <!-- Disclaimer -->
            <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800 flex items-start gap-3">
                <Info class="h-5 w-5 shrink-0 text-blue-500 mt-0.5" />
                <div>
                    <p class="font-semibold">Informasi Laba Kotor</p>
                    <p class="mt-1 text-xs">Nilai yang ditampilkan adalah laba kotor, dihitung murni dari harga jual (snapshot) dikurangi harga beli/modal (snapshot). Laporan ini belum memperhitungkan biaya operasional (listrik, gaji karyawan, sewa, dll).</p>
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
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Card 1: Total Pendapatan Bersih -->
                <div class="rounded-2xl border border-[#e1e5e8] bg-white p-5 shadow-[0_1px_2px_rgba(0,30,43,0.04)] flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-green-50">
                        <ArrowUpCircle class="h-6 w-6 text-green-600" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-[#7c8c9a] truncate">Total Pendapatan Bersih</p>
                        <p class="text-lg font-bold text-[#001e2b]">{{ formatRupiah(netRevenue) }}</p>
                    </div>
                </div>

                <!-- Card 2: Total Modal (COGS) -->
                <div class="rounded-2xl border border-[#e1e5e8] bg-white p-5 shadow-[0_1px_2px_rgba(0,30,43,0.04)] flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50">
                        <ArrowDownCircle class="h-6 w-6 text-red-600" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-[#7c8c9a] truncate">Total Modal (COGS)</p>
                        <p class="text-lg font-bold text-[#001e2b]">{{ formatRupiah(totalCogs) }}</p>
                    </div>
                </div>

                <!-- Card 3: Laba Kotor -->
                <div class="rounded-2xl border border-[#e1e5e8] bg-white p-5 shadow-[0_1px_2px_rgba(0,30,43,0.04)] flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#001e2b]">
                        <Wallet class="h-6 w-6 text-[#00ed64]" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-[#7c8c9a] truncate">Laba Kotor</p>
                        <p class="text-lg font-bold" :class="grossProfit >= 0 ? 'text-[#00684a]' : 'text-red-600'">
                            {{ formatRupiah(grossProfit) }}
                        </p>
                    </div>
                </div>

                <!-- Card 4: Potongan MDR -->
                <div class="rounded-2xl border border-[#e1e5e8] bg-white p-5 shadow-[0_1px_2px_rgba(0,30,43,0.04)] flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-50">
                        <ArrowDownCircle class="h-6 w-6 text-orange-600" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-[#7c8c9a] truncate">Total Potongan MDR</p>
                        <p class="text-lg font-bold text-orange-600">-{{ formatRupiah(totalMdr) }}</p>
                    </div>
                </div>

                <!-- Card 5: Total Dana Cair Bersih / Laba Setelah MDR -->
                <div class="rounded-2xl border border-[#e1e5e8] bg-white p-5 shadow-[0_1px_2px_rgba(0,30,43,0.04)] flex items-center gap-3 relative overflow-hidden">
                    <div class="absolute -right-2 -top-2 opacity-5">
                        <Wallet class="h-20 w-20" />
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#00684a]">
                        <Wallet class="h-6 w-6 text-white" />
                    </div>
                    <div class="relative z-10 min-w-0">
                        <p class="text-xs font-medium text-[#7c8c9a] truncate">Total Dana Cair Bersih</p>
                        <p class="text-lg font-bold" :class="netAfterMdr >= 0 ? 'text-[#00684a]' : 'text-red-600'">
                            {{ formatRupiah(netAfterMdr) }}
                        </p>
                        <p class="mt-0.5 text-[10px] font-semibold" :class="marginPercentage >= 0 ? 'text-[#00ed64]' : 'text-red-500'">
                            Margin: {{ marginPercentage }}%
                        </p>
                    </div>
                </div>
            </div>

            <!-- Additional Info: Total Pajak & Service Charge -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl border border-[#e1e5e8] bg-white p-5 shadow-[0_1px_2px_rgba(0,30,43,0.04)] flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#e3fcef]">
                        <FileText class="h-6 w-6 text-[#00684a]" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-[#7c8c9a]">Total Pajak Terkumpul</p>
                        <p class="text-lg font-bold text-[#00684a]">{{ formatRupiah(totalTax) }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-[#e1e5e8] bg-white p-5 shadow-[0_1px_2px_rgba(0,30,43,0.04)] flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#f4f7f6]">
                        <FileText class="h-6 w-6 text-[#003d4f]" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-[#7c8c9a]">Total Service Charge Terkumpul</p>
                        <p class="text-lg font-bold text-[#001e2b]">{{ formatRupiah(totalSc) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
