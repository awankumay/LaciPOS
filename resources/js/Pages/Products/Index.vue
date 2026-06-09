<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router, Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Badge } from '@/Components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Plus, Search, Package } from 'lucide-vue-next';
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
</script>

<template>
    <AppLayout title="Produk">
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-slate-900">Produk</h1>
                <Link href="/products/create">
                    <Button size="sm">
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Produk
                    </Button>
                </Link>
            </div>
        </template>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                <Input v-model="search" placeholder="Cari produk..." class="pl-10" />
            </div>
            <select
                v-model="selectedCategory"
                class="rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-400"
            >
                <option value="">Semua Kategori</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                </option>
            </select>
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
                        <TableCell class="font-medium">
                            {{ product.name }}
                            <Badge v-if="product.variants_count > 0" variant="outline" class="ml-2">
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
                            <Badge :variant="product.is_active ? 'default' : 'outline'">
                                {{ product.is_active ? 'Aktif' : 'Nonaktif' }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right">
                            <Link :href="`/products/${product.id}/edit`">
                                <Button variant="ghost" size="sm">Edit</Button>
                            </Link>
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
    </AppLayout>
</template>
