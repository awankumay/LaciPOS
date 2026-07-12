<script setup>
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
            <div class="font-mono text-xs leading-relaxed">
                <div class="border-t border-dashed border-[#999]"></div>
                <div class="text-center">
                    <img
                        v-if="logoPreviewUrl"
                        :src="logoPreviewUrl"
                        alt="Logo"
                        class="mx-auto my-2 h-12 w-12 object-contain"
                    />
                    <div class="font-bold text-[#001e2b]" style="font-size: 1.1em;">{{ storeName || 'Nama Toko' }}</div>
                    <div v-if="address" class="text-[#777]">{{ address }}</div>
                    <div v-if="phone" class="text-[#777]">Telp: {{ phone }}</div>
                </div>
                <div class="border-t border-dashed border-[#999] my-1.5"></div>
                <table class="w-full">
                    <tr><td>No: TRX-20260610-001</td></tr>
                    <tr><td>Kasir: Sari</td></tr>
                    <tr><td>Tgl: 10 Jun 2026, 14:32</td></tr>
                </table>
                <div class="border-t border-dashed border-[#999] my-1.5"></div>
                <table class="w-full">
                    <tr class="align-top">
                        <td>
                            Kopi Susu
                            <br>
                            <span class="text-[#777]">1 x <span class="line-through">Rp 10.000</span> Rp 8.000</span>
                        </td>
                        <td class="text-right whitespace-nowrap">Rp 8.000</td>
                    </tr>
                    <tr class="align-top">
                        <td>
                            Es Teh
                            <br>
                            <span class="text-[#777]">2 x Rp 7.000</span>
                        </td>
                        <td class="text-right whitespace-nowrap">Rp 14.000</td>
                    </tr>
                </table>
                <div class="border-t border-dashed border-[#999] my-1.5"></div>
                <table class="w-full">
                    <tr>
                        <td>Subtotal</td>
                        <td class="text-right whitespace-nowrap">Rp 24.000</td>
                    </tr>
                    <tr>
                        <td>Diskon Trx</td>
                        <td class="text-right whitespace-nowrap">- Rp 2.000</td>
                    </tr>
                    <tr>
                        <td>Pajak (10%)</td>
                        <td class="text-right whitespace-nowrap">Rp 2.200</td>
                    </tr>
                    <tr>
                        <td class="font-bold" style="padding-top: 4px;">Total:</td>
                        <td class="text-right whitespace-nowrap font-bold" style="padding-top: 4px;">Rp 24.200</td>
                    </tr>
                    <tr>
                        <td class="text-[#777]">Bayar (Tunai)</td>
                        <td class="text-right whitespace-nowrap text-[#777]">Rp 50.000</td>
                    </tr>
                    <tr>
                        <td class="text-[#777]">Kembali</td>
                        <td class="text-right whitespace-nowrap text-[#777]">Rp 25.800</td>
                    </tr>
                </table>
                <div class="border-t border-dashed border-[#999] my-1.5"></div>
                <div class="text-center">
                    <div v-if="receiptFooter" class="text-[#777]">{{ receiptFooter }}</div>
                    <div class="font-bold mt-0.5">Terima Kasih</div>
                </div>
                <div class="border-t border-dashed border-[#999] mt-1.5"></div>
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
