<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Command, CommandGroup, CommandItem, CommandList } from '@/Components/ui/command';
import { ArrowLeft, ChevronsUpDown, Check } from 'lucide-vue-next';
import { ref } from 'vue';

const openDiscountType = ref(false);
const discountTypes = [
    { value: 'percentage', label: 'Persentase (%)' },
    { value: 'nominal', label: 'Nominal (Rp)' },
];

const props = defineProps({
    discount: Object,
});

const form = useForm({
    _method: 'PUT',
    name: props.discount.name,
    discount_type: props.discount.discount_type,
    discount_value: props.discount.discount_value,
    start_date: props.discount.start_date ? props.discount.start_date.split('T')[0] : '',
    end_date: props.discount.end_date ? props.discount.end_date.split('T')[0] : '',
    start_time: props.discount.start_time || '',
    end_time: props.discount.end_time || '',
    quota: props.discount.quota || '',
    is_active: props.discount.is_active,
});

const submit = () => {
    form.post(`/discounts/${props.discount.id}`);
};
</script>

<template>
    <AppLayout title="Edit Diskon">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between w-full">
                <div class="flex gap-5">
                    <Link href="/discounts" class="shrink-0 mt-0.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl border-[1.5px] border-slate-200 bg-white text-slate-500 shadow-[0_1px_2px_rgba(0,30,43,0.04)] transition-all hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                            <ArrowLeft class="h-4 w-4" />
                        </div>
                    </Link>
                    <div>
                        <h1 class="text-lg font-semibold text-[#001e2b]">Edit Master Diskon</h1>
                        <p class="text-xs text-[#7c8c9a]">Perbarui pengaturan promo diskon ini.</p>
                    </div>
                </div>
            </div>
        </template>

        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full pb-8">
            <div class="md:col-span-2 space-y-6">
                <!-- Informasi Utama -->
                <Card class="bg-white border-slate-200 shadow-sm rounded-xl">
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold text-slate-900">Informasi Diskon</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div class="space-y-2">
                            <Label for="name" class="text-sm font-medium text-slate-700">Nama Promo/Diskon <span class="text-red-500">*</span></Label>
                            <Input id="name" v-model="form.name" placeholder="Contoh: Promo Akhir Tahun" class="h-10 rounded-lg border-[1.5px] border-[#c1ccd6] px-4 shadow-[0_1px_2px_rgba(0,30,43,0.04)] focus-visible:border-[#00684a] focus-visible:ring-[3px] focus-visible:ring-[#00684a]/10" required />
                            <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-slate-700">Tipe Diskon <span class="text-red-500">*</span></Label>
                                <Popover v-model:open="openDiscountType">
                                    <PopoverTrigger as-child>
                                        <button
                                            type="button"
                                            role="combobox"
                                            :aria-expanded="openDiscountType"
                                            class="flex items-center justify-between h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-normal text-slate-900 shadow-sm outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[2px] focus:ring-[#00684a]/10"
                                        >
                                            <span class="truncate">{{ discountTypes.find(t => t.value === form.discount_type)?.label }}</span>
                                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-slate-400" />
                                        </button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-full sm:w-[280px] p-0 bg-white" align="start">
                                        <Command>
                                            <CommandList>
                                                <CommandGroup>
                                                    <CommandItem
                                                        v-for="t in discountTypes"
                                                        :key="t.value"
                                                        :value="t.label"
                                                        @select="() => {
                                                            form.discount_type = t.value;
                                                            openDiscountType = false;
                                                        }"
                                                        class="text-sm cursor-pointer"
                                                    >
                                                        {{ t.label }}
                                                        <Check
                                                            :class="['ml-auto h-4 w-4', form.discount_type === t.value ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                                        />
                                                    </CommandItem>
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                <p v-if="form.errors.discount_type" class="text-sm text-red-500">{{ form.errors.discount_type }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-slate-700">Nilai Diskon <span class="text-red-500">*</span></Label>
                                <Input v-model="form.discount_value" type="number" step="1" min="0" placeholder="0" class="h-10 rounded-lg border-[1.5px] border-[#c1ccd6] px-4 shadow-[0_1px_2px_rgba(0,30,43,0.04)] focus-visible:border-[#00684a] focus-visible:ring-[3px] focus-visible:ring-[#00684a]/10" required />
                                <p v-if="form.errors.discount_value" class="text-sm text-red-500">{{ form.errors.discount_value }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Syarat & Ketentuan -->
                <Card class="bg-white border-slate-200 shadow-sm rounded-xl">
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold text-slate-900">Syarat Berlaku</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-slate-700">Tanggal Mulai (Opsional)</Label>
                                <Input v-model="form.start_date" type="date" class="h-10 rounded-lg border-[1.5px] border-[#c1ccd6] px-4 shadow-[0_1px_2px_rgba(0,30,43,0.04)] focus-visible:border-[#00684a] focus-visible:ring-[3px] focus-visible:ring-[#00684a]/10" />
                                <p v-if="form.errors.start_date" class="text-sm text-red-500">{{ form.errors.start_date }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-slate-700">Tanggal Selesai (Opsional)</Label>
                                <Input v-model="form.end_date" type="date" class="h-10 rounded-lg border-[1.5px] border-[#c1ccd6] px-4 shadow-[0_1px_2px_rgba(0,30,43,0.04)] focus-visible:border-[#00684a] focus-visible:ring-[3px] focus-visible:ring-[#00684a]/10" />
                                <p v-if="form.errors.end_date" class="text-sm text-red-500">{{ form.errors.end_date }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-slate-700">Jam Mulai (Happy Hour)</Label>
                                <Input v-model="form.start_time" type="time" class="h-10 rounded-lg border-[1.5px] border-[#c1ccd6] px-4 shadow-[0_1px_2px_rgba(0,30,43,0.04)] focus-visible:border-[#00684a] focus-visible:ring-[3px] focus-visible:ring-[#00684a]/10" />
                                <p v-if="form.errors.start_time" class="text-sm text-red-500">{{ form.errors.start_time }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-slate-700">Jam Selesai (Happy Hour)</Label>
                                <Input v-model="form.end_time" type="time" class="h-10 rounded-lg border-[1.5px] border-[#c1ccd6] px-4 shadow-[0_1px_2px_rgba(0,30,43,0.04)] focus-visible:border-[#00684a] focus-visible:ring-[3px] focus-visible:ring-[#00684a]/10" />
                                <p v-if="form.errors.end_time" class="text-sm text-red-500">{{ form.errors.end_time }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-slate-700">Batas Penggunaan (Kuota)</Label>
                            <Input v-model="form.quota" type="number" min="1" placeholder="Kosongkan jika tanpa batas" class="h-10 rounded-lg border-[1.5px] border-[#c1ccd6] px-4 shadow-[0_1px_2px_rgba(0,30,43,0.04)] focus-visible:border-[#00684a] focus-visible:ring-[3px] focus-visible:ring-[#00684a]/10" />
                            <p class="text-xs text-slate-500 mt-1">Total diskon ini bisa dipakai berapa kali secara keseluruhan.</p>
                            <p v-if="form.errors.quota" class="text-sm text-red-500">{{ form.errors.quota }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Kolom Kanan: Status & Submit -->
            <div class="space-y-6">
                <!-- Status -->
                <Card class="bg-white border-slate-200 shadow-sm rounded-xl">
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold text-slate-900">Status</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div class="flex items-center gap-3 pt-2">
                            <input id="is_active" type="checkbox" v-model="form.is_active" class="h-4 w-4 rounded border-slate-300 text-[#00684a] focus:ring-[#00684a]" />
                            <Label for="is_active" class="text-sm font-medium text-slate-700">Diskon Aktif</Label>
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <div class="flex flex-col gap-3">
                    <Button type="submit" :disabled="form.processing" class="h-11 w-full rounded-xl bg-[#001e2b] text-white hover:bg-[#1c2d38] font-medium shadow-sm transition-colors">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </Button>
                    <Link href="/discounts" class="w-full">
                        <Button type="button" variant="outline" class="h-11 w-full rounded-xl font-medium border-slate-300">
                            Batal
                        </Button>
                    </Link>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
