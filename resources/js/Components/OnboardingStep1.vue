<script setup>
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
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
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#e3fcef]">
                <Store class="h-6 w-6 text-[#00684a]" />
            </div>
            <div>
                <h2 class="text-lg font-semibold text-[#001e2b]">Apa nama toko Anda?</h2>
                <p class="mt-1 text-sm text-[#5c6c7a]">
                    Nama ini akan muncul di header struk dan laporan.
                </p>
            </div>
        </div>

        <!-- Field -->
        <div class="space-y-2">
            <Label for="store_name" class="text-sm font-medium text-[#1c2d38]">Nama Toko</Label>
            <Input
                id="store_name"
                :model-value="modelValue"
                @update:model-value="$emit('update:modelValue', $event)"
                type="text"
                placeholder="Contoh: Warung Kopi Budi"
                autofocus
                class="h-11 text-base"
            />
        </div>

        <!-- CTA -->
        <button
            @click="handleNext"
            :disabled="!modelValue.trim()"
            :class="[
                'flex w-full items-center justify-center gap-2 rounded-full py-3 text-sm font-semibold transition-all duration-150',
                modelValue.trim()
                    ? 'bg-[#00ed64] text-[#001e2b] hover:bg-[#00b545] active:bg-[#008c34] cursor-pointer'
                    : 'bg-[#e1e5e8] text-[#a8b3bc] cursor-not-allowed',
            ]"
        >
            Lanjutkan
            <ArrowRight class="h-4 w-4" />
        </button>
    </div>
</template>
