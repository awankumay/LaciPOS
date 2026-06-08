<script setup>
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Button } from '@/Components/ui/button';
import { Store, ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    modelValue: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'next']);

const handleNext = () => {
    if (props.modelValue.trim()) {
        emit('next');
    }
};
</script>

<template>
    <div class="space-y-6">
        <div class="text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                <Store class="h-8 w-8 text-slate-700" />
            </div>
            <h2 class="text-xl font-semibold text-slate-900">Apa nama toko Anda?</h2>
            <p class="mt-1 text-sm text-slate-500">
                Nama ini akan muncul di header struk dan laporan.
            </p>
        </div>

        <div class="space-y-2">
            <Label for="store_name">Nama Toko</Label>
            <Input
                id="store_name"
                :model-value="modelValue"
                @update:model-value="$emit('update:modelValue', $event)"
                type="text"
                placeholder="Contoh: Warung Kopi Budi"
                autofocus
                class="text-center text-lg"
            />
        </div>

        <Button
            @click="handleNext"
            :disabled="!modelValue.trim()"
            class="w-full"
        >
            Lanjutkan
            <ArrowRight class="ml-2 h-4 w-4" />
        </Button>
    </div>
</template>
