<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Command, CommandGroup, CommandItem, CommandList } from '@/Components/ui/command';
import { Check, ChevronsUpDown } from 'lucide-vue-next';

const props = defineProps({
    tax_enabled: Boolean,
    tax_type: String,
    tax_value: [Number, String],
    service_charge_enabled: Boolean,
    service_charge_type: String,
    service_charge_value: [Number, String],
});

const form = useForm({
    tax_enabled: props.tax_enabled,
    tax_type: props.tax_type,
    tax_value: props.tax_value,
    service_charge_enabled: props.service_charge_enabled,
    service_charge_type: props.service_charge_type,
    service_charge_value: props.service_charge_value,
});

const openTaxTypeBox = ref(false);
const openServiceChargeTypeBox = ref(false);

const changeTaxType = (newType) => {
    if (form.tax_type === newType) return;
    form.tax_value = 0;
    form.tax_type = newType;
    openTaxTypeBox.value = false;
};

const changeServiceChargeType = (newType) => {
    if (form.service_charge_type === newType) return;
    form.service_charge_value = 0;
    form.service_charge_type = newType;
    openServiceChargeTypeBox.value = false;
};

const saveSettings = () => {
    form.post('/settings/taxes', {
        preserveScroll: true,
    });
};
</script>

<template>
    <SettingsLayout title="Pajak & Layanan">
        <form @submit.prevent="saveSettings" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Pajak & Layanan</h2>
                    <p class="text-sm text-gray-500 mt-1">Konfigurasi perhitungan pajak dan service charge untuk setiap transaksi.</p>
                </div>

                <div class="p-6 space-y-8">
                <!-- Section: Pajak (Tax) -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-md font-medium text-gray-900">Pajak Transaksi</h3>
                            <p class="text-sm text-gray-500">Aktifkan untuk menambahkan pajak pada subtotal pesanan.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.tax_enabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00ed64]"></div>
                        </label>
                    </div>

                    <div v-if="form.tax_enabled" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Pajak</label>
                            <Popover v-model:open="openTaxTypeBox">
                                <PopoverTrigger as-child>
                                    <button
                                        type="button"
                                        role="combobox"
                                        :aria-expanded="openTaxTypeBox"
                                        class="flex items-center justify-between h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pl-4 pr-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10"
                                    >
                                        <span class="truncate">{{ form.tax_type === 'percentage' ? 'Persentase (%)' : 'Nominal (Rp)' }}</span>
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-[#7c8c9a]" />
                                    </button>
                                </PopoverTrigger>
                                <PopoverContent class="w-full p-0 bg-white" align="start">
                                    <Command>
                                        <CommandList>
                                            <CommandGroup>
                                                <CommandItem
                                                    value="Persentase (%)"
                                                    @select="changeTaxType('percentage')"
                                                    class="text-sm cursor-pointer"
                                                >
                                                    Persentase (%)
                                                    <Check
                                                        :class="['ml-auto h-4 w-4', form.tax_type === 'percentage' ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                                    />
                                                </CommandItem>
                                                <CommandItem
                                                    value="Nominal (Rp)"
                                                    @select="changeTaxType('nominal')"
                                                    class="text-sm cursor-pointer"
                                                >
                                                    Nominal (Rp)
                                                    <Check
                                                        :class="['ml-auto h-4 w-4', form.tax_type === 'nominal' ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                                    />
                                                </CommandItem>
                                            </CommandGroup>
                                        </CommandList>
                                    </Command>
                                </PopoverContent>
                            </Popover>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Pajak</label>
                            <div class="relative">
                                <span v-if="form.tax_type === 'nominal'" class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 text-sm">Rp</span>
                                <input 
                                    type="number" 
                                    v-model="form.tax_value" 
                                    min="0"
                                    step="0.01"
                                    :class="['h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10', form.tax_type === 'nominal' ? 'pl-9' : 'pl-4 pr-8']"
                                />
                                <span v-if="form.tax_type === 'percentage'" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 text-sm">%</span>
                            </div>
                            <div v-if="form.errors.tax_value" class="text-red-500 text-xs mt-1">{{ form.errors.tax_value }}</div>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100">

                <!-- Section: Service Charge -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-md font-medium text-gray-900">Biaya Layanan (Service Charge)</h3>
                            <p class="text-sm text-gray-500">Aktifkan untuk membebankan biaya layanan ekstra.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.service_charge_enabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00ed64]"></div>
                        </label>
                    </div>

                    <div v-if="form.service_charge_enabled" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Layanan</label>
                            <Popover v-model:open="openServiceChargeTypeBox">
                                <PopoverTrigger as-child>
                                    <button
                                        type="button"
                                        role="combobox"
                                        :aria-expanded="openServiceChargeTypeBox"
                                        class="flex items-center justify-between h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pl-4 pr-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10"
                                    >
                                        <span class="truncate">{{ form.service_charge_type === 'percentage' ? 'Persentase (%)' : 'Nominal (Rp)' }}</span>
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-[#7c8c9a]" />
                                    </button>
                                </PopoverTrigger>
                                <PopoverContent class="w-full p-0 bg-white" align="start">
                                    <Command>
                                        <CommandList>
                                            <CommandGroup>
                                                <CommandItem
                                                    value="Persentase (%)"
                                                    @select="changeServiceChargeType('percentage')"
                                                    class="text-sm cursor-pointer"
                                                >
                                                    Persentase (%)
                                                    <Check
                                                        :class="['ml-auto h-4 w-4', form.service_charge_type === 'percentage' ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                                    />
                                                </CommandItem>
                                                <CommandItem
                                                    value="Nominal (Rp)"
                                                    @select="changeServiceChargeType('nominal')"
                                                    class="text-sm cursor-pointer"
                                                >
                                                    Nominal (Rp)
                                                    <Check
                                                        :class="['ml-auto h-4 w-4', form.service_charge_type === 'nominal' ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                                    />
                                                </CommandItem>
                                            </CommandGroup>
                                        </CommandList>
                                    </Command>
                                </PopoverContent>
                            </Popover>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Layanan</label>
                            <div class="relative">
                                <span v-if="form.service_charge_type === 'nominal'" class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 text-sm">Rp</span>
                                <input 
                                    type="number" 
                                    v-model="form.service_charge_value" 
                                    min="0"
                                    step="0.01"
                                    :class="['h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10', form.service_charge_type === 'nominal' ? 'pl-9' : 'pl-4 pr-8']"
                                />
                                <span v-if="form.service_charge_type === 'percentage'" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 text-sm">%</span>
                            </div>
                            <div v-if="form.errors.service_charge_value" class="text-red-500 text-xs mt-1">{{ form.errors.service_charge_value }}</div>
                        </div>
                    </div>
                </div>

                </div>
            </div>

            <div class="flex justify-end">
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-[#001e2b] text-white rounded-lg hover:bg-gray-800 transition-colors font-medium text-sm flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="mr-2">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </SettingsLayout>
</template>
