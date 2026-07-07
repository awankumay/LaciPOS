<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router, Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Badge } from '@/Components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import Modal from '@/Components/Modal.vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/Components/ui/command';
import { Plus, Search, Package, Trash2, ArrowRightLeft, Check, ChevronsUpDown } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useFormatCurrency } from '@/composables/useFormatCurrency';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
});

const { formatRupiah } = useFormatCurrency();
const search = ref(props.filters.search);
const selectedCategory = ref(props.filters.category);
const openCategoryBox = ref(false);

// Debounced search
let searchTimeout;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

watch(selectedCategory, () => applyFilters());

const applyFilters = () => {
    router.get('/products', {
        search: search.value || undefined,
        category: selectedCategory.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

// Delete
const showDeleteDialog = ref(false);
const deletingProduct = ref(null);

const confirmDelete = (product) => {
    deletingProduct.value = product;
    showDeleteDialog.value = true;
};

const executeDelete = () => {
    router.delete(`/products/${deletingProduct.value.id}`, {
        onFinish: () => {
            showDeleteDialog.value = false;
            deletingProduct.value = null;
        },
    });
};
</script>

<template>
    <AppLayout title="Produk">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between w-full">
                <div>
                    <h1 class="text-lg font-semibold text-[#001e2b]">Produk</h1>
                    <p class="text-xs text-[#7c8c9a]">Kelola daftar produk, harga, dan stok</p>
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
                        placeholder="Cari produk..." 
                        class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pr-4 pl-11 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all placeholder:text-[#a8b3bc] focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10" 
                    />
                </div>
                <Popover v-model:open="openCategoryBox">
                    <PopoverTrigger as-child>
                        <button
                            role="combobox"
                            :aria-expanded="openCategoryBox"
                            class="flex items-center justify-between h-10 w-full sm:w-[220px] rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pl-4 pr-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10"
                        >
                            <span class="truncate">{{ selectedCategory ? categories.find(cat => cat.id === selectedCategory)?.name : 'Semua Kategori' }}</span>
                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-[#7c8c9a]" />
                        </button>
                    </PopoverTrigger>
                    <PopoverContent class="w-[220px] p-0 bg-white" align="start">
                        <Command>
                            <CommandInput class="h-10 text-sm" placeholder="Cari kategori..." />
                            <CommandEmpty class="py-3 text-sm text-center text-slate-500">Kategori tidak ditemukan.</CommandEmpty>
                            <CommandList>
                                <CommandGroup>
                                    <CommandItem
                                        value="Semua Kategori"
                                        @select="() => {
                                            selectedCategory = '';
                                            openCategoryBox = false;
                                        }"
                                        class="text-sm cursor-pointer"
                                    >
                                        Semua Kategori
                                        <Check
                                            :class="['ml-auto h-4 w-4', selectedCategory === '' ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                        />
                                    </CommandItem>
                                    <CommandItem
                                        v-for="cat in categories"
                                        :key="cat.id"
                                        :value="cat.name"
                                        @select="() => {
                                            selectedCategory = cat.id;
                                            openCategoryBox = false;
                                        }"
                                        class="text-sm cursor-pointer"
                                    >
                                        {{ cat.name }}
                                        <Check
                                            :class="['ml-auto h-4 w-4', selectedCategory === cat.id ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                        />
                                    </CommandItem>
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </PopoverContent>
                </Popover>
            </div>
            <Link href="/products/create" class="shrink-0 w-full sm:w-auto">
                <Button class="h-10 rounded-xl w-full sm:w-auto bg-[#001e2b] text-white hover:bg-[#1c2d38]">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Produk
                </Button>
            </Link>
        </div>

        <!-- Products Table -->
        <div class="rounded-lg border border-slate-200 bg-white">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-[60px]">Foto</TableHead>
                        <TableHead>Nama Produk</TableHead>
                        <TableHead>Kategori</TableHead>
                        <TableHead class="text-right">Harga Jual</TableHead>
                        <TableHead class="text-right">Harga Modal</TableHead>
                        <TableHead class="text-center">Stok</TableHead>
                        <TableHead class="text-center">Status</TableHead>
                        <TableHead class="text-right w-[100px]">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="product in products.data" :key="product.id">
                        <TableCell>
                            <div class="h-10 w-10 rounded-md bg-slate-100 flex items-center justify-center overflow-hidden">
                                <img v-if="product.photo_url" :src="product.photo_url" :alt="product.name" class="h-full w-full object-cover" />
                                <Package v-else class="h-5 w-5 text-slate-300" />
                            </div>
                        </TableCell>
                        <TableCell class="font-medium max-w-[250px] truncate" :title="product.name">
                            {{ product.name }}
                            <Badge v-if="product.variants_count > 0" variant="outline" class="ml-2 shrink-0">
                                {{ product.variants_count }} varian
                            </Badge>
                        </TableCell>
                        <TableCell>{{ product.category?.name }}</TableCell>
                        <TableCell class="text-right">{{ formatRupiah(product.price) }}</TableCell>
                        <TableCell class="text-right text-slate-500">{{ formatRupiah(product.cogs) }}</TableCell>
                        <TableCell class="text-center">
                            <Badge :variant="product.stock <= product.min_stock_alert ? 'destructive' : 'secondary'">
                                {{ product.stock }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-center">
                            <span :class="[
                                'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium',
                                product.is_active ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600'
                            ]">
                                {{ product.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="`/products/${product.id}/stock`" title="Penyesuaian Stok">
                                    <Button variant="ghost" size="sm" class="text-blue-600">
                                        <ArrowRightLeft class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <Link :href="`/products/${product.id}/edit`">
                                    <Button variant="ghost" size="sm">Edit</Button>
                                </Link>
                                <Button variant="ghost" size="sm" @click="confirmDelete(product)">
                                    <Trash2 class="h-4 w-4 text-red-500" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="products.data.length === 0">
                        <TableCell colspan="8" class="text-center text-slate-400 py-8">
                            Belum ada produk. Klik "Tambah Produk" untuk memulai.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div v-if="products.last_page > 1" class="mt-4 flex items-center justify-center gap-2">
            <template v-for="link in products.links" :key="link.label">
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

        <!-- Delete Confirmation Dialog -->
        <Modal
            :show="showDeleteDialog"
            @update:show="showDeleteDialog = $event"
            title="Hapus Produk"
        >
            <p class="text-sm text-slate-600 break-all">
                Apakah Anda yakin ingin menghapus produk <strong class="break-all">{{ deletingProduct?.name }}</strong>? Data produk ini tidak akan ditampilkan lagi, namun riwayat penjualan tetap tersimpan.
            </p>
            <template #footer>
                <Button variant="outline" @click="showDeleteDialog = false">Batal</Button>
                <Button variant="destructive" @click="executeDelete" class="bg-red-600 text-white">Hapus</Button>
            </template>
        </Modal>
    </AppLayout>
</template>
