<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Calendar, Filter, FileText, Trophy, Medal } from 'lucide-vue-next';

const props = defineProps({
    products: Array,
    filters: Object,
});

const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

const applyFilter = () => {
    router.get(
        '/reports/best-sellers',
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
</script>

<template>
    <AppLayout title="Produk Terlaris">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between w-full">
                <div>
                    <h1 class="text-lg font-semibold text-[#001e2b]">Produk Terlaris</h1>
                    <p class="text-xs text-[#7c8c9a]">Ranking produk berdasarkan jumlah barang terjual</p>
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
                                <th scope="col" class="px-6 py-4 font-semibold w-16 text-center">#</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Nama Produk</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-right">Qty Terjual</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-right">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e1e5e8]">
                            <tr v-if="products.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-[#7c8c9a]">
                                    <div class="flex flex-col items-center justify-center">
                                        <FileText class="mb-2 h-8 w-8 text-[#c1ccd6]" />
                                        <p>Belum ada produk yang terjual pada rentang tanggal ini.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="(product, index) in products" :key="product.product_id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-[#001e2b] text-center">
                                    <div class="flex items-center justify-center">
                                        <Trophy v-if="index === 0" class="h-5 w-5 text-yellow-500" />
                                        <Medal v-else-if="index === 1" class="h-5 w-5 text-slate-400" />
                                        <Medal v-else-if="index === 2" class="h-5 w-5 text-amber-600" />
                                        <span v-else>{{ index + 1 }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-[#001e2b]">
                                    {{ product.product_name }}
                                </td>
                                <td class="px-6 py-4 text-right font-medium">
                                    {{ product.total_qty }}
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-[#00684a]">
                                    {{ formatRupiah(product.total_revenue) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
