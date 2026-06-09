<script setup>
import { ref, computed, watch } from 'vue';
import { Dialog, DialogContent } from '@/Components/ui/dialog';
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import { ShoppingCart, X } from 'lucide-vue-next';

const props = defineProps({
    open: { type: Boolean, default: false },
    product: { type: Object, default: null },
});

const emit = defineEmits(['close', 'add-to-cart']);
const { formatRupiah } = useFormatCurrency();

const selectedOptions = ref({});

watch(() => props.product, (product) => {
    if (!product) return;
    const defaults = {};
    product.variants.forEach(v => {
        if (v.options.length > 0) {
            defaults[v.id] = v.options[0];
        }
    });
    selectedOptions.value = defaults;
}, { immediate: true });

const calculatedPrice = computed(() => {
    if (!props.product) return 0;
    let total = Number(props.product.price);
    Object.values(selectedOptions.value).forEach(option => {
        total += Number(option.price_modifier || 0);
    });
    return total;
});

const calculatedCogs = computed(() => {
    if (!props.product) return 0;
    let total = Number(props.product.cogs);
    Object.values(selectedOptions.value).forEach(option => {
        total += Number(option.cogs_modifier || 0);
    });
    return total;
});

const variantLabel = computed(() => {
    return Object.values(selectedOptions.value)
        .map(opt => opt.label)
        .join(' · ');
});

const selectOption = (variantId, option) => {
    selectedOptions.value = { ...selectedOptions.value, [variantId]: option };
};

const handleAddToCart = () => {
    emit('add-to-cart', {
        product: props.product,
        selectedOptions: Object.values(selectedOptions.value),
        variantLabel: variantLabel.value,
        finalPrice: calculatedPrice.value,
        finalCogs: calculatedCogs.value,
    });
    emit('close');
};
</script>

<template>
    <Dialog :open="open" @update:open="$emit('close')">
        <DialogContent class="variant-modal-content">
            <!-- Header -->
            <div class="variant-modal__header">
                <div class="variant-modal__header-text">
                    <p class="variant-modal__header-sub">Pilih Varian</p>
                    <h2 class="variant-modal__header-title">{{ product?.name }}</h2>
                </div>
                <button class="variant-modal__close-btn" @click="$emit('close')">
                    <X class="variant-modal__close-icon" />
                </button>
            </div>

            <!-- Body -->
            <div class="variant-modal__body">
                <!-- Variant Groups -->
                <div
                    v-for="variant in product?.variants"
                    :key="variant.id"
                    class="variant-group"
                >
                    <label class="variant-group__label">{{ variant.name }}</label>
                    <div class="variant-options">
                        <button
                            v-for="option in variant.options"
                            :key="option.id"
                            class="option-btn"
                            :class="{ 'option-btn--active': selectedOptions[variant.id]?.id === option.id }"
                            @click="selectOption(variant.id, option)"
                        >
                            {{ option.label }}
                            <span v-if="option.price_modifier > 0" class="option-btn__modifier">
                                +{{ formatRupiah(option.price_modifier) }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Price Summary -->
                <div class="variant-price-summary">
                    <div>
                        <p class="variant-price-summary__label">Harga</p>
                        <p class="variant-price-summary__amount">{{ formatRupiah(calculatedPrice) }}</p>
                    </div>
                    <p v-if="variantLabel" class="variant-price-summary__variant-tag">
                        {{ variantLabel }}
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="variant-modal__footer">
                <button class="footer-btn footer-btn--cancel" @click="$emit('close')">
                    Batal
                </button>
                <button class="footer-btn footer-btn--add" @click="handleAddToCart">
                    <ShoppingCart class="footer-btn__icon" />
                    Tambah ke Keranjang
                </button>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
:deep(.variant-modal-content) {
    padding: 0 !important;
    border-radius: 12px !important;
    background-color: #ffffff !important;
    overflow: hidden !important;
    border: none !important;
    max-width: 420px !important;
    box-shadow: 0 16px 48px rgba(0, 30, 43, 0.16) !important;
}

/* ── Header ── */
.variant-modal__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 24px;
    background: #001e2b;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

.variant-modal__header-sub {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #00ed64;
    margin-bottom: 4px;
}

.variant-modal__header-title {
    font-size: 18px;
    font-weight: 600;
    color: #ffffff;
    line-height: 1.3;
}

.variant-modal__close-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 9999px;
    border: none;
    background: rgba(255, 255, 255, 0.1);
    cursor: pointer;
    color: rgba(255, 255, 255, 0.7);
    transition: background 0.15s ease;
    flex-shrink: 0;
    margin-top: 2px;
}

.variant-modal__close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.variant-modal__close-icon {
    width: 15px;
    height: 15px;
}

/* ── Body ── */
.variant-modal__body {
    padding: 24px;
    background: #f9fbfa;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.variant-group__label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #5c6c7a;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 8px;
}

.variant-options {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.option-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    height: 36px;
    padding: 0 16px;
    border-radius: 9999px;
    border: 1.5px solid #c1ccd6;
    background: #ffffff;
    font-size: 13px;
    font-weight: 500;
    color: #3d4f5b;
    cursor: pointer;
    transition: all 0.15s ease;
}

.option-btn:hover {
    border-color: #001e2b;
    color: #001e2b;
}

.option-btn--active {
    background: #001e2b;
    border-color: #001e2b;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 30, 43, 0.2);
}
.option-btn--active:hover {
    background: #001e2b;
    border-color: #001e2b;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 30, 43, 0.2);
}

.option-btn__modifier {
    font-size: 11px;
    opacity: 0.8;
}

/* ── Price Summary ── */
.variant-price-summary {
    background: #ffffff;
    border: 1.5px solid #e1e5e8;
    border-radius: 12px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.variant-price-summary__label {
    font-size: 11px;
    font-weight: 600;
    color: #7c8c9a;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 2px;
}

.variant-price-summary__amount {
    font-size: 24px;
    font-weight: 700;
    color: #001e2b;
    letter-spacing: -0.5px;
}

.variant-price-summary__variant-tag {
    font-size: 12px;
    font-weight: 500;
    color: #00684a;
    background: #e3fcef;
    padding: 4px 12px;
    border-radius: 9999px;
}

/* ── Footer ── */
.variant-modal__footer {
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
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
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

.footer-btn--add {
    background: #00ed64;
    color: #001e2b;
    box-shadow: 0 4px 14px rgba(0, 237, 100, 0.35);
}

.footer-btn--add:hover {
    background: #00b545;
    box-shadow: 0 6px 20px rgba(0, 237, 100, 0.5);
    transform: translateY(-1px);
}

.footer-btn__icon {
    width: 16px;
    height: 16px;
}
</style>
