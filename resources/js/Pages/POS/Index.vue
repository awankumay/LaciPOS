<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductGrid from '@/Components/ProductGrid.vue';
import CartPanel from '@/Components/CartPanel.vue';
import VariantPickerModal from '@/Components/VariantPickerModal.vue';
import PaymentModal from '@/Components/PaymentModal.vue';
import { ref } from 'vue';
import { useCart } from '@/composables/useCart';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    products: Array,
    categories: Array,
});

const { items, addItem, total, clearCart } = useCart();

const showVariantModal = ref(false);
const selectedProduct = ref(null);

const showPaymentModal = ref(false);

const handleProductClick = (product) => {
    if (product.has_variants) {
        selectedProduct.value = product;
        showVariantModal.value = true;
    } else {
        // Langsung tambah ke cart tanpa modal
        addToCartDirect(product);
    }
};

const addToCartDirect = (product) => {
    addItem({
        productId: product.id,
        productName: product.name,
        variantLabel: null,
        price: Number(product.price),
        cogs: Number(product.cogs),
        photoUrl: product.photo_url,
    });
};

const addToCartFromModal = (data) => {
    addItem({
        productId: data.product.id,
        productName: data.product.name,
        variantLabel: data.variantLabel,
        price: data.finalPrice,
        cogs: data.finalCogs,
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
    <AppLayout title="Kasir (POS)">
        <div class="flex gap-4 h-[calc(100vh-80px)]">
            <!-- Panel Kiri: Grid Produk (60%) -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <ProductGrid
                    :products="products"
                    :categories="categories"
                    @add-to-cart="handleProductClick"
                />
            </div>

            <!-- Panel Kanan: Keranjang (40%) -->
            <div class="w-[380px] flex-shrink-0">
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
    </AppLayout>
</template>
