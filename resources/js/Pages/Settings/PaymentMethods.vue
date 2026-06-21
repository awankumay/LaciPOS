<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Command, CommandGroup, CommandItem, CommandList } from '@/Components/ui/command';
import { Plus, Edit2, Trash2, Check, ChevronsUpDown } from 'lucide-vue-next';

const props = defineProps({
    paymentMethods: Array,
});

const isModalOpen = ref(false);
const editingId = ref(null);
const openCategoryBox = ref(false);

const form = useForm({
    name: '',
    category: 'bank_transfer',
    account_details: '',
    admin_fee_type: 'percentage',
    admin_fee: 0,
    min_amount_for_fee: 0,
    is_active: true,
});

const categories = {
    cash: 'Tunai',
    bank_transfer: 'Transfer Bank',
    e_wallet: 'E-Wallet',
    qris: 'QRIS',
};

const selectableCategories = [
    { value: 'bank_transfer', label: 'Transfer Bank' },
    { value: 'e_wallet', label: 'E-Wallet' },
    { value: 'qris', label: 'QRIS' }
];

const getCategoryLabel = (val) => {
    const found = selectableCategories.find(c => c.value === val);
    return found ? found.label : '';
};

const openAdminFeeTypeBox = ref(false);

const adminFeeTypes = [
    { value: 'percentage', label: 'Persentase (%)' },
    { value: 'nominal', label: 'Nominal (Rp)' }
];

const getAdminFeeTypeLabel = (val) => {
    const found = adminFeeTypes.find(c => c.value === val);
    return found ? found.label : '';
};

const openAddModal = () => {
    editingId.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (method) => {
    editingId.value = method.id;
    form.name = method.name;
    form.category = method.category;
    form.account_details = method.account_details || '';
    form.admin_fee_type = method.admin_fee_type || 'percentage';
    form.admin_fee = method.admin_fee;
    form.min_amount_for_fee = method.min_amount_for_fee;
    form.is_active = method.is_active;
    isModalOpen.value = true;
};

const deleteMethod = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus metode pembayaran ini?')) {
        router.delete(`/settings/payment-methods/${id}`);
    }
};

