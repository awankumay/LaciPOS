<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/Components/ui/command';
import { ArrowLeft, Upload, X, Check, ChevronsUpDown } from 'lucide-vue-next';
import { ref } from 'vue';
import VariantEditor from '@/Components/VariantEditor.vue';

const props = defineProps({
    categories: Array,
    discounts: Array,
    defaultMinStock: { type: Number, default: 5 },
});

const form = useForm({
    name: '',
    category_id: '',
    photo: null,
    cogs: '',
    price: '',
    stock: 0,
    min_stock_alert: props.defaultMinStock,
    is_active: true,
    variants: [],
    discount_id: '',
});

const photoPreview = ref(null);
const fileInput = ref(null);
const openCategoryBox = ref(false);

const handlePhotoChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    form.photo = file;
    photoPreview.value = URL.createObjectURL(file);
};

const removePhoto = () => {
    form.photo = null;
    photoPreview.value = null;
    if (fileInput.value) fileInput.value.value = '';
};

const submit = () => {
    form.post('/products', {
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout title="Tambah Produk">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between w-full">
                <div class="flex gap-5">
                    <Link href="/products" class="shrink-0 mt-0.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl border-[1.5px] border-slate-200 bg-white text-slate-500 shadow-[0_1px_2px_rgba(0,30,43,0.04)] transition-all hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                            <ArrowLeft class="h-4 w-4" />
                        </div>
                    </Link>
                    <div>
                        <h1 class="text-lg font-semibold text-[#001e2b]">Tambah Produk</h1>
                        <p class="text-xs text-[#7c8c9a]">Tambahkan produk baru ke dalam katalog sistem kasir Anda.</p>
                    </div>
                </div>
            </div>
        </template>

        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full pb-8">
            <!-- Kolom Kiri: Info & Varian -->
            <div class="md:col-span-2 space-y-6">
                <!-- Informasi Produk -->
                <Card class="bg-white border-slate-200 shadow-sm rounded-xl">
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold text-slate-900">Informasi Produk</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div class="space-y-2">
                            <Label for="name" class="text-sm font-medium text-slate-700">Nama Produk <span class="text-red-500">*</span></Label>
                            <Input id="name" v-model="form.name" placeholder="Contoh: Kopi Susu Aren" class="h-10 rounded-lg" />
                            <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-slate-700">Kategori <span class="text-red-500">*</span></Label>
                            <Popover v-model:open="openCategoryBox">
                                <PopoverTrigger as-child>
                                    <button
                                        type="button"
                                        role="combobox"
                                        :aria-expanded="openCategoryBox"
                                        class="flex items-center justify-between h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-900 shadow-sm outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[2px] focus:ring-[#00684a]/10"
                                    >
                                        <span class="truncate">{{ form.category_id ? categories.find(cat => cat.id === form.category_id)?.name : 'Pilih kategori...' }}</span>
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-slate-500" />
                                    </button>
                                </PopoverTrigger>
                                <PopoverContent class="w-[300px] sm:w-[400px] p-0 bg-white" align="start">
                                    <Command>
                                        <CommandInput class="h-10 text-sm" placeholder="Cari kategori..." />
                                        <CommandEmpty class="py-3 text-sm text-center text-slate-500">Kategori tidak ditemukan.</CommandEmpty>
                                        <CommandList>
                                            <CommandGroup>
                                                <CommandItem
                                                    v-for="cat in categories"
                                                    :key="cat.id"
                                                    :value="cat.name"
                                                    @select="() => {
                                                        form.category_id = cat.id;
                                                        openCategoryBox = false;
                                                    }"
                                                    class="text-sm cursor-pointer"
                                                >
                                                    {{ cat.name }}
                                                    <Check
                                                        :class="['ml-auto h-4 w-4', form.category_id === cat.id ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                                    />
                                                </CommandItem>
                                            </CommandGroup>
                                        </CommandList>
                                    </Command>
                                </PopoverContent>
                            </Popover>
                            <p v-if="form.errors.category_id" class="text-sm text-red-500">{{ form.errors.category_id }}</p>
                        </div>

                        <!-- Foto -->
                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-slate-700">Foto Produk (opsional)</Label>
                            <div class="flex items-center gap-4">
                                <div v-if="photoPreview" class="relative">
                                    <img :src="photoPreview" class="h-24 w-24 rounded-lg object-cover border border-slate-200" />
                                    <button @click="removePhoto" type="button"
                                        class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white shadow-sm hover:bg-red-600 transition-colors">
                                        <X class="h-3 w-3" />
                                    </button>
                                </div>
                                <div @click="fileInput?.click()"
                                    class="flex h-24 w-24 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 hover:bg-slate-100 hover:border-slate-400 transition-colors">
                                    <Upload class="h-5 w-5 text-slate-400" />
                                    <span class="text-xs text-slate-500 mt-1">Maks 1MB</span>
                                </div>
                                <input ref="fileInput" type="file" accept="image/png,image/jpeg" class="hidden" @change="handlePhotoChange" />
                            </div>
                            <p v-if="form.errors.photo" class="text-sm text-red-500">{{ form.errors.photo }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Variants -->
                <Card class="bg-white border-slate-200 shadow-sm rounded-xl">
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold text-slate-900">Varian Produk (Opsional)</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <VariantEditor v-model="form.variants" />
                    </CardContent>
                </Card>

                <!-- Pengaturan Diskon -->
                <Card class="bg-white border-slate-200 shadow-sm rounded-xl">
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold text-slate-900">Pengaturan Diskon</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-slate-700">Pilih Master Diskon</Label>
                            <select v-model="form.discount_id" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-900 shadow-sm outline-none transition-all focus:border-[#00684a] focus:ring-[2px] focus:ring-[#00684a]/10">
                                <option value="">Tidak ada diskon</option>
                                <option v-for="discount in discounts" :key="discount.id" :value="discount.id">
                                    {{ discount.name }} ({{ discount.discount_type === 'percentage' ? discount.discount_value + '%' : 'Rp ' + discount.discount_value }})
                                </option>
                            </select>
                            <p class="text-xs text-slate-500 mt-1">Diskon akan diterapkan pada produk ini saat checkout.</p>
                            <p v-if="form.errors.discount_id" class="text-sm text-red-500">{{ form.errors.discount_id }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Kolom Kanan: Harga, Stok, Submit -->
            <div class="space-y-6">
                <!-- Harga -->
                <Card class="bg-white border-slate-200 shadow-sm rounded-xl">
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold text-slate-900">Harga</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="cogs" class="text-sm font-medium text-slate-700">Harga Modal (COGS) <span class="text-red-500">*</span></Label>
                            <Input id="cogs" v-model="form.cogs" type="number" step="100" min="0" placeholder="0" class="h-10 rounded-lg" />
                            <p v-if="form.errors.cogs" class="text-sm text-red-500">{{ form.errors.cogs }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="price" class="text-sm font-medium text-slate-700">Harga Jual <span class="text-red-500">*</span></Label>
                            <Input id="price" v-model="form.price" type="number" step="100" min="0" placeholder="0" class="h-10 rounded-lg" />
                            <p v-if="form.errors.price" class="text-sm text-red-500">{{ form.errors.price }}</p>
                        </div>
                        <div v-if="form.cogs && form.price && Number(form.price) > Number(form.cogs)"
                            class="p-3 bg-green-50 rounded-lg border border-green-100 flex items-center justify-between">
                            <span class="text-sm text-green-700 font-medium">Estimasi Margin:</span>
                            <span class="text-sm text-green-700 font-bold">{{ Math.round(((Number(form.price) - Number(form.cogs)) / Number(form.cogs)) * 100) }}%</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Stok -->
                <Card class="bg-white border-slate-200 shadow-sm rounded-xl">
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold text-slate-900">Stok</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="stock" class="text-sm font-medium text-slate-700">Stok Awal <span class="text-red-500">*</span></Label>
                            <Input id="stock" v-model="form.stock" type="number" min="0" placeholder="0" class="h-10 rounded-lg" />
                            <p v-if="form.errors.stock" class="text-sm text-red-500">{{ form.errors.stock }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="min_stock_alert" class="text-sm font-medium text-slate-700">Minimum Stok Alert</Label>
                            <Input id="min_stock_alert" v-model="form.min_stock_alert" type="number" min="0" placeholder="5" class="h-10 rounded-lg" />
                            <p class="text-xs text-slate-500 mt-1">Sistem akan memberi tahu jika stok kurang dari ini.</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <div class="flex flex-col gap-3">
                    <Button type="submit" :disabled="form.processing" class="h-11 w-full rounded-xl bg-[#001e2b] text-white hover:bg-[#1c2d38] font-medium shadow-sm transition-colors">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Produk' }}
                    </Button>
                    <Link href="/products" class="w-full">
                        <Button type="button" variant="outline" class="h-11 w-full rounded-xl font-medium border-slate-300">
                            Batal
                        </Button>
                    </Link>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
