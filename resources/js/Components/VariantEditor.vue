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

const updateVariantName = (index, name) => {
    const updated = [...props.modelValue];
    updated[index] = { ...updated[index], name };
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

const updateOption = (variantIndex, optionIndex, field, value) => {
    const updated = [...props.modelValue];
    updated[variantIndex] = {
        ...updated[variantIndex],
        options: updated[variantIndex].options.map((opt, i) =>
            i === optionIndex ? { ...opt, [field]: value } : opt
        ),
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
                    <Input :value="variant.name" @input="updateVariantName(vIdx, $event.target.value)"
                        placeholder='Contoh: Ukuran, Level Gula, Suhu' class="mt-1" />
                </div>
                <Button variant="ghost" size="sm" @click="removeVariant(vIdx)" class="text-red-500">
                    <Trash2 class="h-4 w-4" />
                </Button>
            </div>

            <!-- Options -->
            <div class="space-y-2 pl-4 border-l-2 border-slate-100">
                <div v-for="(option, oIdx) in variant.options" :key="oIdx" class="flex items-center gap-2">
                    <Input :value="option.label" @input="updateOption(vIdx, oIdx, 'label', $event.target.value)"
                        placeholder="Label opsi" class="flex-1" />
                    <div class="w-32">
                        <Input :value="option.price_modifier" @input="updateOption(vIdx, oIdx, 'price_modifier', Number($event.target.value))"
                            type="number" step="500" placeholder="± Harga" />
                    </div>
                    <Button v-if="variant.options.length > 1" variant="ghost" size="sm"
                        @click="removeOption(vIdx, oIdx)">
                        <Trash2 class="h-3 w-3 text-slate-400" />
                    </Button>
                </div>
                <Button variant="outline" size="sm" @click="addOption(vIdx)">
                    <Plus class="mr-1 h-3 w-3" /> Tambah Opsi
                </Button>
            </div>
        </div>

        <Button v-if="modelValue.length < 3" variant="outline" @click="addVariant" class="w-full" type="button">
            <Plus class="mr-2 h-4 w-4" />
            Tambah Varian ({{ modelValue.length }}/3)
        </Button>
    </div>
</template>
