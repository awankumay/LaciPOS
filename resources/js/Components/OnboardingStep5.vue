<script setup>
import { Button } from '@/Components/ui/button';
import { Separator } from '@/Components/ui/separator';
import { ArrowLeft, Check, Printer } from 'lucide-vue-next';

const props = defineProps({
    storeName: { type: String, required: true },
    address: { type: String, default: '' },
    phone: { type: String, default: '' },
    logoPreviewUrl: { type: String, default: null },
    receiptFooter: { type: String, default: '' },
    isSubmitting: { type: Boolean, default: false },
});

const emit = defineEmits(['back', 'submit']);
</script>

<template>
    <div class="space-y-6">
        <div class="text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                <Printer class="h-8 w-8 text-slate-700" />
            </div>
            <h2 class="text-xl font-semibold text-slate-900">Preview Struk</h2>
            <p class="mt-1 text-sm text-slate-500">
                Ini adalah tampilan struk toko Anda.
            </p>
        </div>

        <!-- Receipt Preview -->
        <div class="mx-auto max-w-[280px] rounded-lg border border-slate-200 bg-white p-4 font-mono text-xs leading-relaxed shadow-sm">
            <!-- Header -->
            <div class="text-center space-y-1">
                <div class="text-base font-bold">================================</div>
                <img
                    v-if="logoPreviewUrl"
                    :src="logoPreviewUrl"
                    alt="Logo"
                    class="mx-auto h-12 w-12 object-contain"
                />
                <div class="font-bold text-sm">{{ storeName || 'Nama Toko' }}</div>
                <div v-if="address">{{ address }}</div>
                <div v-if="phone">Telp: {{ phone }}</div>
                <div class="text-base font-bold">================================</div>
            </div>

            <!-- Sample Transaction -->
            <div class="mt-2 space-y-1">
                <div>No: TRX-20260610-001</div>
                <div>Kasir: Sari</div>
                <div>Tgl: 10 Jun 2026, 14:32</div>
                <Separator class="my-2" />
                <div class="flex justify-between">
                    <span>Kopi Susu (M, Less) x1</span>
                </div>
                <div class="text-right">Rp 28.000</div>
                <div class="flex justify-between">
                    <span>Es Teh x2</span>
                </div>
                <div class="text-right">Rp 14.000</div>
                <Separator class="my-2" />
                <div class="flex justify-between font-bold">
                    <span>Subtotal:</span>
                    <span>Rp 42.000</span>
                </div>
                <div class="flex justify-between">
                    <span>Bayar (Tunai):</span>
                    <span>Rp 50.000</span>
                </div>
                <div class="flex justify-between">
                    <span>Kembalian:</span>
                    <span>Rp 8.000</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-2 text-center">
                <div class="text-base font-bold">================================</div>
                <div v-if="receiptFooter" class="mt-1">{{ receiptFooter }}</div>
                <div class="text-base font-bold">================================</div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex gap-3">
            <Button variant="outline" @click="$emit('back')" class="flex-1" :disabled="isSubmitting">
                <ArrowLeft class="mr-2 h-4 w-4" />
                Kembali
            </Button>
            <Button @click="$emit('submit')" class="flex-1" :disabled="isSubmitting">
                <span v-if="isSubmitting">Menyimpan...</span>
                <template v-else>
                    <Check class="mr-2 h-4 w-4" />
                    Selesaikan Setup
                </template>
            </Button>
        </div>
    </div>
</template>
