<script setup>
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Switch } from '@/Components/ui/switch';

const props = defineProps({
    defaultMinStock: { type: Number, default: 5 },
});

const form = useForm({
    default_min_stock: props.defaultMinStock,
    apply_to_all: false,
});

const submit = () => {
    form.post('/settings/inventory', {
        preserveScroll: true,
        onSuccess: () => {
            form.apply_to_all = false;
        }
    });
};
</script>

<template>
    <SettingsLayout title="Pengaturan Stok">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold tracking-tight text-[#001e2b]">Pengaturan Stok</h2>
        </div>

        <div class="max-w-2xl mt-4">
            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>Stok Minimum Global</CardTitle>
                        <CardDescription>
                            Atur nilai default untuk peringatan stok menipis. Nilai ini akan otomatis digunakan saat Anda menambahkan produk baru.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="space-y-2">
                            <Label for="default_min_stock">Minimum Stok Alert</Label>
                            <Input
                                id="default_min_stock"
                                type="number"
                                v-model="form.default_min_stock"
                                min="0"
                                required
                            />
                            <p class="text-sm text-slate-500">Nilai ini digunakan sebagai default saat menambah produk baru.</p>
                            <p v-if="form.errors.default_min_stock" class="text-sm text-red-500">{{ form.errors.default_min_stock }}</p>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end">
                        <Button type="submit" :disabled="form.processing" class="bg-[#001e2b] text-white hover:bg-[#1c2d38]">Simpan Pengaturan</Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </SettingsLayout>
</template>
