<script setup>
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { MapPin, ArrowRight, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    address: { type: String, default: '' },
    timezone: { type: String, default: 'Asia/Jakarta' },
});

const emit = defineEmits(['update:address', 'update:timezone', 'next', 'back']);
</script>

<template>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#e3fcef]">
                <MapPin class="h-6 w-6 text-[#00684a]" />
            </div>
            <div>
                <h2 class="text-lg font-semibold text-[#001e2b]">Di mana lokasi toko Anda?</h2>
                <p class="mt-1 text-sm text-[#5c6c7a]">
                    Alamat ini akan muncul di struk. Bisa dilewati jika belum ada.
                </p>
            </div>
        </div>

        <!-- Field -->
        <div class="space-y-2">
            <Label for="address" class="text-sm font-medium text-[#1c2d38]">
                Alamat Toko
                <span class="ml-1 text-xs font-normal text-[#7c8c9a]">(opsional)</span>
            </Label>
            <Input
                id="address"
                :model-value="address"
                @update:model-value="$emit('update:address', $event)"
                type="text"
                placeholder="Contoh: Jl. Merdeka No. 123, Jakarta"
                autofocus
                class="h-11"
            />
        </div>

        <!-- Field Timezone -->
        <div class="space-y-2">
            <Label for="timezone" class="text-sm font-medium text-[#1c2d38]">
                Zona Waktu <span class="text-red-500">*</span>
            </Label>
            <select
                id="timezone"
                :value="timezone"
                @change="$emit('update:timezone', $event.target.value)"
                required
                class="w-full h-11 px-4 rounded-xl border border-gray-200 bg-white focus:border-[#00684a] focus:ring-4 focus:ring-[#00684a]/10 transition-all text-sm font-medium text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none"
            >
                <option value="Asia/Jakarta">Asia/Jakarta (WIB)</option>
                <option value="Asia/Makassar">Asia/Makassar (WITA)</option>
                <option value="Asia/Jayapura">Asia/Jayapura (WIT)</option>
            </select>
            <p class="text-xs text-[#5c6c7a] mt-1">Zona waktu yang digunakan untuk seluruh fitur sistem.</p>
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
