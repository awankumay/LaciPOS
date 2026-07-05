<script setup>
import { ref } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent } from '@/Components/ui/card';
import { Plus, Trash2, GripVertical } from 'lucide-vue-next';

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const addVariant = () => {
    if (props.modelValue.length >= 3) return;
    const updated = [...props.modelValue, {
        name: '',
        options: [{ label: '', price_modifier: 0, cogs_modifier: 0 }],
    }];
    emit('update:modelValue', updated);
};

const removeVariant = (index) => {
    const updated = props.modelValue.filter((_, i) => i !== index);
    emit('update:modelValue', updated);
};

const addOption = (variantIndex) => {
    const updated = [...props.modelValue];
    updated[variantIndex] = {
        ...updated[variantIndex],
        options: [...updated[variantIndex].options, { label: '', price_modifier: 0, cogs_modifier: 0 }],
    };
    emit('update:modelValue', updated);
};

const removeOption = (variantIndex, optionIndex) => {
    const updated = [...props.modelValue];
    updated[variantIndex] = {
        ...updated[variantIndex],
        options: updated[variantIndex].options.filter((_, i) => i !== optionIndex),
    };
    emit('update:modelValue', updated);
};
</script>

<template>
    <div class="space-y-4">
        <div v-for="(variant, vIdx) in modelValue" :key="vIdx"
            class="rounded-lg border border-slate-200 p-4 space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex-1 mr-3">
                    <Label class="text-xs text-slate-500">Nama Varian {{ vIdx + 1 }}</Label>
                    <Input v-model="variant.name" placeholder='Contoh: Ukuran, Level Gula, Suhu' class="mt-1" />
                </div>
                <Button type="button" variant="ghost" size="sm" @click="removeVariant(vIdx)" class="text-red-500">
                    <Trash2 class="h-4 w-4" />
                </Button>
            </div>

            <!-- Options -->
            <div class="space-y-2 pl-4 border-l-2 border-slate-100">
                <div v-for="(option, oIdx) in variant.options" :key="oIdx" class="flex items-start gap-2">
                    <div class="flex-1">
                        <Label class="text-xs text-slate-500">Label Opsi</Label>
                        <Input v-model="option.label" placeholder="Contoh: Small, Medium, Large" class="mt-1" />
                    </div>
                    <div class="w-28">
                        <Label class="text-xs text-slate-500">+ Harga</Label>
                        <Input v-model="option.price_modifier" type="number" step="500" placeholder="0" class="mt-1" />
                    </div>
                    <div class="w-28">
                        <Label class="text-xs text-slate-500">+ Modal</Label>
                        <Input v-model="option.cogs_modifier" type="number" step="500" placeholder="0" class="mt-1" />
                    </div>
                    <Button type="button" v-if="variant.options.length > 1" variant="ghost" size="sm" class="mt-6"
                        @click="removeOption(vIdx, oIdx)">
                        <Trash2 class="h-3 w-3 text-slate-400" />
                    </Button>
                </div>
                <Button type="button" variant="outline" size="sm" @click="addOption(vIdx)">
                    <Plus class="mr-1 h-3 w-3" /> Tambah Opsi
                </Button>
                <p class="text-xs text-slate-400 mt-2">
                    Isi nilai tambahan harga dan modal untuk opsi ini. Nilai ini akan ditambahkan ke harga dan modal dasar produk saat pelanggan memilih opsi ini di kasir. Contoh: Jika harga produk Rp 10.000 dan opsi "Large" diberi + Rp 2.000, maka harga akhir menjadi Rp 12.000.
                </p>
            </div>
        </div>

        <Button type="button" v-if="modelValue.length < 3" variant="outline" @click="addVariant" class="w-full">
            <Plus class="mr-2 h-4 w-4" />
            Tambah Varian ({{ modelValue.length }}/3)
        </Button>
    </div>
</template>
