<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Badge } from '@/Components/ui/badge';
import { ArrowLeft, PackagePlus, PackageMinus } from 'lucide-vue-next';

const props = defineProps({
    product: Object,
    logs: Array,
});

const form = useForm({
    type: 'add',
    quantity: 1,
    notes: '',
});

const submit = () => {
    form.post(`/products/${props.product.id}/stock`, {
        preserveScroll: true,
        onSuccess: () => form.reset('quantity', 'notes'),
    });
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short'
    }).format(date);
};

const getReasonLabel = (reason) => {
    const reasons = {
        'sale': 'Penjualan',
        'manual_add': 'Tambah Manual',
        'manual_reduce': 'Kurangi Manual',
        'adjustment': 'Penyesuaian',
        'cancellation': 'Pembatalan Order',
    };
    return reasons[reason] || reason;
};
</script>

<template>
    <AppLayout title="Penyesuaian Stok">
        <template #header>
            <div class="flex items-center gap-4">
                <Link href="/products">
                    <Button variant="outline" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Penyesuaian Stok</h1>
                    <p class="text-sm text-slate-500">{{ product.name }}</p>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Form Adjustment -->
            <div class="md:col-span-1 space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Stok Saat Ini</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-4xl font-bold text-slate-900">
                            {{ product.stock }}
                        </div>
                        <div v-if="product.stock <= product.min_stock_alert" class="mt-2">
                            <Badge variant="destructive">Stok Menipis</Badge>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Mutasi Stok</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <Label>Jenis Penyesuaian</Label>
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <button
                                        type="button"
                                        @click="form.type = 'add'"
                                        class="flex items-center justify-center gap-2 rounded-md border p-3 text-sm font-medium transition-colors"
                                        :class="form.type === 'add' ? 'border-green-600 bg-green-50 text-green-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                                    >
                                        <PackagePlus class="h-4 w-4" />
                                        Tambah
                                    </button>
                                    <button
                                        type="button"
                                        @click="form.type = 'reduce'"
                                        class="flex items-center justify-center gap-2 rounded-md border p-3 text-sm font-medium transition-colors"
                                        :class="form.type === 'reduce' ? 'border-red-600 bg-red-50 text-red-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                                    >
                                        <PackageMinus class="h-4 w-4" />
                                        Kurangi
                                    </button>
                                </div>
                                <p v-if="form.errors.type" class="text-sm text-red-500 mt-1">{{ form.errors.type }}</p>
                            </div>

                            <div>
                                <Label for="quantity">Jumlah</Label>
                                <Input id="quantity" type="number" v-model="form.quantity" min="1" class="mt-1" />
                                <p v-if="form.errors.quantity" class="text-sm text-red-500 mt-1">{{ form.errors.quantity }}</p>
                            </div>

                            <div>
                                <Label for="notes">Catatan (Opsional)</Label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    class="mt-1 w-full rounded-md border border-slate-200 p-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-400"
                                    placeholder="Contoh: Barang retur, rusak, dll"
                                ></textarea>
                                <p v-if="form.errors.notes" class="text-sm text-red-500 mt-1">{{ form.errors.notes }}</p>
                            </div>

                            <Button type="submit" class="w-full" :disabled="form.processing">
                                Simpan Mutasi
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <!-- History Log -->
            <div class="md:col-span-2">
                <Card class="h-full">
                    <CardHeader>
                        <CardTitle>Riwayat Stok</CardTitle>
                        <CardDescription>50 catatan terakhir perubahan stok</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="rounded-md border border-slate-200">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Waktu</TableHead>
                                        <TableHead>Jenis</TableHead>
                                        <TableHead class="text-right">Perubahan</TableHead>
                                        <TableHead>Catatan</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="log in logs" :key="log.id">
                                        <TableCell class="text-sm whitespace-nowrap text-slate-500">
                                            {{ formatDate(log.created_at) }}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline" class="font-normal">
                                                {{ getReasonLabel(log.reason) }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-right font-medium" :class="log.change > 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ log.change > 0 ? '+' : '' }}{{ log.change }}
                                        </TableCell>
                                        <TableCell class="text-sm text-slate-600 max-w-[200px] truncate" :title="log.notes">
                                            {{ log.notes || '-' }}
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="logs.length === 0">
                                        <TableCell colspan="4" class="text-center py-8 text-slate-400">
                                            Belum ada riwayat stok
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
