<script setup>
import { ref } from 'vue';
import { useCart } from '@/composables/useCart';
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import { useToast } from '@/composables/useToast';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Command, CommandGroup, CommandItem, CommandList } from '@/Components/ui/command';
import { Trash2, Plus, Minus, MessageSquare, ShoppingBag, X, Check, ChevronsUpDown } from 'lucide-vue-next';

const emit = defineEmits(['checkout']);

const {
    items,
    removeItem,
    updateQuantity,
    updateNotes,
    clearCart,
    getSubtotal,
    subtotal,
    taxType,
    taxValue,
    taxAmount,
    serviceChargeType,
    serviceChargeValue,
    serviceChargeAmount,
    cartDiscountType,
    cartDiscountValue,
    cartDiscountNote,
    cartDiscountAmount,
    subtotalAfterCartDiscount,
    finalTotal,
    totalItems,
    isEmpty,
    getQuantityByProductId,
    getDiscountedQuantityByProductId,
    addItem
} = useCart();

const { formatRupiah } = useFormatCurrency();
const toast = useToast();

const showNotes = ref({});
const showCartDiscount = ref(false);
const openDiscountType = ref(false);

const toggleCartDiscount = () => {
    showCartDiscount.value = !showCartDiscount.value;
    if (!showCartDiscount.value) {
        cartDiscountType.value = null;
        cartDiscountValue.value = null;
        cartDiscountNote.value = null;
    } else {
        cartDiscountType.value = 'percentage';
    }
};

const toggleNotes = (cartId) => {
    showNotes.value[cartId] = !showNotes.value[cartId];
};

const handleUpdateQuantity = (item, newQuantity) => {
    if (newQuantity <= 0) {
        updateQuantity(item.cartId, newQuantity);
        return;
    }
    
    const currentQtyForProduct = getQuantityByProductId(item.productId);
    const qtyOtherVariants = currentQtyForProduct - item.quantity;
    
    if (qtyOtherVariants + newQuantity > item.stock) {
        toast.error('Jumlah melebihi stok yang tersedia.');
        const maxAllowed = item.stock - qtyOtherVariants;
        if (maxAllowed > 0) {
            updateQuantity(item.cartId, maxAllowed);
        }
        item.quantity = maxAllowed;
        return;
    }

    // Check discount quota
    if (newQuantity > item.quantity && item.discountType && item.discountQuotaRemaining !== null) {
        const discountedQtyInCart = getDiscountedQuantityByProductId(item.productId);
        const addedQty = newQuantity - item.quantity;
        
        if (discountedQtyInCart + addedQty > item.discountQuotaRemaining) {
            const allowedDiscountQty = item.discountQuotaRemaining - discountedQtyInCart;
            
            if (allowedDiscountQty > 0) {
                updateQuantity(item.cartId, item.quantity + allowedDiscountQty);
            }
            
            const excessQty = addedQty - Math.max(0, allowedDiscountQty);
            if (excessQty > 0) {
                toast.warning(`Kuota diskon '${item.productName}' mencapai batas. Ditambahkan dengan harga normal.`);
                for (let i = 0; i < excessQty; i++) {
                    addItem({
                        productId: item.productId,
                        productName: item.productName,
                        variantLabel: item.variantLabel,
                        price: item.price,
                        cogs: item.cogs,
                        stock: item.stock,
                        photoUrl: item.photoUrl,
                        discountType: null,
                        discountValue: null,
                        discountQuotaRemaining: null,
                    });
                }
            }
            return;
        }
    }

    updateQuantity(item.cartId, newQuantity);
};
</script>

