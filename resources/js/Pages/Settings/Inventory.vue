<script setup>
import { ref } from 'vue';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { Button } from '@/Components/ui/button';

const props = defineProps({
    defaultMinStock: { type: Number, default: 5 },
});

const form = useForm({
    default_min_stock: props.defaultMinStock,
});

const submit = () => {
    form.post('/settings/inventory', {
        preserveScroll: true,
    });
};

const showApplyModal = ref(false);

const applyToAll = () => {
    router.post('/settings/inventory', {
        default_min_stock: form.default_min_stock,
        apply_to_all: true,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showApplyModal.value = false;
        },
    });
};
</script>

<template>
    <SettingsLayout title="Pengaturan Stok">
        <form @submit.prevent="submit" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Stok Minimum Global</h2>
                    <p class="text-sm text-gray-500 mt-1">Atur nilai default untuk peringatan stok menipis. Nilai ini akan otomatis digunakan saat Anda menambahkan produk baru.</p>
                </div>

                <div class="p-6 space-y-2">
                    <label for="default_min_stock" class="block text-sm font-medium text-gray-700 mb-1">Minimum Stok Alert</label>
                    <input
                        id="default_min_stock"
                        type="number"
                        v-model="form.default_min_stock"
                        min="0"
                        required
                        class="h-10 w-full md:w-1/3 rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10 px-4 block"
                    />
                    <p class="text-sm text-slate-500 mt-1">Nilai ini digunakan sebagai default saat menambah produk baru.</p>
                    <p v-if="form.errors.default_min_stock" class="text-sm text-red-500">{{ form.errors.default_min_stock }}</p>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button
                    type="button"
                    @click="showApplyModal = true"
                    class="px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm"
                >
                    Terapkan ke Semua Produk
                </button>
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

        <!-- Modal Konfirmasi Terapkan ke Semua Produk -->
        <Modal :show="showApplyModal" title="Terapkan ke Semua Produk?" @update:show="showApplyModal = $event">
            <p class="text-sm text-gray-600">
                Nilai Minimum Stok Alert <strong>{{ form.default_min_stock }}</strong> akan diterapkan ke <strong>semua produk</strong> yang sudah tersimpan. Produk dengan stok di bawah nilai ini akan masuk daftar perlu restock di dashboard.
            </p>
            <p class="text-sm text-gray-500 mt-2">Tindakan ini tidak bisa dibatalkan.</p>

            <template #footer>
                <Button variant="outline" @click="showApplyModal = false">Batal</Button>
                <Button class="bg-[#001e2b] text-white hover:bg-gray-800" @click="applyToAll" :disabled="form.processing">Ya, Terapkan</Button>
            </template>
        </Modal>
    </SettingsLayout>
</template>
