<script setup>
import { ref, watch, computed } from 'vue';
import { Dialog, DialogContent } from '@/Components/ui/dialog';
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import { Banknote, CreditCard, Smartphone, X, CheckCircle2 } from 'lucide-vue-next';
import CashCalculator from '@/Components/CashCalculator.vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    total: { type: Number, default: 0 },
});

const emit = defineEmits(['close', 'confirm-payment']);
const { formatRupiah } = useFormatCurrency();

const paymentMethod = ref(null);
const paymentProvider = ref('');
const cashReceived = ref(0);

const BANK_PROVIDERS = ['BCA', 'Mandiri', 'BNI', 'BRI', 'BSI', 'Lainnya'];
const EWALLET_PROVIDERS = ['GoPay', 'OVO', 'DANA', 'ShopeePay', 'LinkAja', 'Lainnya'];

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        paymentMethod.value = null;
        paymentProvider.value = '';
        cashReceived.value = 0;
    }
});

const selectMethod = (method) => {
    paymentMethod.value = method;
    paymentProvider.value = '';
};

const canConfirm = computed(() => {
    if (!paymentMethod.value) return false;
    if (paymentMethod.value === 'bank_transfer' || paymentMethod.value === 'e_wallet') {
        return paymentProvider.value.length > 0;
    }
    if (paymentMethod.value === 'cash') {
        return cashReceived.value >= props.total;
    }
    return false;
});

const handleConfirm = () => {
    if (!canConfirm.value) return;
    emit('confirm-payment', {
        payment_method: paymentMethod.value,
        payment_provider: paymentMethod.value === 'cash' ? null : paymentProvider.value,
        cash_received: paymentMethod.value === 'cash' ? cashReceived.value : null,
        change_amount: paymentMethod.value === 'cash' ? (cashReceived.value - props.total) : null,
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
                <!-- Method Selector -->
                <div class="payment-section">
                    <p class="payment-section__label">Metode Pembayaran</p>
                    <div class="method-grid">
                        <button
                            class="method-btn"
                            :class="{ 'method-btn--active': paymentMethod === 'cash' }"
                            @click="selectMethod('cash')"
                        >
                            <Banknote class="method-btn__icon" />
                            <span>Tunai</span>
                            <span v-if="paymentMethod === 'cash'" class="method-btn__check">
                                <CheckCircle2 class="method-btn__check-icon" />
                            </span>
                        </button>

                        <button
                            class="method-btn"
                            :class="{ 'method-btn--active': paymentMethod === 'bank_transfer' }"
                            @click="selectMethod('bank_transfer')"
                        >
                            <CreditCard class="method-btn__icon" />
                            <span>Transfer</span>
                            <span v-if="paymentMethod === 'bank_transfer'" class="method-btn__check">
                                <CheckCircle2 class="method-btn__check-icon" />
                            </span>
                        </button>

                        <button
                            class="method-btn"
                            :class="{ 'method-btn--active': paymentMethod === 'e_wallet' }"
                            @click="selectMethod('e_wallet')"
                        >
                            <Smartphone class="method-btn__icon" />
                            <span>E-Wallet</span>
                            <span v-if="paymentMethod === 'e_wallet'" class="method-btn__check">
                                <CheckCircle2 class="method-btn__check-icon" />
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Cash Calculator -->
                <div v-if="paymentMethod === 'cash'" class="payment-detail-panel">
                    <CashCalculator
                        :total-amount="total"
                        @update:cashReceived="val => cashReceived = val"
                    />
                </div>

                <!-- Bank Transfer -->
                <div v-if="paymentMethod === 'bank_transfer'" class="payment-detail-panel">
                    <label class="detail-label">Pilih Bank</label>
                    <select v-model="paymentProvider" class="detail-select">
                        <option value="" disabled>-- Pilih Bank --</option>
                        <option v-for="bank in BANK_PROVIDERS" :key="bank" :value="bank">{{ bank }}</option>
                    </select>
                </div>

                <!-- E-Wallet -->
                <div v-if="paymentMethod === 'e_wallet'" class="payment-detail-panel">
                    <label class="detail-label">Pilih E-Wallet</label>
                    <select v-model="paymentProvider" class="detail-select">
                        <option value="" disabled>-- Pilih E-Wallet --</option>
                        <option v-for="wallet in EWALLET_PROVIDERS" :key="wallet" :value="wallet">{{ wallet }}</option>
                    </select>
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
}

/* ── Header ── */
.payment-modal__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 24px;
    background: #001e2b;
    color: #ffffff;
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
    gap: 10px;
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

.detail-select {
    width: 100%;
    height: 44px;
    padding: 0 12px;
    border: 1.5px solid #c1ccd6;
    border-radius: 10px;
    font-size: 14px;
    color: #001e2b;
    background: #ffffff;
    outline: none;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237c8c9a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.detail-select:focus {
    border-color: #00684a;
    box-shadow: 0 0 0 3px rgba(0, 104, 74, 0.1);
}

/* ── Footer ── */
.payment-modal__footer {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 10px;
    padding: 24px;
    background: #ffffff;
    border-top: 1px solid #eceff1;
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
