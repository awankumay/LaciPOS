<script setup>
import { ref } from 'vue';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Command, CommandGroup, CommandItem, CommandList } from '@/Components/ui/command';
import { MapPin, ArrowRight, ArrowLeft, ChevronsUpDown, Check } from 'lucide-vue-next';

const props = defineProps({
    address: { type: String, default: '' },
    timezone: { type: String, default: 'Asia/Jakarta' },
});

const emit = defineEmits(['update:address', 'update:timezone', 'next', 'back']);

const openTimezone = ref(false);
const timezones = [
    { value: 'Asia/Jakarta', label: 'Asia/Jakarta (WIB)' },
    { value: 'Asia/Makassar', label: 'Asia/Makassar (WITA)' },
    { value: 'Asia/Jayapura', label: 'Asia/Jayapura (WIT)' },
];
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
                class="h-11 rounded-xl border-[1.5px] border-[#c1ccd6] px-4 shadow-[0_1px_2px_rgba(0,30,43,0.04)] focus-visible:border-[#00684a] focus-visible:ring-[3px] focus-visible:ring-[#00684a]/10"
            />
        </div>

        <!-- Field Timezone -->
        <div class="space-y-2">
            <Label for="timezone" class="text-sm font-medium text-[#1c2d38]">
                Zona Waktu <span class="text-red-500">*</span>
            </Label>
            <Popover v-model:open="openTimezone">
                <PopoverTrigger as-child>
                    <button
                        id="timezone"
                        type="button"
                        role="combobox"
                        :aria-expanded="openTimezone"
                        class="flex items-center justify-between h-11 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pl-4 pr-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10"
                    >
                        <span class="truncate">{{ timezone ? timezones.find(tz => tz.value === timezone)?.label : 'Pilih Zona Waktu' }}</span>
                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-[#7c8c9a]" />
                    </button>
                </PopoverTrigger>
                <PopoverContent class="w-full sm:w-[400px] p-0 bg-white" align="start">
                    <Command>
                        <CommandList>
                            <CommandGroup>
                                <CommandItem
                                    v-for="tz in timezones"
                                    :key="tz.value"
                                    :value="tz.label"
                                    @select="() => {
                                        emit('update:timezone', tz.value);
                                        openTimezone = false;
                                    }"
                                    class="text-sm cursor-pointer"
                                >
                                    {{ tz.label }}
                                    <Check
                                        :class="['ml-auto h-4 w-4', timezone === tz.value ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                    />
                                </CommandItem>
                            </CommandGroup>
                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>
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
