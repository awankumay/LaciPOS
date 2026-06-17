<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductGrid from '@/Components/ProductGrid.vue';
import CartPanel from '@/Components/CartPanel.vue';
import VariantPickerModal from '@/Components/VariantPickerModal.vue';
import PaymentModal from '@/Components/PaymentModal.vue';
import { ref } from 'vue';
import { useCart } from '@/composables/useCart';
import { useToast } from '@/composables/useToast';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    products: Array,
    categories: Array,
});

const { items, addItem, total, clearCart, getQuantityByProductId } = useCart();
const toast = useToast();

const showVariantModal = ref(false);
const selectedProduct = ref(null);

const showPaymentModal = ref(false);

const handleProductClick = (product) => {
    if (product.has_variants) {
        selectedProduct.value = product;
        showVariantModal.value = true;
    } else {
        addToCartDirect(product);
    }
};

const addToCartDirect = (product) => {
    const currentQty = getQuantityByProductId(product.id);
    if (currentQty >= product.stock) {
        toast.error(`Stok produk '${product.name}' tidak mencukupi.`);
        return;
    }

    addItem({
        productId: product.id,
        productName: product.name,
        variantLabel: null,
        price: Number(product.price),
        cogs: Number(product.cogs),
        stock: product.stock,
        photoUrl: product.photo_url,
    });
};

const addToCartFromModal = (data) => {
    const currentQty = getQuantityByProductId(data.product.id);
    if (currentQty >= data.product.stock) {
        toast.error(`Stok produk '${data.product.name}' tidak mencukupi.`);
        return;
    }

    addItem({
        productId: data.product.id,
        productName: data.product.name,
        variantLabel: data.variantLabel,
        price: data.finalPrice,
        cogs: data.finalCogs,
        stock: data.product.stock,
        photoUrl: data.product.photo_url,
    });
};

const handleCheckout = () => {
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
        })),
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
                        @add-to-cart="handleProductClick"
                    />
                </div>

                <!-- Right Panel: Cart -->
                <div class="pos-cart-panel">
                    <CartPanel @checkout="handleCheckout" />
                </div>
            </div>

            <VariantPickerModal
                :open="showVariantModal"
                :product="selectedProduct"
                @close="showVariantModal = false"
                @add-to-cart="addToCartFromModal"
            />

            <PaymentModal
                :open="showPaymentModal"
                :total="total"
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
