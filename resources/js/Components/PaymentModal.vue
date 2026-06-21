<script setup>
import { ref, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogTitle, DialogDescription } from '@/Components/ui/dialog';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Command, CommandInput, CommandEmpty, CommandGroup, CommandItem, CommandList } from '@/Components/ui/command';
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import { Banknote, CreditCard, Smartphone, X, CheckCircle2, Check, ChevronsUpDown } from 'lucide-vue-next';
import CashCalculator from '@/Components/CashCalculator.vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    total: { type: Number, default: 0 },
    paymentMethods: { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'confirm-payment']);
const { formatRupiah } = useFormatCurrency();

const page = usePage();
const storeSettings = computed(() => page.props.storeSettings || {});
const enableCustomerName = computed(() => storeSettings.value.enable_customer_name || false);
const enableTableNumber = computed(() => storeSettings.value.enable_table_number || false);
const enableOrderNotes = computed(() => storeSettings.value.enable_order_notes || false);

const selectedCategory = ref(null);
const selectedMethodId = ref(null);
const openMethodBox = ref(false);
const cashReceived = ref(0);

const customerName = ref('');
const tableNumber = ref('');
const orderNotes = ref('');

const hasCategory = (cat) => {
    if (!props.paymentMethods || props.paymentMethods.length === 0) {
        // Fallback jika tidak ada data dari DB
        return cat === 'cash';
    }
    return props.paymentMethods.some(m => m.category === cat);
};

const methodsByCategory = computed(() => {
    return props.paymentMethods.filter(m => m.category === selectedCategory.value);
});

const selectedMethodDetails = computed(() => {
    if (!selectedMethodId.value) return null;
    return props.paymentMethods.find(m => m.id === selectedMethodId.value);
});

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        selectedCategory.value = null;
        selectedMethodId.value = null;
        openMethodBox.value = false;
        cashReceived.value = 0;
        customerName.value = '';
        tableNumber.value = '';
        orderNotes.value = '';
    }
});

const selectCategory = (cat) => {
    selectedCategory.value = cat;
    const available = methodsByCategory.value;
    if (available.length === 1) {
        selectedMethodId.value = available[0].id;
    } else {
        selectedMethodId.value = null;
    }
};

const canConfirm = computed(() => {
    if (enableCustomerName.value && !customerName.value.trim()) return false;
    if (enableTableNumber.value && !tableNumber.value.trim()) return false;

    if (!selectedCategory.value) return false;
    
    // Jika data master kosong, izinkan transaksi cash fallback
    if (!props.paymentMethods || props.paymentMethods.length === 0) {
        if (selectedCategory.value === 'cash') {
            return cashReceived.value >= props.total;
        }
        return false;
    }

    if (!selectedMethodId.value) return false;

    if (selectedCategory.value === 'cash') {
        return cashReceived.value >= props.total;
    }
    return true;
});

const handleConfirm = () => {
    if (!canConfirm.value) return;
    
    let paymentProvider = null;
    if (selectedMethodDetails.value) {
        paymentProvider = selectedMethodDetails.value.name;
    }

    emit('confirm-payment', {
        payment_method_id: selectedMethodId.value,
        payment_method: selectedCategory.value,
        payment_provider: paymentProvider,
        cash_received: selectedCategory.value === 'cash' ? cashReceived.value : null,
        change_amount: selectedCategory.value === 'cash' ? (cashReceived.value - props.total) : null,
        customer_name: enableCustomerName.value ? customerName.value.trim() : null,
        table_number: enableTableNumber.value ? tableNumber.value.trim() : null,
        notes: enableOrderNotes.value ? orderNotes.value.trim() : null,
    });
    emit('close');
};
</script>

