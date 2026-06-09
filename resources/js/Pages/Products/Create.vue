<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { ArrowLeft, Upload, X } from 'lucide-vue-next';
import { ref } from 'vue';
import VariantEditor from '@/Components/VariantEditor.vue';

const props = defineProps({
    categories: Array,
});

const form = useForm({
    name: '',
    category_id: '',
    photo: null,
    cogs: '',
    price: '',
    stock: 0,
    min_stock_alert: 5,
    is_active: true,
    variants: [],
});

const photoPreview = ref(null);
const fileInput = ref(null);

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
            <div class="flex items-center gap-4">
                <Link href="/products">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <h1 class="text-2xl font-semibold text-slate-900">Tambah Produk Baru</h1>
            </div>
        </template>

        <form @submit.prevent="submit" class="max-w-2xl space-y-6">
            <!-- Informasi Dasar -->
            <Card>
                <CardHeader><CardTitle>Informasi Produk</CardTitle></CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Nama Produk *</Label>
                        <Input id="name" v-model="form.name" placeholder="Contoh: Kopi Susu" />
                        <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="category_id">Kategori *</Label>
                        <select id="category_id" v-model="form.category_id"
                            class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm">
                            <option value="">Pilih kategori</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                        <p v-if="form.errors.category_id" class="text-sm text-red-500">{{ form.errors.category_id }}</p>
                    </div>

                    <!-- Foto -->
                    <div class="space-y-2">
                        <Label>Foto Produk (opsional)</Label>
                        <div class="flex items-center gap-4">
                            <div v-if="photoPreview" class="relative">
                                <img :src="photoPreview" class="h-24 w-24 rounded-lg object-cover border" />
                                <button @click="removePhoto" type="button"
                                    class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white">
                                    <X class="h-3 w-3" />
                                </button>
                            </div>
                            <div @click="fileInput?.click()"
                                class="flex h-24 w-24 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 hover:border-slate-400">
                                <Upload class="h-5 w-5 text-slate-400" />
                                <span class="text-xs text-slate-400 mt-1">Maks 1MB</span>
                            </div>
                            <input ref="fileInput" type="file" accept="image/png,image/jpeg" class="hidden" @change="handlePhotoChange" />
                        </div>
                        <p v-if="form.errors.photo" class="text-sm text-red-500">{{ form.errors.photo }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Harga -->
            <Card>
                <CardHeader><CardTitle>Harga</CardTitle></CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="cogs">Harga Modal (COGS) *</Label>
                            <Input id="cogs" v-model="form.cogs" type="number" step="100" min="0" placeholder="0" />
                            <p v-if="form.errors.cogs" class="text-sm text-red-500">{{ form.errors.cogs }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="price">Harga Jual *</Label>
                            <Input id="price" v-model="form.price" type="number" step="100" min="0" placeholder="0" />
                            <p v-if="form.errors.price" class="text-sm text-red-500">{{ form.errors.price }}</p>
                        </div>
                    </div>
                    <p v-if="form.cogs && form.price && Number(form.price) > Number(form.cogs)"
                        class="text-sm text-green-600">
                        Margin: {{ Math.round(((Number(form.price) - Number(form.cogs)) / Number(form.cogs)) * 100) }}%
                    </p>
                </CardContent>
            </Card>

            <!-- Stok -->
            <Card>
                <CardHeader><CardTitle>Stok</CardTitle></CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="stock">Stok Awal *</Label>
                            <Input id="stock" v-model="form.stock" type="number" min="0" placeholder="0" />
                            <p v-if="form.errors.stock" class="text-sm text-red-500">{{ form.errors.stock }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="min_stock_alert">Minimum Stok Alert</Label>
                            <Input id="min_stock_alert" v-model="form.min_stock_alert" type="number" min="0" placeholder="5" />
                            <p class="text-xs text-slate-400">Notifikasi muncul saat stok ≤ nilai ini</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Variants -->
            <Card>
                <CardHeader><CardTitle>Varian Produk (Opsional)</CardTitle></CardHeader>
                <CardContent>
                    <VariantEditor v-model="form.variants" />
                </CardContent>
            </Card>

            <!-- Submit -->
            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Produk' }}
                </Button>
                <Link href="/products">
                    <Button type="button" variant="outline">Batal</Button>
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
