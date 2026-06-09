<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductGrid from '@/Components/ProductGrid.vue';
import CartPanel from '@/Components/CartPanel.vue';
import VariantPickerModal from '@/Components/VariantPickerModal.vue';
import { ref } from 'vue';

const props = defineProps({
    products: Array,
    categories: Array,
});

// Cart state akan dikelola oleh useCart composable (T031)
// Untuk saat ini, placeholder
const cartItems = ref([]);

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
    // Akan diimplementasikan di T031
    console.log('Add direct to cart:', product);
};

const addToCartFromModal = (payload) => {
    // Akan diimplementasikan di T031
    console.log('Add to cart from modal:', payload);
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
                <CartPanel :items="cartItems" />
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
