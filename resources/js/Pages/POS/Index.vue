<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductGrid from '@/Components/ProductGrid.vue';
import CartPanel from '@/Components/CartPanel.vue';
import VariantPickerModal from '@/Components/VariantPickerModal.vue';
import PaymentModal from '@/Components/PaymentModal.vue';
import { ref, nextTick } from 'vue';
import { useCart } from '@/composables/useCart';
import { useToast } from '@/composables/useToast';
import { router } from '@inertiajs/vue3';
import { ShoppingBag } from 'lucide-vue-next';

const props = defineProps({
    products: Array,
    categories: Array,
    next_order_number: String,
    payment_methods: Array,
});

const { items, addItem, finalTotal, clearCart, getQuantityByProductId, getDiscountedQuantityByProductId, cartDiscountType, cartDiscountValue, cartDiscountNote } = useCart();
const toast = useToast();

const showVariantModal = ref(false);
const selectedProduct = ref(null);

const showPaymentModal = ref(false);
const paymentTotal = ref(0);

// Fly-to-cart animation state
const flyingItems = ref([]);
const cartPanelRef = ref(null);

const triggerFlyAnimation = (event, product) => {
    // Walk up from target to find the product card button
    let trigger = event?.target;
    while (trigger && !trigger.classList.contains('product-card')) {
        trigger = trigger.parentElement;
    }
    if (!trigger) return;

    const fromRect = trigger.getBoundingClientRect();
    const cartEl = document.querySelector('.pos-cart-panel');
    if (!cartEl) return;
    const toRect = cartEl.getBoundingClientRect();

    const id = Date.now() + Math.random();
    const item = {
        id,
        photoUrl: product.photo_url || null,
        name: product.name,
        startX: fromRect.left + fromRect.width / 2,
        startY: fromRect.top + fromRect.height / 2,
        endX: toRect.left + 40,
        endY: toRect.top + 40,
    };

    flyingItems.value.push(item);
    setTimeout(() => {
        flyingItems.value = flyingItems.value.filter(i => i.id !== id);
    }, 700);
};

const handleProductClick = (product, event) => {
    if (product.has_variants) {
        selectedProduct.value = product;
        showVariantModal.value = true;
    } else {
        const added = addToCartDirect(product);
        if (added && event) triggerFlyAnimation(event, product);
    }
};

const addToCartDirect = (product) => {
    const currentQty = getQuantityByProductId(product.id);
    if (currentQty >= product.stock) {
        toast.error(`Stok produk '${product.name}' tidak mencukupi.`);
        return false;
    }

    let discountType = null;
    let discountValue = null;

    if (product.is_discount_active) {
        if (product.discount_quota_remaining !== null) {
            const discountedQtyInCart = getDiscountedQuantityByProductId(product.id);
            if (discountedQtyInCart < product.discount_quota_remaining) {
                discountType = product.discount_type;
                discountValue = product.discount_value;
            } else {
                toast.warning(`Kuota diskon '${product.name}' telah habis. Ditambahkan dengan harga normal.`);
            }
        } else {
            discountType = product.discount_type;
            discountValue = product.discount_value;
        }
    }

    addItem({
        productId: product.id,
        productName: product.name,
        variantLabel: null,
        price: Number(product.price),
        cogs: Number(product.cogs),
        stock: product.stock,
        photoUrl: product.photo_url,
        discountType: discountType,
        discountValue: discountValue,
        discountQuotaRemaining: product.discount_quota_remaining,
    });
    return true;
};

const addToCartFromModal = (data) => {
    const currentQty = getQuantityByProductId(data.product.id);
    if (currentQty >= data.product.stock) {
        toast.error(`Stok produk '${data.product.name}' tidak mencukupi.`);
        return;
    }

    let applyDiscount = false;
    let discountType = null;
    let discountValue = null;

    if (data.product.is_discount_active) {
        if (data.product.discount_quota_remaining !== null) {
            const discountedQtyInCart = getDiscountedQuantityByProductId(data.product.id);
            if (discountedQtyInCart < data.product.discount_quota_remaining) {
                applyDiscount = true;
                discountType = data.product.discount_type;
                discountValue = data.product.discount_value;
            } else {
                toast.warning(`Kuota diskon '${data.product.name}' telah habis. Ditambahkan dengan harga normal.`);
            }
        } else {
            applyDiscount = true;
            discountType = data.product.discount_type;
            discountValue = data.product.discount_value;
        }
    }

    addItem({
        productId: data.product.id,
        productName: data.product.name,
        variantLabel: data.variantLabel,
        price: data.finalPrice,
        cogs: data.finalCogs,
        stock: data.product.stock,
        photoUrl: data.product.photo_url,
        discountType: discountType,
        discountValue: discountValue,
        discountQuotaRemaining: data.product.discount_quota_remaining,
    });
};