<template>
    <div class="cart-panel">
        <!-- ── Header ── -->
        <div class="cart-header">
            <div class="cart-header__left">
                <ShoppingBag class="cart-header__icon" />
                <span class="cart-header__title">Pesanan</span>
                <span class="cart-header__badge">{{ totalItems }}</span>
            </div>
            <button
                v-if="!isEmpty"
                class="cart-header__clear-btn"
                @click="clearCart"
                title="Kosongkan keranjang"
            >
                <X class="cart-header__clear-icon" />
                Kosongkan
            </button>
        </div>

        <!-- ── Items ── -->
        <div class="cart-body">
            <!-- Empty State -->
            <div v-if="isEmpty" class="cart-empty">
                <ShoppingBag class="cart-empty__icon" />
                <p class="cart-empty__title">Keranjang kosong</p>
                <p class="cart-empty__sub">Pilih produk untuk memulai</p>
            </div>

            <!-- Item List -->
            <div v-else class="cart-items">
                <div
                    v-for="item in items"
                    :key="item.cartId"
                    class="cart-item"
                >
                    <!-- Row 1: Info + Actions -->
                    <div class="cart-item__top">
                        <div class="cart-item__info">
                            <p class="cart-item__name">{{ item.productName }}</p>
                            <span v-if="item.variantLabel" class="cart-item__variant">
                                {{ item.variantLabel }}
                            </span>
                            <div class="flex items-center gap-1 mt-0.5">
                                <p class="cart-item__unit-price" :class="{ 'line-through text-[#a8b3bc]': item.discountValue }">{{ formatRupiah(item.price) }}</p>
                                <p v-if="item.discountValue" class="text-xs font-semibold text-[#001e2b]">{{ formatRupiah(Math.max(0, item.price - (item.discountType === 'percentage' ? item.price * (item.discountValue/100) : item.discountValue))) }}</p>
                                <span class="cart-item__unit-price">/ item</span>
                            </div>
                        </div>
                        <div class="cart-item__actions">
                            <button
                                class="cart-item__icon-btn cart-item__icon-btn--note"
                                :class="{ 'cart-item__icon-btn--note-active': showNotes[item.cartId] || item.notes }"
                                @click="toggleNotes(item.cartId)"
                                title="Tambah catatan"
                            >
                                <MessageSquare class="icon-xs" />
                            </button>
                            <button
                                class="cart-item__icon-btn cart-item__icon-btn--delete"
                                @click="removeItem(item.cartId)"
                                title="Hapus item"
                            >
                                <Trash2 class="icon-xs" />
                            </button>
                        </div>
                    </div>

                    <!-- Row 2: Qty Control + Subtotal -->
                    <div class="cart-item__bottom">
                        <div class="qty-control">
                            <button
                                class="qty-btn"
                                @click="handleUpdateQuantity(item, item.quantity - 1)"
                            >
                                <Minus class="icon-xs" />
                            </button>
                            <input
                                type="number"
                                class="qty-input"
                                :value="item.quantity"
                                @change="e => {
                                    handleUpdateQuantity(item, parseInt(e.target.value) || 1);
                                    e.target.value = item.quantity; // Force reset input if over stock
                                }"
                            />
                            <button
                                class="qty-btn"
                                @click="handleUpdateQuantity(item, item.quantity + 1)"
                            >
                                <Plus class="icon-xs" />
                            </button>
                        </div>
                        <p class="cart-item__subtotal">{{ formatRupiah(getSubtotal(item.cartId)) }}</p>
                    </div>

                    <!-- Notes (collapsible) -->
                    <div v-if="showNotes[item.cartId] || item.notes" class="cart-item__notes">
                        <input
                            type="text"
                            placeholder="Catatan untuk item ini..."
                            class="notes-input"
                            :value="item.notes"
                            @input="e => updateNotes(item.cartId, e.target.value)"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Footer / Checkout ── -->
        <div class="cart-footer">
            <!-- Diskon Transaksi -->
            <div class="cart-discount-section">
                <button 
                    v-if="!showCartDiscount" 
                    class="cart-discount-toggle" 
                    @click="toggleCartDiscount"
                    :disabled="isEmpty"
                >
                    <Plus class="icon-xs" /> Tambah Diskon Transaksi
                </button>
                <div v-else class="cart-discount-form">
                    <div class="cart-discount-header">
                        <span class="cart-discount-title">Diskon Transaksi</span>
                        <button class="cart-discount-close" @click="toggleCartDiscount"><X class="icon-xs" /></button>
                    </div>
                    <div class="cart-discount-inputs">
                        <Popover v-model:open="openDiscountType">
                            <PopoverTrigger as-child>
                                <button
                                    type="button"
                                    role="combobox"
                                    :aria-expanded="openDiscountType"
                                    class="cart-discount-type-btn"
                                >
                                    <span>{{ cartDiscountType === 'nominal' ? 'Rp' : '%' }}</span>
                                    <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50 text-[#7c8c9a]" />
                                </button>
                            </PopoverTrigger>
                            <PopoverContent class="w-[80px] p-0 bg-white" align="start">
                                <Command>
                                    <CommandList>
                                        <CommandGroup>
                                            <CommandItem
                                                value="percentage"
                                                @select="() => {
                                                    cartDiscountType = 'percentage';
                                                    openDiscountType = false;
                                                }"
                                                class="text-sm cursor-pointer"
                                            >
                                                %
                                                <Check
                                                    :class="['ml-auto h-4 w-4', cartDiscountType === 'percentage' ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                                />
                                            </CommandItem>
                                            <CommandItem
                                                value="nominal"
                                                @select="() => {
                                                    cartDiscountType = 'nominal';
                                                    openDiscountType = false;
                                                }"
                                                class="text-sm cursor-pointer"
                                            >
                                                Rp
                                                <Check
                                                    :class="['ml-auto h-4 w-4', cartDiscountType === 'nominal' ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                                />
                                            </CommandItem>
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                        <input v-model="cartDiscountValue" type="number" min="0" placeholder="0" class="cart-discount-input" />
                    </div>
                    <textarea v-model="cartDiscountNote" placeholder="Keterangan (opsional)" class="cart-discount-note"></textarea>
                </div>
            </div>

            <div class="cart-footer__total-row cart-footer__subtotal-row" v-if="taxAmount > 0 || serviceChargeAmount > 0 || cartDiscountAmount > 0">
                <span class="cart-footer__label">Subtotal</span>
                <span class="cart-footer__amount">{{ formatRupiah(subtotal) }}</span>
            </div>
            <div class="cart-footer__total-row cart-footer__tax-row" v-if="cartDiscountAmount > 0">
                <span class="cart-footer__label">Diskon Transaksi</span>
                <span class="cart-footer__amount text-red-500">-{{ formatRupiah(cartDiscountAmount) }}</span>
            </div>
            <div class="cart-footer__total-row cart-footer__tax-row" v-if="taxAmount > 0">
                <span class="cart-footer__label">
                    Pajak <span v-if="taxType === 'percentage'">({{ taxValue }}%)</span>
                </span>
                <span class="cart-footer__amount">{{ formatRupiah(taxAmount) }}</span>
            </div>
            <div class="cart-footer__total-row cart-footer__tax-row" v-if="serviceChargeAmount > 0">
                <span class="cart-footer__label">
                    Service Charge <span v-if="serviceChargeType === 'percentage'">({{ serviceChargeValue }}%)</span>
                </span>
                <span class="cart-footer__amount">{{ formatRupiah(serviceChargeAmount) }}</span>
            </div>
            <div class="cart-footer__total-row cart-footer__final-row">
                <span class="cart-footer__total-label">Total Tagihan</span>
                <span class="cart-footer__total-amount">{{ formatRupiah(finalTotal) }}</span>
            </div>
            <button
                class="cart-footer__checkout-btn"
                :class="{ 'cart-footer__checkout-btn--disabled': isEmpty }"
                :disabled="isEmpty"
                @click="$emit('checkout', finalTotal)"
            >
                Bayar Sekarang
            </button>
        </div>
    </div>
