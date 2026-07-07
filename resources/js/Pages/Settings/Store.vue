<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import { Input } from '@/Components/ui/input';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/Components/ui/card';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Command, CommandGroup, CommandItem, CommandList } from '@/Components/ui/command';
import { UploadCloud, X, Check, ChevronsUpDown } from 'lucide-vue-next';

const props = defineProps({
    profile: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    store_name: props.profile?.store_name || '',
    address: props.profile?.address || '',
    phone: props.profile?.phone || '',
    logo: null,
    remove_logo: false,
    receipt_footer: props.profile?.receipt_footer || 'Terima kasih sudah berkunjung!',
    timezone: props.profile?.timezone || 'Asia/Jakarta',
    enable_customer_name: Boolean(props.profile?.enable_customer_name),
    enable_table_number: Boolean(props.profile?.enable_table_number),
    enable_order_notes: Boolean(props.profile?.enable_order_notes),
});

const openTimezoneBox = ref(false);
const timezones = [
    { value: 'Asia/Jakarta', label: 'Asia/Jakarta (WIB)' },
    { value: 'Asia/Makassar', label: 'Asia/Makassar (WITA)' },
    { value: 'Asia/Jayapura', label: 'Asia/Jayapura (WIT)' }
];

const logoPreviewUrl = ref(props.profile?.logo_url || null);
const logoError = ref('');

const handleLogoChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
    if (!allowedTypes.includes(file.type)) {
        logoError.value = 'Format logo harus PNG atau JPG.';
        event.target.value = '';
        return;
    }

    const maxSize = 2 * 1024 * 1024;
    if (file.size > maxSize) {
        logoError.value = 'Ukuran logo maksimal 2MB.';
        event.target.value = '';
        return;
    }

    logoError.value = '';
    form.logo = file;
    form.remove_logo = false;
    logoPreviewUrl.value = URL.createObjectURL(file);
};

const removeLogo = () => {
    form.logo = null;
    form.remove_logo = true;
    logoPreviewUrl.value = null;
};

const submit = () => {
    form.post('/settings/store', {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <SettingsLayout title="Informasi Umum">
        <form @submit.prevent="submit" class="space-y-6">
            
            <!-- Card 1: Informasi Toko -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Informasi Toko</h2>
                    <p class="text-sm text-gray-500 mt-1">Informasi detail mengenai toko Anda yang akan muncul di struk transaksi.</p>
                </div>

                <div class="p-6 space-y-6">
                    <div class="space-y-2">
                        <label for="store_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Toko <span class="text-red-500">*</span></label>
                        <input
                            id="store_name"
                            v-model="form.store_name"
                            placeholder="Contoh: Toko Maju Jaya"
                            required
                            class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10 px-4"
                        />
                        <p v-if="form.errors.store_name" class="text-sm text-red-500">{{ form.errors.store_name }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                        <input
                            id="address"
                            v-model="form.address"
                            placeholder="Contoh: Jl. Merdeka No. 123, Jakarta"
                            class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10 px-4"
                        />
                        <p v-if="form.errors.address" class="text-sm text-red-500">{{ form.errors.address }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            placeholder="Contoh: 081234567890"
                            class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10 px-4"
                        />
                        <p v-if="form.errors.phone" class="text-sm text-red-500">{{ form.errors.phone }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Zona Waktu <span class="text-red-500">*</span></label>
                        <Popover v-model:open="openTimezoneBox">
                            <PopoverTrigger as-child>
                                <button
                                    type="button"
                                    role="combobox"
                                    :aria-expanded="openTimezoneBox"
                                    class="flex items-center justify-between h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pl-4 pr-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10"
                                >
                                    <span class="truncate">{{ form.timezone ? timezones.find(tz => tz.value === form.timezone)?.label : 'Pilih Zona Waktu' }}</span>
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
                                                    form.timezone = tz.value;
                                                    openTimezoneBox = false;
                                                }"
                                                class="text-sm cursor-pointer"
                                            >
                                                {{ tz.label }}
                                                <Check
                                                    :class="['ml-auto h-4 w-4', form.timezone === tz.value ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                                />
                                            </CommandItem>
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                        <p class="text-xs text-gray-500 mt-1">Zona waktu yang digunakan untuk seluruh fitur sistem (termasuk jam tayang diskon produk).</p>
                        <p v-if="form.errors.timezone" class="text-sm text-red-500">{{ form.errors.timezone }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo Toko</label>
                        <div class="mt-2 flex items-center gap-6">
                            <div class="relative shrink-0">
                                <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-[#c1ccd6] bg-[#f8fafc]">
                                    <img
                                        v-if="logoPreviewUrl"
                                        :src="logoPreviewUrl"
                                        class="h-full w-full object-cover"
                                        alt="Logo Toko"
                                    />
                                    <UploadCloud v-else class="h-8 w-8 text-[#a8b3bc]" />
                                </div>
                                <button
                                    v-if="logoPreviewUrl"
                                    type="button"
                                    @click="removeLogo"
                                    class="absolute -right-2 -top-2 rounded-full bg-white p-1 text-[#ff3b3b] shadow-sm hover:bg-[#fff0f0] border border-[#ff3b3b] z-10"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                            <div>
                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-[#c1ccd6] bg-white px-5 py-2.5 text-sm font-semibold text-[#3d4f5b] transition-colors hover:border-[#001e2b] hover:text-[#001e2b]">
                                    <UploadCloud class="h-4 w-4" />
                                    <span>Pilih Foto</span>
                                    <input
                                        type="file"
                                        accept="image/png, image/jpeg, image/jpg"
                                        class="hidden"
                                        @change="handleLogoChange"
                                    />
                                </label>
                                <p class="mt-2 text-xs text-[#7c8c9a]">Format .PNG atau .JPG, maks. 2MB.</p>
                            </div>
                        </div>
                        <p v-if="logoError" class="text-sm text-red-500">{{ logoError }}</p>
                        <p v-if="form.errors.logo" class="text-sm text-red-500">{{ form.errors.logo }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="receipt_footer" class="block text-sm font-medium text-gray-700 mb-1">Pesan Footer Struk</label>
                        <input
                            id="receipt_footer"
                            v-model="form.receipt_footer"
                            placeholder="Contoh: Terima kasih sudah berkunjung!"
                            class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10 px-4"
                        />
                        <p v-if="form.errors.receipt_footer" class="text-sm text-red-500">{{ form.errors.receipt_footer }}</p>
                    </div>
                </div>
            </div>

            <!-- Card 2: Informasi Pemesan -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Informasi Pemesan (Kasir)</h2>
                    <p class="text-sm text-gray-500 mt-1">Aktifkan pengaturan ini untuk menampilkan isian data pelanggan saat proses pembayaran (Checkout).</p>
                </div>
                
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Nama Pelanggan</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Wajib isi nama pelanggan sebelum bayar.</p>
                        </div>
                        <label for="toggle_customer_name" class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="toggle_customer_name" name="enable_customer_name" v-model="form.enable_customer_name" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00ed64]"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Nomor Meja</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Wajib isi nomor meja pelanggan sebelum bayar.</p>
                        </div>
                        <label for="toggle_table_number" class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="toggle_table_number" name="enable_table_number" v-model="form.enable_table_number" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00ed64]"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Catatan Pesanan</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Sediakan kolom isian opsional untuk pesan khusus sebelum bayar.</p>
                        </div>
                        <label for="toggle_order_notes" class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="toggle_order_notes" name="enable_order_notes" v-model="form.enable_order_notes" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00ed64]"></div>
                        </label>
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
