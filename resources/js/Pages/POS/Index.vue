<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductGrid from '@/Components/ProductGrid.vue';
import CartPanel from '@/Components/CartPanel.vue';
import { ref } from 'vue';

const props = defineProps({
    products: Array,
    categories: Array,
});

// Cart state akan dikelola oleh useCart composable (T031)
// Untuk saat ini, placeholder
const cartItems = ref([]);

const addToCart = (product, selectedOptions) => {
    // Akan diimplementasikan di T031
    console.log('Add to cart:', product, selectedOptions);
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
                    @add-to-cart="addToCart"
                />
            </div>

            <!-- Panel Kanan: Keranjang (40%) -->
            <div class="w-[380px] flex-shrink-0">
                <CartPanel :items="cartItems" />
            </div>
        </div>
    </AppLayout>
</template>
