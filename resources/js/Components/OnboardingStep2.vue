<script setup>
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Button } from '@/Components/ui/button';
import { MapPin, ArrowRight, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    modelValue: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'next', 'back']);
</script>

<template>
    <div class="space-y-6">
        <div class="text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                <MapPin class="h-8 w-8 text-slate-700" />
            </div>
            <h2 class="text-xl font-semibold text-slate-900">Di mana lokasi toko Anda?</h2>
            <p class="mt-1 text-sm text-slate-500">
                Alamat ini akan muncul di struk. Bisa dilewati jika belum ada.
            </p>
        </div>

        <div class="space-y-2">
            <Label for="address">Alamat Toko (opsional)</Label>
            <Input
                id="address"
                :model-value="modelValue"
                @update:model-value="$emit('update:modelValue', $event)"
                type="text"
                placeholder="Contoh: Jl. Merdeka No. 123, Jakarta"
                autofocus
            />
        </div>

        <div class="flex gap-3">
            <Button variant="outline" @click="$emit('back')" class="flex-1">
                <ArrowLeft class="mr-2 h-4 w-4" />
                Kembali
            </Button>
            <Button @click="$emit('next')" class="flex-1">
                Lanjutkan
                <ArrowRight class="ml-2 h-4 w-4" />
            </Button>
        </div>
    </div>
</template>
