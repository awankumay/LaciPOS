<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductGrid from '@/Components/ProductGrid.vue';
import CartPanel from '@/Components/CartPanel.vue';
import VariantPickerModal from '@/Components/VariantPickerModal.vue';
import { ref } from 'vue';
import { useCart } from '@/composables/useCart';

const props = defineProps({
    products: Array,
    categories: Array,
});

const { addItem } = useCart();

const showVariantModal = ref(false);
const selectedProduct = ref(null);

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
    // Akan diimplementasikan di T033 (Payment Modal)
    console.log('Open payment modal');
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
    </AppLayout>
</template>