</template>

<style scoped>
/* ── Panel Root ── */
.cart-panel {
    display: flex;
    flex-direction: column;
    height: 100%;
    background: #ffffff;
    border-radius: 16px;
    border: 1.5px solid #e1e5e8;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 30, 43, 0.06);
}

/* ── Header ── */
.cart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #eceff1;
    background: #ffffff;
    flex-shrink: 0;
}

.cart-header__left {
    display: flex;
    align-items: center;
    gap: 8px;
}

.cart-header__icon {
    width: 18px;
    height: 18px;
    color: #001e2b;
}

.cart-header__title {
    font-size: 15px;
    font-weight: 600;
    color: #001e2b;
}

.cart-header__badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 6px;
    border-radius: 9999px;
    background: #001e2b;
    color: #00ed64;
    font-size: 11px;
    font-weight: 700;
}

.cart-header__clear-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 12px;
    border-radius: 9999px;
    border: 1px solid #e1e5e8;
    background: transparent;
    color: #7c8c9a;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
}

.cart-header__clear-btn:hover {
    background: #fff1f2;
    border-color: #fecdd3;
    color: #dc2626;
}

.cart-header__clear-icon {
    width: 12px;
    height: 12px;
}

/* ── Body ── */
.cart-body {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    background: #f9fbfa;
    scrollbar-width: thin;
    scrollbar-color: #c1ccd6 transparent;
    min-height: 0;
}