<template>
    <Dialog :open="open" @update:open="$emit('close')">
        <DialogContent class="payment-modal-content">
            <!-- Header -->
            <div class="payment-modal__header">
                <div>
                    <p class="payment-modal__header-label">Total Tagihan</p>
                    <p class="payment-modal__header-amount">{{ formatRupiah(total) }}</p>
                </div>
                <button class="payment-modal__close-btn" @click="$emit('close')">
                    <X class="payment-modal__close-icon" />
                </button>
            </div>

            <!-- Body -->
            <div class="payment-modal__body">
                
                <!-- Customer Info Section -->
                <div v-if="enableCustomerName || enableTableNumber || enableOrderNotes" class="payment-detail-panel" style="animation: none;">
                    <div v-if="enableCustomerName">
                        <label class="detail-label block mb-1">Nama Pemesan <span class="text-red-500">*</span></label>
                        <input type="text" v-model="customerName" placeholder="Contoh: Budi" class="detail-input w-full" />
                    </div>
                    
                    <div v-if="enableTableNumber">
                        <label class="detail-label block mb-1">Nomor Meja <span class="text-red-500">*</span></label>
                        <input type="text" v-model="tableNumber" placeholder="Contoh: Meja 4" class="detail-input w-full" />
                    </div>
                    
                    <div v-if="enableOrderNotes">
                        <label class="detail-label block mb-1">Catatan</label>
                        <textarea v-model="orderNotes" placeholder="Contoh: Tolong dibungkus" rows="2" class="detail-input w-full resize-none"></textarea>
                    </div>
                </div>

                <!-- Method Selector -->
                <div class="payment-section">
                    <p class="payment-section__label">Metode Pembayaran</p>
                    <div class="method-grid">
                        <button
                            v-if="hasCategory('cash')"
                            class="method-btn"
                            :class="{ 'method-btn--active': selectedCategory === 'cash' }"
                            @click="selectCategory('cash')"
                        >
                            <Banknote class="method-btn__icon" />
                            <span>Tunai</span>
                            <span v-if="selectedCategory === 'cash'" class="method-btn__check">
                                <CheckCircle2 class="method-btn__check-icon" />
                            </span>
                        </button>

                        <button
                            v-if="hasCategory('bank_transfer')"
                            class="method-btn"
                            :class="{ 'method-btn--active': selectedCategory === 'bank_transfer' }"
                            @click="selectCategory('bank_transfer')"
                        >
                            <CreditCard class="method-btn__icon" />
                            <span>Transfer</span>
                            <span v-if="selectedCategory === 'bank_transfer'" class="method-btn__check">
                                <CheckCircle2 class="method-btn__check-icon" />
                            </span>
                        </button>

                        <button
                            v-if="hasCategory('e_wallet')"
                            class="method-btn"
                            :class="{ 'method-btn--active': selectedCategory === 'e_wallet' }"
                            @click="selectCategory('e_wallet')"
                        >
                            <Smartphone class="method-btn__icon" />
                            <span>E-Wallet</span>
                            <span v-if="selectedCategory === 'e_wallet'" class="method-btn__check">
                                <CheckCircle2 class="method-btn__check-icon" />
                            </span>
                        </button>

                        <button
                            v-if="hasCategory('qris')"
                            class="method-btn"
                            :class="{ 'method-btn--active': selectedCategory === 'qris' }"
                            @click="selectCategory('qris')"
                        >
                            <Smartphone class="method-btn__icon" /> <!-- Reuse icon or change -->
                            <span>QRIS</span>
                            <span v-if="selectedCategory === 'qris'" class="method-btn__check">
                                <CheckCircle2 class="method-btn__check-icon" />
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Payment Options Form based on Category -->
                <div v-if="selectedCategory" class="payment-detail-panel">
                    
                    <!-- Pilihan sub-metode jika ada lebih dari 1 atau wajib dipilih -->
                    <div v-if="methodsByCategory.length > 1 || (['bank_transfer', 'e_wallet', 'qris'].includes(selectedCategory) && methodsByCategory.length > 0)" class="mb-2">
                        <label class="detail-label mb-1 block">Pilih Metode {{ selectedCategory === 'bank_transfer' ? 'Transfer' : (selectedCategory === 'e_wallet' ? 'E-Wallet' : 'QRIS') }}</label>
                        <Popover v-model:open="openMethodBox">
                            <PopoverTrigger as-child>
                                <button
                                    type="button"
                                    role="combobox"
                                    :aria-expanded="openMethodBox"
                                    class="flex items-center justify-between h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pl-4 pr-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10"
                                >
                                    <span class="truncate">{{ selectedMethodDetails ? selectedMethodDetails.name : '-- Pilih --' }}</span>
                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-[#7c8c9a]" />
                                </button>
                            </PopoverTrigger>
                            <PopoverContent class="w-full p-0 bg-white" align="start">
                                <Command>
                                    <CommandInput placeholder="Cari metode..." class="h-9 border-none focus:ring-0" />
                                    <CommandEmpty>Tidak ada metode yang cocok.</CommandEmpty>
                                    <CommandList>
                                        <CommandGroup>
                                            <CommandItem
                                                v-for="m in methodsByCategory"
                                                :key="m.id"
                                                :value="m.name"
                                                @select="() => {
                                                    selectedMethodId = m.id;
                                                    openMethodBox = false;
                                                }"
                                                class="text-sm cursor-pointer"
                                            >
                                                {{ m.name }}
                                                <Check
                                                    :class="['ml-auto h-4 w-4', selectedMethodId === m.id ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                                />
                                            </CommandItem>
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                    </div>

                    <!-- Informasi Rekening / Detail (Hanya muncul jika sudah pilih metode non-tunai) -->
                    <div v-if="selectedMethodDetails && selectedCategory !== 'cash'" class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-xs text-gray-500 mb-1">Diktekan ke Pelanggan:</p>
                        <p class="text-sm font-semibold text-gray-900">{{ selectedMethodDetails.account_details || '-' }}</p>
                        <p v-if="selectedMethodDetails.admin_fee > 0 && total >= selectedMethodDetails.min_amount_for_fee" class="text-xs text-orange-600 mt-1">
                            *Catatan Kasir: Transaksi ini dikenakan MDR {{ selectedMethodDetails.admin_fee_type === 'percentage' ? Number(selectedMethodDetails.admin_fee) + '%' : 'Rp ' + Number(selectedMethodDetails.admin_fee).toLocaleString('id-ID') }} (ditanggung toko)
                        </p>
                    </div>

                    <!-- Cash Calculator -->
                    <div v-if="selectedCategory === 'cash'" class="mt-4">
                        <CashCalculator
                            :total-amount="total"
                            @update:cashReceived="val => cashReceived = val"
                        />
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="payment-modal__footer">
                <button class="footer-btn footer-btn--cancel" @click="$emit('close')">
                    Batal
                </button>
                <button
                    class="footer-btn footer-btn--confirm"
                    :class="{ 'footer-btn--disabled': !canConfirm }"
                    :disabled="!canConfirm"
                    @click="handleConfirm"
                >
                    Konfirmasi Pembayaran
                </button>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
