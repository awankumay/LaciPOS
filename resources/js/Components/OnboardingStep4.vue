<script setup>
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Button } from '@/Components/ui/button';
import { Phone, ArrowRight, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    phone: { type: String, default: '' },
    receiptFooter: { type: String, default: 'Terima kasih sudah berkunjung!' },
});

const emit = defineEmits(['update:phone', 'update:receiptFooter', 'next', 'back']);
</script>

<template>
    <div class="space-y-6">
        <div class="text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                <Phone class="h-8 w-8 text-slate-700" />
            </div>
            <h2 class="text-xl font-semibold text-slate-900">Informasi Kontak</h2>
            <p class="mt-1 text-sm text-slate-500">
                Nomor telepon akan ditampilkan di struk.
            </p>
        </div>

        <div class="space-y-4">
            <div class="space-y-2">
                <Label for="phone">Nomor Telepon (opsional)</Label>
                <Input
                    id="phone"
                    :model-value="phone"
                    @update:model-value="$emit('update:phone', $event)"
                    type="tel"
                    placeholder="Contoh: 0812-3456-7890"
                    autofocus
                />
            </div>

            <div class="space-y-2">
                <Label for="receipt_footer">Teks Footer Struk (opsional)</Label>
                <Input
                    id="receipt_footer"
                    :model-value="receiptFooter"
                    @update:model-value="$emit('update:receiptFooter', $event)"
                    type="text"
                    placeholder="Contoh: Terima kasih sudah berkunjung!"
                />
                <p class="text-xs text-slate-400">Teks ini akan muncul di bagian bawah struk.</p>
            </div>
        </div>

        <div class="flex gap-3">
            <Button variant="outline" @click="$emit('back')" class="flex-1">
                <ArrowLeft class="mr-2 h-4 w-4" />
                Kembali
            </Button>
            <Button @click="$emit('next')" class="flex-1">
                Lihat Preview
                <ArrowRight class="ml-2 h-4 w-4" />
            </Button>
        </div>
    </div>
</template>
