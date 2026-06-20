<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Search, Filter, Calendar, CreditCard, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
    orders: Object,
    filters: Object,
});

const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');
const status = ref(props.filters?.status || '');
const paymentMethod = ref(props.filters?.payment_method || '');

const applyFilter = () => {
    router.get(
        '/orders',
        {
            start_date: startDate.value,
            end_date: endDate.value,
            status: status.value,
            payment_method: paymentMethod.value,
        },
        { preserveState: true, replace: true }
    );
};

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value || 0);
};

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

const getStatusColor = (statusText) => {
    switch (statusText) {
        case 'completed': return 'bg-green-100 text-green-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        default: return 'bg-yellow-100 text-yellow-800';
    }
};
</script>

<template>
    <AppLayout title="Riwayat Transaksi">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between w-full">
                <div>
                    <h1 class="text-lg font-semibold text-[#001e2b]">Riwayat Transaksi</h1>
                    <p class="text-xs text-[#7c8c9a]">Daftar semua transaksi yang pernah dilakukan</p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filters -->
            <div class="rounded-2xl border border-[#e1e5e8] bg-white p-5 shadow-[0_1px_2px_rgba(0,30,43,0.04)]">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[#3d4f5b]">Tanggal Mulai</label>
                        <div class="relative">
                            <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[#7c8c9a]" />
                            <input
                                type="date"
                                v-model="startDate"
                                class="w-full rounded-lg border-[#e1e5e8] pl-9 text-sm focus:border-[#00ed64] focus:ring focus:ring-[#00ed64] focus:ring-opacity-20 transition-all"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[#3d4f5b]">Tanggal Selesai</label>
                        <div class="relative">
                            <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[#7c8c9a]" />
                            <input
                                type="date"
                                v-model="endDate"
                                class="w-full rounded-lg border-[#e1e5e8] pl-9 text-sm focus:border-[#00ed64] focus:ring focus:ring-[#00ed64] focus:ring-opacity-20 transition-all"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[#3d4f5b]">Status</label>
                        <select
                            v-model="status"
                            class="w-full rounded-lg border-[#e1e5e8] text-sm focus:border-[#00ed64] focus:ring focus:ring-[#00ed64] focus:ring-opacity-20 transition-all"
                        >
                            <option value="">Semua Status</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[#3d4f5b]">Metode Pembayaran</label>
                        <select
                            v-model="paymentMethod"
                            class="w-full rounded-lg border-[#e1e5e8] text-sm focus:border-[#00ed64] focus:ring focus:ring-[#00ed64] focus:ring-opacity-20 transition-all"
                        >
                            <option value="">Semua Metode</option>
                            <option value="cash">Tunai (Cash)</option>
                            <option value="qris">QRIS</option>
                            <option value="transfer">Transfer Bank</option>
                        </select>
                    </div>
                    <button
                        @click="applyFilter"
                        class="inline-flex h-[42px] items-center justify-center gap-2 rounded-lg bg-[#001e2b] px-5 py-2 text-sm font-semibold text-white hover:bg-[#1c2d38] transition-colors"
                    >
                        <Filter class="h-4 w-4" />
                        Terapkan
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-2xl border border-[#e1e5e8] bg-white shadow-[0_1px_2px_rgba(0,30,43,0.04)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-[#3d4f5b]">
                        <thead class="bg-[#f4f7f6] text-xs uppercase text-[#7c8c9a] border-b border-[#e1e5e8]">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Order ID</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Tanggal</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Kasir</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Metode</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-right">Total</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Status</th>
                                <th scope="col" class="px-6 py-4 font-semibold"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e1e5e8]">
                            <tr v-if="orders.data.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-[#7c8c9a]">
                                    Belum ada data transaksi yang ditemukan.
                                </td>
                            </tr>
                            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-slate-50 transition-colors group">
                                <td class="px-6 py-4 font-medium text-[#001e2b]">
                                    {{ order.order_number }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ formatDate(order.created_at) }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ order.user?.name || 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 uppercase text-xs font-semibold">
                                    {{ order.payment_method }}
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-[#001e2b]">
                                    {{ formatRupiah(order.total_amount) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider', getStatusColor(order.status)]">
                                        {{ order.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="`/orders/${order.id}`" class="inline-flex items-center justify-center rounded-lg p-2 text-slate-400 hover:bg-[#e3fcef] hover:text-[#00684a] transition-colors">
                                        <ChevronRight class="h-4 w-4" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="border-t border-[#e1e5e8] bg-white px-6 py-4 flex items-center justify-between" v-if="orders.links && orders.links.length > 3">
                    <p class="text-xs text-[#7c8c9a]">
                        Menampilkan <span class="font-medium text-[#001e2b]">{{ orders.from }}</span> hingga <span class="font-medium text-[#001e2b]">{{ orders.to }}</span> dari <span class="font-medium text-[#001e2b]">{{ orders.total }}</span> hasil
                    </p>
                    <div class="flex items-center gap-1">
                        <Link 
                            v-for="(link, k) in orders.links" 
                            :key="k" 
                            :href="link.url || '#'" 
                            v-html="link.label"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-xs font-medium transition-colors"
                            :class="[
                                link.active ? 'bg-[#001e2b] text-white' : 'text-[#5c6c7a] hover:bg-slate-100',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
