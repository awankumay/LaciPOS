<script setup>
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Phone, ArrowRight, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    phone: { type: String, default: '' },
    receiptFooter: { type: String, default: 'Terima kasih sudah berkunjung!' },
});

const emit = defineEmits(['update:phone', 'update:receiptFooter', 'next', 'back']);
</script>

<template>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#e3fcef]">
                <Phone class="h-6 w-6 text-[#00684a]" />
            </div>
            <div>
                <h2 class="text-lg font-semibold text-[#001e2b]">Informasi Kontak</h2>
                <p class="mt-1 text-sm text-[#5c6c7a]">
                    Nomor telepon dan teks footer akan ditampilkan di struk.
                </p>
            </div>
        </div>

        <!-- Fields -->
        <div class="space-y-5">
            <div class="space-y-2">
                <Label for="phone" class="text-sm font-medium text-[#1c2d38]">
                    Nomor Telepon
                    <span class="ml-1 text-xs font-normal text-[#7c8c9a]">(opsional)</span>
                </Label>
                <Input
                    id="phone"
                    :model-value="phone"
                    @update:model-value="$emit('update:phone', $event)"
                    type="tel"
                    placeholder="Contoh: 0812-3456-7890"
                    autofocus
                    class="h-11 rounded-xl border-[1.5px] border-[#c1ccd6] px-4 shadow-[0_1px_2px_rgba(0,30,43,0.04)] focus-visible:border-[#00684a] focus-visible:ring-[3px] focus-visible:ring-[#00684a]/10"
                />
            </div>

            <div class="space-y-2">
                <Label for="receipt_footer" class="text-sm font-medium text-[#1c2d38]">
                    Teks Footer Struk
                    <span class="ml-1 text-xs font-normal text-[#7c8c9a]">(opsional)</span>
                </Label>
                <Input
                    id="receipt_footer"
                    :model-value="receiptFooter"
                    @update:model-value="$emit('update:receiptFooter', $event)"
                    type="text"
                    placeholder="Contoh: Terima kasih sudah berkunjung!"
                    class="h-11 rounded-xl border-[1.5px] border-[#c1ccd6] px-4 shadow-[0_1px_2px_rgba(0,30,43,0.04)] focus-visible:border-[#00684a] focus-visible:ring-[3px] focus-visible:ring-[#00684a]/10"
                />
                <p class="text-xs text-[#7c8c9a]">Teks ini akan muncul di bagian bawah struk.</p>
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex gap-3">
            <button
                @click="$emit('back')"
                class="flex flex-1 items-center justify-center gap-2 rounded-full border border-[#c1ccd6] py-3 text-sm font-semibold text-[#3d4f5b] transition-colors hover:border-[#001e2b] hover:text-[#001e2b]"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali
            </button>
            <button
                @click="$emit('next')"
                class="flex flex-1 items-center justify-center gap-2 rounded-full bg-[#00ed64] py-3 text-sm font-semibold text-[#001e2b] transition-colors hover:bg-[#00b545] active:bg-[#008c34]"
            >
                Lanjutkan
                <ArrowRight class="h-4 w-4" />
            </button>
        </div>
    </div>
</template>