.cart-body::-webkit-scrollbar {
    width: 4px;
}

.cart-body::-webkit-scrollbar-thumb {
    background: #c1ccd6;
    border-radius: 4px;
}

/* ── Empty state ── */
.cart-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    min-height: 200px;
    gap: 8px;
    text-align: center;
}

.cart-empty__icon {
    width: 44px;
    height: 44px;
    color: #c1ccd6;
    margin-bottom: 4px;
}

.cart-empty__title {
    font-size: 14px;
    font-weight: 600;
    color: #7c8c9a;
}

.cart-empty__sub {
    font-size: 13px;
    color: #a8b3bc;
}

/* ── Items ── */
.cart-items {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.cart-item {
    background: #ffffff;
    border: 1.5px solid #eceff1;
    border-radius: 12px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: border-color 0.15s ease;
}

.cart-item:hover {
    border-color: #c1ccd6;
}

/* Row 1 */
.cart-item__top {
    display: flex;
    gap: 10px;
    align-items: flex-start;
}

.cart-item__info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.cart-item__name {
    font-size: 13px;
    font-weight: 600;
    color: #001e2b;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.cart-item__variant {
    display: inline-block;
    font-size: 11px;
    font-weight: 500;
    color: #5c6c7a;
    background: #f4f7f6;
    border-radius: 6px;
    padding: 1px 6px;
    width: fit-content;
}

.cart-item__unit-price {
    font-size: 12px;
    color: #7c8c9a;
}

.cart-item__actions {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
}

.cart-item__icon-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 9999px;
    border: none;
    cursor: pointer;
    transition: all 0.15s ease;
}

.cart-item__icon-btn--note {
    background: #f4f7f6;
    color: #7c8c9a;
}

.cart-item__icon-btn--note:hover {
    background: #e3fcef;
    color: #00684a;
}

.cart-item__icon-btn--note-active {
    background: #e3fcef;
    color: #00684a;
}

.cart-item__icon-btn--delete {
    background: #f4f7f6;
    color: #a8b3bc;
}

.cart-item__icon-btn--delete:hover {
    background: #fff1f2;
    color: #dc2626;
}

/* Row 2: qty + subtotal */
.cart-item__bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 8px;
    border-top: 1px solid #f4f7f6;
}

.qty-control {
    display: flex;
    align-items: center;
    border: 1.5px solid #e1e5e8;
    border-radius: 9999px;
    overflow: hidden;
    background: #f9fbfa;
}

.qty-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background: transparent;
    border: none;
    cursor: pointer;
    color: #3d4f5b;
    transition: background 0.12s ease;
}

.qty-btn:hover {
    background: #e1e5e8;
}

.qty-input {
    width: 38px;
    height: 30px;
    background: transparent;
    border: none;
    outline: none;
    text-align: center;
    font-size: 13px;
    font-weight: 600;
    color: #001e2b;
    -moz-appearance: textfield;
}

.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.cart-item__subtotal {
    font-size: 15px;
    font-weight: 700;
    color: #001e2b;
}

/* Notes */
.cart-item__notes {
    animation: slideDown 0.15s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}

.notes-input {
    width: 100%;
    height: 36px;
    padding: 0 12px;
    border: 1.5px solid #c1ccd6;
    border-radius: 8px;
    font-size: 13px;
    color: #001e2b;
    background: #ffffff;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.notes-input::placeholder {
    color: #a8b3bc;
}

