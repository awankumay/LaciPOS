<script setup>
import { ref, watch, computed } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Badge } from '@/Components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Command, CommandGroup, CommandItem, CommandList } from '@/Components/ui/command';
import { Search, ChevronRight, ChevronsUpDown, Check, Calendar as CalendarIcon, Filter } from 'lucide-vue-next';
import { useFormatCurrency } from '@/composables/useFormatCurrency';

const props = defineProps({
    orders: Object,
    filters: Object,
});

const { formatRupiah } = useFormatCurrency();

const search = ref(props.filters?.search || '');
const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');
const status = ref(props.filters?.status || '');
const paymentMethod = ref(props.filters?.payment_method || '');

const openStatusBox = ref(false);
const openPaymentBox = ref(false);

const statuses = [
    { value: '', label: 'Semua Status' },
    { value: 'completed', label: 'Selesai' },
    { value: 'cancelled', label: 'Dibatalkan' },
    { value: 'pending', label: 'Tertunda' }
];

const paymentMethods = [
    { value: '', label: 'Semua Metode' },
    { value: 'cash', label: 'Tunai' },
    { value: 'qris', label: 'QRIS' },
    { value: 'transfer', label: 'Transfer Bank' }
];

// Debounced search
let searchTimeout;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

const applyFilters = () => {
    router.get('/orders', {
        search: search.value || undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
        status: status.value || undefined,
        payment_method: paymentMethod.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const page = usePage();
const storeTimezone = computed(() => page.props.storeSettings?.timezone || 'Asia/Jakarta');

const formatDate = (dateString) => {
    const options = { timeZone: storeTimezone.value, year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
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

        <!-- Filters & Actions -->
        <div class="mb-6 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 w-full">
                <!-- Search -->
                <div class="relative flex-1 min-w-[200px] max-w-sm group">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-[#7c8c9a] transition-colors group-focus-within:text-[#00684a]" />
                    <input 
                        v-model="search" 
                        placeholder="Cari Order ID..." 
                        class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pr-4 pl-11 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all placeholder:text-[#a8b3bc] focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10" 
                    />
                </div>

                <!-- Status Popover -->
                <Popover v-model:open="openStatusBox">
                    <PopoverTrigger as-child>
                        <button
                            role="combobox"
                            :aria-expanded="openStatusBox"
                            class="flex items-center justify-between h-10 w-full sm:w-[150px] rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pl-4 pr-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10"
                        >
                            <span class="truncate">{{ status ? statuses.find(s => s.value === status)?.label : 'Semua Status' }}</span>
                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-[#7c8c9a]" />
                        </button>
                    </PopoverTrigger>
                    <PopoverContent class="w-[150px] p-0 bg-white" align="start">
                        <Command>
                            <CommandList>
                                <CommandGroup>
                                    <CommandItem
                                        v-for="st in statuses"
                                        :key="st.value"
                                        :value="st.label"
                                        @select="() => {
                                            status = st.value;
                                            openStatusBox = false;
                                            applyFilters();
                                        }"
                                        class="text-sm cursor-pointer"
                                    >
                                        {{ st.label }}
                                        <Check :class="['ml-auto h-4 w-4', status === st.value ? 'opacity-100 text-[#00684a]' : 'opacity-0']" />
                                    </CommandItem>
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </PopoverContent>
                </Popover>

                <!-- Payment Method Popover -->
                <Popover v-model:open="openPaymentBox">
                    <PopoverTrigger as-child>
                        <button
                            role="combobox"
                            :aria-expanded="openPaymentBox"
                            class="flex items-center justify-between h-10 w-full sm:w-[160px] rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pl-4 pr-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10"
                        >
                            <span class="truncate">{{ paymentMethod ? paymentMethods.find(m => m.value === paymentMethod)?.label : 'Semua Metode' }}</span>
                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-[#7c8c9a]" />
                        </button>
                    </PopoverTrigger>
                    <PopoverContent class="w-[160px] p-0 bg-white" align="start">
                        <Command>
                            <CommandList>
                                <CommandGroup>
                                    <CommandItem
                                        v-for="m in paymentMethods"
                                        :key="m.value"
                                        :value="m.label"
                                        @select="() => {
                                            paymentMethod = m.value;
                                            openPaymentBox = false;
                                            applyFilters();
                                        }"
                                        class="text-sm cursor-pointer"
                                    >
                                        {{ m.label }}
                                        <Check :class="['ml-auto h-4 w-4', paymentMethod === m.value ? 'opacity-100 text-[#00684a]' : 'opacity-0']" />
                                    </CommandItem>
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </PopoverContent>
                </Popover>

                <!-- Date Range -->
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <input type="date" v-model="startDate" @change="applyFilters" class="h-10 rounded-xl border-[1.5px] border-[#c1ccd6] bg-white px-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10" />
                    <span class="text-slate-400">-</span>
                    <input type="date" v-model="endDate" @change="applyFilters" class="h-10 rounded-xl border-[1.5px] border-[#c1ccd6] bg-white px-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10" />
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="rounded-lg border border-slate-200 bg-white">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Order ID</TableHead>
                        <TableHead>Tanggal</TableHead>
                        <TableHead>Kasir</TableHead>
                        <TableHead>Metode</TableHead>
                        <TableHead class="text-right">Total</TableHead>
                        <TableHead class="text-center">Status</TableHead>
                        <TableHead class="text-right w-[80px]">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="order in orders.data" :key="order.id" class="hover:bg-slate-50 transition-colors">
                        <TableCell class="font-medium text-[#001e2b]">{{ order.order_number }}</TableCell>
                        <TableCell>{{ formatDate(order.created_at) }}</TableCell>
                        <TableCell>{{ order.user?.name || 'Unknown' }}</TableCell>
                        <TableCell class="uppercase text-xs font-semibold">{{ order.payment_method }}</TableCell>
                        <TableCell class="text-right font-semibold text-[#001e2b]">{{ formatRupiah(order.total_amount) }}</TableCell>
                        <TableCell class="text-center">
                            <span :class="[
                                'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium',
                                order.status === 'completed' ? 'bg-green-50 text-green-700' :
                                order.status === 'cancelled' ? 'bg-red-50 text-red-700' :
                                'bg-yellow-50 text-yellow-700'
                            ]">
                                {{ order.status === 'completed' ? 'Selesai' : order.status === 'cancelled' ? 'Batal' : 'Tertunda' }}
                            </span>
                        </TableCell>
                        <TableCell class="text-right">
                            <Link :href="`/orders/${order.id}`" title="Detail Pesanan">
                                <Button variant="ghost" size="sm">
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                            </Link>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="orders.data.length === 0">
                        <TableCell colspan="7" class="text-center text-slate-400 py-8">
                            Belum ada riwayat transaksi yang ditemukan.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div v-if="orders.last_page > 1" class="mt-4 flex items-center justify-center gap-2">
            <template v-for="link in orders.links" :key="link.label">
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
    </AppLayout>
</template>