const handleCheckout = (total) => {
    paymentTotal.value = total;
    showPaymentModal.value = true;
};

const handleConfirmPayment = (paymentData) => {
    router.post('/orders', {
        ...paymentData,
        items: items.value.map(item => ({
            productId: item.productId,
            productName: item.productName,
            price: item.price,
            cogs: item.cogs,
            quantity: item.quantity,
            variantLabel: item.variantLabel,
            notes: item.notes,
            discount_type: item.discountType,
            discount_value: item.discountValue,
        })),
        discount_type: cartDiscountType.value,
        discount_value: cartDiscountValue.value,
        discount_note: cartDiscountNote.value,
    }, {
        onSuccess: () => {
            clearCart();
            showPaymentModal.value = false;
        },
    });
};
</script>

<template>
    <!-- POS uses full-height layout without extra padding from AppLayout -->
    <AppLayout title="Kasir (POS)">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between w-full">
                <div>
                    <h1 class="text-lg font-semibold text-[#001e2b]">Kasir (POS)</h1>
                    <p class="text-xs text-[#7c8c9a]">Kelola transaksi penjualan dengan mudah</p>
                </div>
            </div>
        </template>

        <template #default>
            <div class="pos-wrapper">
                <!-- Left Panel: Product Grid -->
                <div class="pos-products-panel">
                    <ProductGrid
                        :products="products"
                        :categories="categories"
                        :next-order-number="next_order_number"
                        @add-to-cart="handleProductClick"
                    />
                </div>

                <!-- Right Panel: Cart -->
                <div class="pos-cart-panel">
                    <CartPanel @checkout="handleCheckout" />
                </div>
            </div>

            <!-- Fly-to-Cart Ghost Elements (rendered at body level via fixed positioning) -->
            <Teleport to="body">
                <div
                    v-for="item in flyingItems"
                    :key="item.id"
                    class="fly-ghost"
                    :style="{
                        '--from-x': item.startX + 'px',
                        '--from-y': item.startY + 'px',
                        '--to-x': item.endX + 'px',
                        '--to-y': item.endY + 'px',
                    }"
                >
                    <img v-if="item.photoUrl" :src="item.photoUrl" :alt="item.name" class="fly-ghost__img" />
                    <div v-else class="fly-ghost__fallback">
                        <ShoppingBag class="fly-ghost__icon" />
                    </div>
                </div>
            </Teleport>

            <VariantPickerModal
                :open="showVariantModal"
                :product="selectedProduct"
                @close="showVariantModal = false"
                @add-to-cart="addToCartFromModal"
            />

            <PaymentModal
                :open="showPaymentModal"
                :total="paymentTotal"
                :payment-methods="payment_methods"
                @close="showPaymentModal = false"
                @confirm-payment="handleConfirmPayment"
            />
        </template>
    </AppLayout>
</template>

<style scoped>
.pos-wrapper {
    display: flex;
    gap: 20px;
    /* Adjusted for AppLayout padding and the new header height */
    height: calc(100vh - 180px);
    min-height: 0;
}

.pos-products-panel {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.pos-cart-panel {
    width: 360px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    min-height: 0;
}
</style>

<style>
/* Fly-to-cart animation — must NOT be scoped so Teleport can access */
.fly-ghost {
    position: fixed;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    overflow: hidden;
    pointer-events: none;
    z-index: 9999;
    box-shadow: 0 8px 24px rgba(0, 30, 43, 0.25);
    border: 2px solid #00ed64;
    animation: fly-to-cart 0.65s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
}

.fly-ghost__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.fly-ghost__fallback {
    width: 100%;
    height: 100%;
    background: #001e2b;
    display: flex;
    align-items: center;
    justify-content: center;
}

.fly-ghost__icon {
    width: 22px;
    height: 22px;
    color: #00ed64;
}

@keyframes fly-to-cart {
    0% {
        left: calc(var(--from-x) - 24px);
        top: calc(var(--from-y) - 24px);
        opacity: 1;
        transform: scale(1);
    }
    60% {
        opacity: 1;
        transform: scale(0.85);
    }
    100% {
        left: calc(var(--to-x) - 24px);
        top: calc(var(--to-y) - 24px);
        opacity: 0;
        transform: scale(0.4);
    }
}
</style>