/* Override DialogContent to remove default padding */
:deep(.payment-modal-content) {
    padding: 0 !important;
    border-radius: 12px !important;
    background-color: #ffffff !important;
    overflow: hidden !important;
    border: none !important;
    max-width: 420px !important;
    box-shadow: 0 16px 48px rgba(0, 30, 43, 0.16) !important;
    max-height: 90vh !important;
    display: flex !important;
    flex-direction: column !important;
}

/* ── Header ── */
.payment-modal__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 24px;
    background: #001e2b;
    color: #ffffff;
    flex-shrink: 0;
}

.payment-modal__header-label {
    font-size: 12px;
    font-weight: 500;
    color: #a8b3bc;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 4px;
}

.payment-modal__header-amount {
    font-size: 32px;
    font-weight: 700;
    color: #00ed64;
    letter-spacing: -0.5px;
    line-height: 1.1;
}

.payment-modal__close-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 9999px;
    border: none;
    background: rgba(255, 255, 255, 0.1);
    cursor: pointer;
    color: rgba(255, 255, 255, 0.7);
    transition: background 0.15s ease;
    flex-shrink: 0;
    margin-top: 2px;
}

.payment-modal__close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.payment-modal__close-icon {
    width: 16px;
    height: 16px;
}

/* ── Body ── */
.payment-modal__body {
    padding: 24px;
    background: #f9fbfa;
    display: flex;
    flex-direction: column;
    gap: 16px;
    overflow-y: auto;
    flex: 1;
}

.payment-section__label {
    font-size: 12px;
    font-weight: 600;
    color: #7c8c9a;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 10px;
}

/* ── Method Grid ── */
.method-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.method-btn {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 14px 8px;
    border-radius: 12px;
    border: 1.5px solid #e1e5e8;
    background: #ffffff;
    font-size: 13px;
    font-weight: 500;
    color: #5c6c7a;
    cursor: pointer;
    transition: all 0.15s ease;
}

.method-btn:hover {
    border-color: #001e2b;
    color: #001e2b;
    background: #f4f7f6;
}

.method-btn--active {
    border-color: #001e2b;
    background: #001e2b;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 30, 43, 0.2);
}

.method-btn__icon {
    width: 22px;
    height: 22px;
}

.method-btn__check {
    position: absolute;
    top: 8px;
    right: 8px;
}

.method-btn__check-icon {
    width: 14px;
    height: 14px;
    color: #00ed64;
}

/* ── Detail Panel ── */
.payment-detail-panel {
    background: #ffffff;
    border: 1.5px solid #e1e5e8;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    animation: panelIn 0.18s ease;
}

@keyframes panelIn {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}

.detail-label {
    font-size: 12px;
    font-weight: 600;
    color: #5c6c7a;
    text-transform: uppercase;
    letter-spacing: 0.6px;
}

.detail-input,
.detail-select {
    width: 100%;
    padding: 10px 12px;
    border: 1.5px solid #c1ccd6;
    border-radius: 10px;
    font-size: 14px;
    color: #001e2b;
    background: #ffffff;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.detail-select {
    height: 44px;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237c8c9a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
}

.detail-input:focus,
.detail-select:focus {
    border-color: #00684a;
    box-shadow: 0 0 0 3px rgba(0, 104, 74, 0.1);
}

/* ── Footer ── */
.payment-modal__footer {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 10px;
    padding: 20px 24px;
    background: #ffffff;
    border-top: 1px solid #eceff1;
    flex-shrink: 0;
}

.footer-btn {
    height: 48px;
    border-radius: 9999px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    border: none;
}

.footer-btn--cancel {
    background: transparent;
    border: 1.5px solid #c1ccd6;
    color: #5c6c7a;
}

.footer-btn--cancel:hover {
    background: #f4f7f6;
    border-color: #001e2b;
    color: #001e2b;
}

.footer-btn--confirm {
    background: #00ed64;
    color: #001e2b;
    box-shadow: 0 4px 14px rgba(0, 237, 100, 0.35);
}

.footer-btn--confirm:hover {
    background: #00b545;
    box-shadow: 0 6px 20px rgba(0, 237, 100, 0.5);
    transform: translateY(-1px);
}

.footer-btn--disabled {
    background: #eceff1 !important;
    color: #a8b3bc !important;
    box-shadow: none !important;
    cursor: not-allowed !important;
    transform: none !important;
}
</style>
