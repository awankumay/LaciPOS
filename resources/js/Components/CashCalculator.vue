<script setup>
import { ref, computed, watch } from 'vue';
import { useFormatCurrency } from '@/composables/useFormatCurrency';

const props = defineProps({
    totalAmount: { type: Number, required: true },
});

const emit = defineEmits(['update:cashReceived']);
const { formatRupiah } = useFormatCurrency();

const cashReceived = ref(0);

const changeAmount = computed(() => cashReceived.value - props.totalAmount);
const isEnough = computed(() => cashReceived.value >= props.totalAmount);

const quickAmounts = computed(() => {
    const total = props.totalAmount;
    const amounts = [];
    const base = Math.ceil(total / 10000) * 10000;
    if (base >= total) amounts.push(base);
    amounts.push(50000, 100000, 150000, 200000);
    return [...new Set(amounts)]
        .filter(a => a >= total)
        .sort((a, b) => a - b)
        .slice(0, 4);
});

const setQuickAmount = (amount) => { cashReceived.value = amount; };
const setExactAmount = () => { cashReceived.value = props.totalAmount; };

watch(cashReceived, (val) => {
    emit('update:cashReceived', val);
});
</script>

<template>
    <div class="cash-calc">
        <!-- Input -->
        <div class="cash-calc__input-group">
            <label class="cash-calc__label">Uang Diterima</label>
            <div class="cash-calc__input-wrap">
                <span class="cash-calc__prefix">Rp</span>
                <input
                    id="cash_received"
                    v-model.number="cashReceived"
                    type="number"
                    min="0"
                    step="1000"
                    class="cash-calc__input"
                    autofocus
                    placeholder="0"
                />
            </div>
        </div>

        <!-- Quick Amounts -->
        <div class="cash-calc__quick-row">
            <button 
                class="quick-btn quick-btn--exact" 
                :class="{'quick-btn--active': cashReceived === totalAmount && totalAmount > 0}"
                @click="setExactAmount"
            >
                Uang Pas
            </button>
            <button
                v-for="amount in quickAmounts"
                :key="amount"
                class="quick-btn"
                :class="{'quick-btn--active': cashReceived === amount}"
                @click="setQuickAmount(amount)"
            >
                {{ formatRupiah(amount) }}
            </button>
        </div>

        <!-- Change Display -->
        <div
            class="cash-calc__change"
            :class="{
                'cash-calc__change--enough': isEnough && cashReceived > 0,
                'cash-calc__change--short': !isEnough && cashReceived > 0,
            }"
        >
            <p class="cash-calc__change-label">
                <template v-if="cashReceived === 0">Masukkan nominal uang</template>
                <template v-else-if="isEnough">Kembalian</template>
                <template v-else>Kurang</template>
            </p>
            <p class="cash-calc__change-amount">
                {{ cashReceived > 0 ? formatRupiah(Math.abs(changeAmount)) : '—' }}
            </p>
        </div>
    </div>
</template>

<style scoped>
.cash-calc {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* ── Input ── */
.cash-calc__label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #5c6c7a;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 6px;
}

.cash-calc__input-wrap {
    display: flex;
    align-items: center;
    border: 1.5px solid #c1ccd6;
    border-radius: 10px;
    background: #ffffff;
    overflow: hidden;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.cash-calc__input-wrap:focus-within {
    border-color: #00684a;
    box-shadow: 0 0 0 3px rgba(0, 104, 74, 0.1);
}

.cash-calc__prefix {
    padding: 0 12px;
    font-size: 14px;
    font-weight: 600;
    color: #7c8c9a;
    border-right: 1px solid #eceff1;
    height: 44px;
    display: flex;
    align-items: center;
    background: #f9fbfa;
    flex-shrink: 0;
}

.cash-calc__input {
    flex: 1;
    height: 44px;
    padding: 0 12px;
    border: none;
    outline: none;
    font-size: 20px;
    font-weight: 700;
    color: #001e2b;
    background: transparent;
    text-align: right;
    -moz-appearance: textfield;
}

.cash-calc__input::-webkit-outer-spin-button,
.cash-calc__input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.cash-calc__input::placeholder {
    color: #c1ccd6;
    font-weight: 400;
}

/* ── Quick amounts ── */
.cash-calc__quick-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.quick-btn {
    height: 34px;
    padding: 0 14px;
    border-radius: 9999px;
    border: 1.5px solid #c1ccd6;
    background: #ffffff;
    font-size: 12px;
    font-weight: 500;
    color: #3d4f5b;
    cursor: pointer;
    transition: all 0.12s ease;
}

.quick-btn:hover {
    border-color: #001e2b;
    background: #001e2b;
    color: #00ed64;
}

.quick-btn--active {
    border-color: #001e2b;
    background: #001e2b;
    color: #00ed64;
}

.quick-btn--exact {
    border-color: #00684a;
    color: #00684a;
    background: #e3fcef;
}

.quick-btn--exact:hover,
.quick-btn--exact.quick-btn--active {
    background: #00684a;
    color: #ffffff;
    border-color: #00684a;
}

/* ── Change display ── */
.cash-calc__change {
    border-radius: 10px;
    padding: 12px 16px;
    text-align: center;
    background: #f4f7f6;
    border: 1.5px solid #e1e5e8;
    transition: all 0.2s ease;
}

.cash-calc__change--enough {
    background: #e3fcef;
    border-color: #86efac;
}

.cash-calc__change--short {
    background: #fff1f2;
    border-color: #fecdd3;
}

.cash-calc__change-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #7c8c9a;
    margin-bottom: 4px;
}

.cash-calc__change--enough .cash-calc__change-label {
    color: #16a34a;
}

.cash-calc__change--short .cash-calc__change-label {
    color: #dc2626;
}

.cash-calc__change-amount {
    font-size: 24px;
    font-weight: 700;
    color: #001e2b;
    letter-spacing: -0.5px;
    line-height: 1.1;
}

.cash-calc__change--enough .cash-calc__change-amount {
    color: #15803d;
}

.cash-calc__change--short .cash-calc__change-amount {
    color: #dc2626;
}
</style>