const saveMethod = () => {
    if (editingId.value) {
        form.put(`/settings/payment-methods/${editingId.value}`, {
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    } else {
        form.post('/settings/payment-methods', {
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    }
};

const toggleActive = (method) => {
    router.put(`/settings/payment-methods/${method.id}`, {
        name: method.name,
        category: method.category,
        account_details: method.account_details,
        admin_fee_type: method.admin_fee_type,
        admin_fee: method.admin_fee,
        min_amount_for_fee: method.min_amount_for_fee,
        is_active: !method.is_active
    }, { preserveScroll: true });
};
</script>

<template>
    <SettingsLayout title="Metode Pembayaran">
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Metode Pembayaran</h2>
                        <p class="text-sm text-gray-500 mt-1">Kelola daftar metode pembayaran dan perhitungan MDR/Biaya Admin.</p>
                    </div>
                    <button @click="openAddModal" class="px-6 py-2 bg-[#00ed64] text-[#001e2b] rounded-lg hover:bg-[#00b545] transition-colors font-medium text-sm flex items-center shrink-0">
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Metode
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                                <th class="px-6 py-4">Nama Metode</th>
                                <th class="px-6 py-4">Kategori</th>
                                <th class="px-6 py-4">Detail Akun</th>
                                <th class="px-6 py-4">MDR</th>
                                <th class="px-6 py-4">Min. Transaksi (MDR)</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="method in paymentMethods" :key="method.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ method.name }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ categories[method.category] }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ method.category === 'cash' ? '-' : (method.account_details || '-') }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ method.category === 'cash' ? '-' : (method.admin_fee_type === 'percentage' ? Number(method.admin_fee) + '%' : 'Rp ' + Number(method.admin_fee).toLocaleString('id-ID')) }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ method.category === 'cash' ? '-' : ('Rp ' + Number(method.min_amount_for_fee).toLocaleString('id-ID')) }}</td>
                                <td class="px-6 py-4 text-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" :checked="method.is_active" @change="toggleActive(method)" class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#00ed64]"></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <template v-if="method.category !== 'cash'">
                                        <button @click="openEditModal(method)" class="p-1 text-blue-600 hover:bg-blue-50 rounded">
                                            <Edit2 class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteMethod(method.id)" class="p-1 text-red-600 hover:bg-red-50 rounded">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </template>
                                    <template v-else>
                                        <span class="text-xs text-gray-400 italic">Dibekukan</span>
                                    </template>
                                </td>
                            </tr>
                            <tr v-if="paymentMethods.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    Belum ada metode pembayaran yang ditambahkan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Modal
            :show="isModalOpen"
            @update:show="isModalOpen = $event"
            :title="editingId ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran'"
            description="Kelola informasi metode pembayaran di sini."
        >
            <form id="payment-method-form" @submit.prevent="saveMethod" class="space-y-4 py-2">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Metode <span class="text-red-500">*</span></label>
                    <input v-model="form.name" type="text" class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10 px-4" placeholder="Contoh: Transfer BCA" required />
                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <Popover v-model:open="openCategoryBox">
                        <PopoverTrigger as-child>
                            <button
                                type="button"
                                role="combobox"
                                :aria-expanded="openCategoryBox"
                                class="flex items-center justify-between h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pl-4 pr-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10"
                            >
                                <span class="truncate">{{ getCategoryLabel(form.category) || 'Pilih Kategori' }}</span>
                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-[#7c8c9a]" />
                            </button>
                        </PopoverTrigger>
                        <PopoverContent class="w-full p-0 bg-white" align="start">
                            <Command>
                                <CommandList>
                                    <CommandGroup>
                                        <CommandItem
                                            v-for="cat in selectableCategories"
                                            :key="cat.value"
                                            :value="cat.label"
                                            @select="() => {
                                                form.category = cat.value;
                                                openCategoryBox = false;
                                            }"
                                            class="text-sm cursor-pointer"
                                        >
                                            {{ cat.label }}
                                            <Check
                                                :class="['ml-auto h-4 w-4', form.category === cat.value ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                            />
                                        </CommandItem>
                                    </CommandGroup>
                                </CommandList>
                            </Command>
                        </PopoverContent>
                    </Popover>
                    <div v-if="form.errors.category" class="text-red-500 text-xs mt-1">{{ form.errors.category }}</div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Detail Akun / No. Rekening</label>
                    <input v-model="form.account_details" type="text" class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10 px-4" placeholder="Contoh: 8830xxxx a.n Coffee Shop" />
                    <p class="text-xs text-gray-500 mt-1">Akan ditampilkan pada layar kasir untuk didiktekan ke pelanggan.</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe MDR / Admin <span class="text-red-500">*</span></label>
                        <Popover v-model:open="openAdminFeeTypeBox">
                            <PopoverTrigger as-child>
                                <button
                                    type="button"
                                    role="combobox"
                                    :aria-expanded="openAdminFeeTypeBox"
                                    class="flex items-center justify-between h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pl-4 pr-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10"
                                >
                                    <span class="truncate">{{ getAdminFeeTypeLabel(form.admin_fee_type) }}</span>
                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-[#7c8c9a]" />
                                </button>
                            </PopoverTrigger>
                            <PopoverContent class="w-full p-0 bg-white" align="start">
                                <Command>
                                    <CommandList>
                                        <CommandGroup>
                                            <CommandItem
                                                v-for="type in adminFeeTypes"
                                                :key="type.value"
                                                :value="type.label"
                                                @select="() => {
                                                    form.admin_fee_type = type.value;
                                                    openAdminFeeTypeBox = false;
                                                }"
                                                class="text-sm cursor-pointer"
                                            >
                                                {{ type.label }}
                                                <Check
                                                    :class="['ml-auto h-4 w-4', form.admin_fee_type === type.value ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                                />
                                            </CommandItem>
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">MDR / Admin <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span v-if="form.admin_fee_type === 'nominal'" class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 text-sm">Rp</span>
                            <input v-model="form.admin_fee" type="number" step="0.01" min="0" :class="['h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10', form.admin_fee_type === 'nominal' ? 'pl-9 pr-4' : 'px-4 pr-8']" required />
                            <span v-if="form.admin_fee_type === 'percentage'" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500">%</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Mulai <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 text-sm">Rp</span>
                            <input v-model="form.min_amount_for_fee" type="number" min="0" class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10 pl-9 pr-4" required />
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">Isi 0 jika selalu kena MDR.</p>
                    </div>

                <div class="flex items-center gap-2 mt-4 mb-2">
                    <input type="checkbox" id="is_active" v-model="form.is_active" class="rounded text-[#00684a] focus:ring-[#00684a]" />
                    <label for="is_active" class="text-sm text-gray-700 font-medium">Metode Pembayaran Aktif</label>
                </div>
            </form>
            <template #footer>
                <div class="flex items-center gap-3 w-full justify-end">
                    <button type="button" @click="isModalOpen = false" class="px-6 py-2 bg-white text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm flex items-center">Batal</button>
                    <button type="submit" form="payment-method-form" :disabled="form.processing" class="px-6 py-2 bg-[#001e2b] text-white rounded-lg hover:bg-gray-800 transition-colors font-medium text-sm flex items-center disabled:opacity-50">Simpan</button>
                </div>
            </template>
        </Modal>
    </SettingsLayout>
</template>

<style scoped>
</style>