.notes-input:focus {
    border-color: #00684a;
    box-shadow: 0 0 0 3px rgba(0, 104, 74, 0.1);
}

/* ── Footer ── */
.cart-footer {
    padding: 16px 20px 20px;
    border-top: 1px solid #eceff1;
    background: #ffffff;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.cart-footer__total-row {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
}

.cart-footer__label {
    font-size: 13px;
    font-weight: 500;
    color: #5c6c7a;
}

.cart-footer__amount {
    font-size: 13px;
    font-weight: 600;
    color: #001e2b;
}

.cart-footer__subtotal-row, .cart-footer__tax-row {
    margin-bottom: -4px;
}

.cart-footer__final-row {
    margin-top: 4px;
    border-top: 1px dashed #e1e5e8;
    padding-top: 8px;
}

.cart-footer__total-label {
    font-size: 12px;
    font-weight: 600;
    color: #7c8c9a;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

.cart-footer__total-amount {
    font-size: 24px;
    font-weight: 700;
    color: #001e2b;
    letter-spacing: -0.5px;
}

.cart-footer__checkout-btn {
    width: 100%;
    height: 52px;
    border-radius: 9999px;
    border: none;
    background: #00ed64;
    color: #001e2b;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 14px rgba(0, 237, 100, 0.35);
    letter-spacing: 0.1px;
}

.cart-footer__checkout-btn:hover {
    background: #00b545;
    box-shadow: 0 6px 20px rgba(0, 237, 100, 0.5);
    transform: translateY(-1px);
}

.cart-footer__checkout-btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(0, 237, 100, 0.3);
}

.cart-footer__checkout-btn--disabled {
    background: #eceff1;
    color: #a8b3bc;
    box-shadow: none;
    cursor: not-allowed;
}

.cart-footer__checkout-btn--disabled:hover {
    transform: none;
    box-shadow: none;
    background: #eceff1;
}

/* ── Shared icon sizes ── */
.icon-xs {
    width: 13px;
    height: 13px;
}

/* ── Cart Discount Form ── */
.cart-discount-section {
    margin-bottom: 4px;
}

.cart-discount-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: transparent;
    border: 1px dashed #c1ccd6;
    color: #5c6c7a;
    font-size: 12px;
    font-weight: 600;
    padding: 8px 12px;
    border-radius: 8px;
    cursor: pointer;
    width: 100%;
    justify-content: center;
    transition: all 0.15s ease;
}

.cart-discount-toggle:hover:not(:disabled) {
    background: #f4f7f6;
    border-color: #00684a;
    color: #00684a;
}

.cart-discount-form {
    background: #f9fbfa;
    border: 1.5px solid #e1e5e8;
    border-radius: 10px;
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.cart-discount-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-discount-title {
    font-size: 12px;
    font-weight: 600;
    color: #3d4f5b;
}

.cart-discount-close {
    background: transparent;
    border: none;
    color: #a8b3bc;
    cursor: pointer;
    padding: 2px;
}
.cart-discount-close:hover {
    color: #dc2626;
}

.cart-discount-inputs {
    display: flex;
    gap: 8px;
}

.cart-discount-type-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 60px;
    height: 32px;
    border: 1px solid #c1ccd6;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    outline: none;
    background: white;
    padding: 0 8px;
    cursor: pointer;
    transition: border-color 0.15s ease;
}
.cart-discount-type-btn:hover {
    border-color: #00684a;
}
.cart-discount-type-btn:focus-visible {
    border-color: #00684a;
    ring: 2px solid #00684a;
}

.cart-discount-input {
    flex: 1;
    height: 32px;
    border: 1px solid #c1ccd6;
    border-radius: 6px;
    padding: 0 10px;
    font-size: 13px;
    outline: none;
    width: 100%;
}
.cart-discount-input:focus {
    border-color: #00684a;
}

.cart-discount-note {
    width: 100%;
    min-height: 60px;
    border: 1px solid #c1ccd6;
    border-radius: 6px;
    padding: 8px 10px;
    font-size: 13px;
    font-family: inherit;
    outline: none;
    resize: vertical;
    box-sizing: border-box;
}
.cart-discount-note:focus {
    border-color: #00684a;
}
</style>
