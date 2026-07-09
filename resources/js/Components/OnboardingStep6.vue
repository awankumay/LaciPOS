<script setup>
import { Separator } from '@/Components/ui/separator';
import { ArrowLeft, Check, Printer, Loader2 } from 'lucide-vue-next';

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
    <div class="space-y-8">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#e3fcef]">
                <Printer class="h-6 w-6 text-[#00684a]" />
            </div>
            <div>
                <h2 class="text-lg font-semibold text-[#001e2b]">Konfirmasi & Selesai</h2>
                <p class="mt-1 text-sm text-[#5c6c7a]">
                    Pastikan data toko sudah benar, lalu selesaikan setup.
                </p>
            </div>
        </div>

        <div class="mx-auto w-64 rounded-2xl border border-[#e1e5e8] bg-white p-5 shadow-[0_4px_12px_rgba(0,30,43,0.08)]">
            <div class="text-center font-mono text-xs leading-relaxed">
                <div class="text-[#a8b3bc]">― ― ― ― ― ― ― ― ― ―</div>
                <img
                    v-if="logoPreviewUrl"
                    :src="logoPreviewUrl"
                    alt="Logo"
                    class="mx-auto my-2 h-12 w-12 object-contain"
                />
                <div class="mt-1 text-sm font-bold text-[#001e2b]">{{ storeName || 'Nama Toko' }}</div>
                <div v-if="address" class="text-[#5c6c7a]">{{ address }}</div>
                <div v-if="phone" class="text-[#5c6c7a]">Telp: {{ phone }}</div>
                <div class="mt-1 text-[#a8b3bc]">― ― ― ― ― ― ― ― ― ―</div>
            </div>

            <div class="mt-3 font-mono text-xs space-y-1 text-[#3d4f5b]">
                <div>No: TRX-20260610-001</div>
                <div>Kasir: Sari</div>
                <div>Tgl: 10 Jun 2026, 14:32</div>
                <Separator class="my-2" />
                <div class="flex justify-between">
                    <span>Kopi Susu x1</span>
                    <span>28.000</span>
                </div>
                <div class="flex justify-between">
                    <span>Es Teh x2</span>
                    <span>14.000</span>
                </div>
                <Separator class="my-2" />
                <div class="flex justify-between font-bold text-[#001e2b]">
                    <span>Total:</span>
                    <span>Rp 42.000</span>
                </div>
                <div class="flex justify-between text-[#5c6c7a]">
                    <span>Bayar:</span>
                    <span>50.000</span>
                </div>
                <div class="flex justify-between text-[#5c6c7a]">
                    <span>Kembali:</span>
                    <span>8.000</span>
                </div>
            </div>

            <div class="mt-3 text-center font-mono text-xs">
                <div class="text-[#a8b3bc]">― ― ― ― ― ― ― ― ― ―</div>
                <div v-if="receiptFooter" class="mt-1 text-[#5c6c7a]">{{ receiptFooter }}</div>
                <div class="mt-0.5 text-[#a8b3bc]">― ― ― ― ― ― ― ― ― ―</div>
            </div>
        </div>

        <div class="flex gap-3">
            <button
                @click="$emit('back')"
                :disabled="isSubmitting"
                class="flex flex-1 items-center justify-center gap-2 rounded-full border border-[#c1ccd6] py-3 text-sm font-semibold text-[#3d4f5b] transition-colors hover:border-[#001e2b] hover:text-[#001e2b] disabled:opacity-40 disabled:cursor-not-allowed"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali
            </button>
            <button
                @click="$emit('submit')"
                :disabled="isSubmitting"
                class="flex flex-1 items-center justify-center gap-2 rounded-full bg-[#00ed64] py-3 text-sm font-semibold text-[#001e2b] transition-colors hover:bg-[#00b545] active:bg-[#008c34] disabled:opacity-60 disabled:cursor-not-allowed"
            >
                <Loader2 v-if="isSubmitting" class="h-4 w-4 animate-spin" />
                <Check v-else class="h-4 w-4 stroke-[2.5]" />
                {{ isSubmitting ? 'Menyimpan...' : 'Selesaikan Setup' }}
            </button>
        </div>
    </div>
</template>
