<script setup>
import { ref, watch, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import Modal from '@/Components/Modal.vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Command, CommandGroup, CommandItem, CommandList } from '@/Components/ui/command';
import { Plus, Pencil, Trash2, Calendar, Clock, PercentCircle, Search, Filter, ChevronsUpDown, Check } from 'lucide-vue-next';

const props = defineProps({
    discounts: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');
const openStatusBox = ref(false);

const statuses = [
    { value: '', label: 'Semua Status' },
    { value: 'active', label: 'Aktif' },
    { value: 'inactive', label: 'Nonaktif' }
];

watch(status, () => applyFilters());

let searchTimeout;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});
onUnmounted(() => clearTimeout(searchTimeout));

const applyFilters = () => {
    router.get('/discounts', {
        search: search.value || undefined,
        status: status.value || undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const showDeleteDialog = ref(false);
const deletingDiscount = ref(null);

const confirmDelete = (discount) => {
    deletingDiscount.value = discount;
    showDeleteDialog.value = true;
};

const executeDelete = () => {
    router.delete(`/discounts/${deletingDiscount.value.id}`, {
        onFinish: () => {
            showDeleteDialog.value = false;
            deletingDiscount.value = null;
        },
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    // Gunakan regex untuk mengambil tahun, bulan, tanggal untuk menghindari pergeseran timezone JS
    const match = dateString.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (match) {
        const [, year, month, day] = match;
        const date = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
        return date.toLocaleDateString('id-ID', {
            day: 'numeric', month: 'short', year: 'numeric'
        });
    }
    
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
    });
};
</script>

<template>
    <AppLayout title="Master Diskon">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between w-full">
                <div>
                    <h1 class="text-lg font-semibold text-[#001e2b]">Master Diskon</h1>
                    <p class="text-xs text-[#7c8c9a]">Kelola daftar promo dan diskon untuk toko Anda.</p>
                </div>
            </div>
        </template>

        <!-- Filters & Actions -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <div class="relative flex-1 min-w-[240px] max-w-sm group">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-[18px] w-[18px] text-[#7c8c9a] transition-colors group-focus-within:text-[#00684a]" />
                    <input 
                        v-model="search" 
                        placeholder="Cari diskon..." 
                        class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pr-4 pl-11 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all placeholder:text-[#a8b3bc] focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10" 
                    />
                </div>
                <Popover v-model:open="openStatusBox">
                    <PopoverTrigger as-child>
                        <button
                            role="combobox"
                            :aria-expanded="openStatusBox"
                            class="flex items-center justify-between h-10 w-full sm:w-[160px] rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pl-4 pr-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10"
                        >
                            <span class="truncate">{{ status ? statuses.find(s => s.value === status)?.label : 'Semua Status' }}</span>
                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-[#7c8c9a]" />
                        </button>
                    </PopoverTrigger>
                    <PopoverContent class="w-[160px] p-0 bg-white" align="start">
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
                                        }"
                                        class="text-sm cursor-pointer"
                                    >
                                        {{ st.label }}
                                        <Check
                                            :class="['ml-auto h-4 w-4', status === st.value ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                        />
                                    </CommandItem>
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </PopoverContent>
                </Popover>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <input type="date" v-model="startDate" @change="applyFilters" class="h-10 rounded-xl border-[1.5px] border-[#c1ccd6] bg-white px-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10" />
                    <span class="text-slate-400">-</span>
                    <input type="date" v-model="endDate" @change="applyFilters" class="h-10 rounded-xl border-[1.5px] border-[#c1ccd6] bg-white px-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10" />
                </div>
            </div>
            <Link href="/discounts/create" class="shrink-0 w-full sm:w-auto">
                <Button class="h-10 rounded-xl w-full sm:w-auto bg-[#001e2b] text-white hover:bg-[#1c2d38]">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Diskon
                </Button>
            </Link>
        </div>

        <div class="w-full pb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-[#e1e5e8] overflow-hidden">
                <div v-if="discounts.data.length === 0" class="flex flex-col items-center justify-center py-20 text-center px-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-slate-400 mb-4">
                        <PercentCircle class="h-8 w-8" />
                    </div>
                    <h3 class="text-base font-semibold text-slate-900 mb-1">Belum ada Master Diskon</h3>
                    <p class="text-sm text-slate-500 max-w-sm">Buat diskon seperti "Promo Gajian" lalu terapkan ke produk yang Anda inginkan.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-[#f8fafc] text-[#5c6c7a] border-b border-[#e1e5e8]">
                            <tr>
                                <th class="px-6 py-4 font-semibold w-1/3">Nama Diskon</th>
                                <th class="px-6 py-4 font-semibold">Potongan</th>
                                <th class="px-6 py-4 font-semibold">Periode (Tgl)</th>
                                <th class="px-6 py-4 font-semibold">Waktu (Jam)</th>
                                <th class="px-6 py-4 font-semibold">Status</th>
                                <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e1e5e8]">
                            <tr v-for="discount in discounts.data" :key="discount.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-[#001e2b]">{{ discount.name }}</div>
                                    <div class="text-xs text-[#7c8c9a] mt-0.5">Kuota: {{ discount.quota ? `${discount.quota_used} / ${discount.quota}` : 'Tanpa batas' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                        {{ discount.discount_type === 'percentage' ? discount.discount_value + '%' : 'Rp ' + Number(discount.discount_value).toLocaleString('id-ID') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5 text-slate-600">
                                        <Calendar class="h-3.5 w-3.5" />
                                        <span>{{ formatDate(discount.start_date) }} - {{ formatDate(discount.end_date) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5 text-slate-600">
                                        <Clock class="h-3.5 w-3.5" />
                                        <span>{{ discount.start_time ? discount.start_time.substring(0,5) : '-' }} - {{ discount.end_time ? discount.end_time.substring(0,5) : '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium',
                                        discount.is_active ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600'
                                    ]">
                                        {{ discount.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="`/discounts/${discount.id}/edit`">
                                            <button class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-[#00684a] transition-colors">
                                                <Pencil class="h-4 w-4" />
                                            </button>
                                        </Link>
                                        <button @click="confirmDelete(discount)" class="flex h-8 w-8 items-center justify-center rounded-lg text-red-500 hover:bg-red-50 transition-colors">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <div v-if="discounts.last_page > 1" class="mt-4 flex items-center justify-center gap-2">
                <template v-for="link in discounts.links" :key="link.label">
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

        <!-- Delete Confirmation Modal -->
        <Modal
            :show="showDeleteDialog"
            @update:show="showDeleteDialog = $event"
            title="Hapus Diskon"
            :description="`Apakah Anda yakin ingin menghapus diskon \u0022${deletingDiscount?.name}\u0022? Diskon ini tidak akan bisa digunakan lagi.`"
        >
            <template #footer>
                <Button variant="outline" @click="showDeleteDialog = false">Batal</Button>
                <Button class="bg-red-600 text-white hover:bg-red-700" @click="executeDelete">Hapus</Button>
            </template>
        </Modal>
    </AppLayout>
</template>
