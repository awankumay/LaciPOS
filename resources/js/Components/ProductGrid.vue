<script setup>
import { ref, computed } from 'vue';
import { Input } from '@/Components/ui/input';
import { Search, SlidersHorizontal } from 'lucide-vue-next';
import ProductCard from '@/Components/ProductCard.vue';

const props = defineProps({
    products: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const emit = defineEmits(['add-to-cart']);

const searchQuery = ref('');
const selectedCategoryId = ref('');

const filteredProducts = computed(() => {
    let result = props.products;

    if (selectedCategoryId.value) {
        result = result.filter(p => p.category_id === selectedCategoryId.value);
    }

    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(p => p.name.toLowerCase().includes(query));
    }

    return result;
});

const selectCategory = (catId) => {
    selectedCategoryId.value = selectedCategoryId.value === catId ? '' : catId;
};
</script>

<template>
    <div class="product-grid-root">
        <!-- Top Bar: Search -->
        <div class="product-grid-topbar">
            <div class="search-wrapper">
                <Search class="search-icon" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari produk..."
                    class="search-input"
                />
            </div>
        </div>

        <!-- Category Filter Tabs -->
        <div class="category-tabs">
            <button
                class="cat-tab"
                :class="{ 'cat-tab--active': !selectedCategoryId }"
                @click="selectedCategoryId = ''"
            >
                Semua
            </button>
            <button
                v-for="cat in categories"
                :key="cat.id"
                class="cat-tab"
                :class="{ 'cat-tab--active': selectedCategoryId === cat.id }"
                @click="selectCategory(cat.id)"
            >
                {{ cat.name }}
            </button>
        </div>

        <!-- Product Grid Scrollable -->
        <div class="products-scroll">
            <div v-if="filteredProducts.length > 0" class="products-grid">
                <ProductCard
                    v-for="product in filteredProducts"
                    :key="product.id"
                    :product="product"
                    @click="$emit('add-to-cart', product)"
                />
            </div>

            <!-- Empty State -->
            <div v-else class="empty-state">
                <Search class="empty-state__icon" />
                <p class="empty-state__title">Produk tidak ditemukan</p>
                <p class="empty-state__sub">Coba kata kunci atau kategori yang berbeda</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ── Root container ── */
.product-grid-root {
    display: flex;
    flex-direction: column;
    height: 100%;
    background: #f4f7f6;
    border-radius: 16px;
    overflow: hidden;
}

/* ── Top bar ── */
.product-grid-topbar {
    padding: 16px 16px 0;
    flex-shrink: 0;
}

.search-wrapper {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    color: #7c8c9a;
    pointer-events: none;
    transition: color 0.15s ease;
}

.search-wrapper:focus-within .search-icon {
    color: #00684a;
}

.search-input {
    width: 100%;
    height: 48px;
    padding: 0 16px 0 44px;
    background: #ffffff;
    border: 1.5px solid #c1ccd6;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 400;
    color: #001e2b;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 1px 2px rgba(0, 30, 43, 0.04);
}

.search-input::placeholder {
    color: #a8b3bc;
}

.search-input:focus {
    border-color: #00684a;
    box-shadow: 0 0 0 3px rgba(0, 104, 74, 0.1);
}

/* ── Category tabs ── */
.category-tabs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    padding: 12px 16px;
    flex-shrink: 0;
}

.cat-tab {
    display: inline-flex;
    align-items: center;
    height: 34px;
    padding: 0 16px;
    border-radius: 9999px;
    font-size: 13px;
    font-weight: 500;
    border: 1.5px solid #c1ccd6;
    background: transparent;
    color: #5c6c7a;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
}

.cat-tab:hover {
    border-color: #001e2b;
    color: #001e2b;
    background: rgba(0, 30, 43, 0.04);
}

.cat-tab--active, .cat-tab--active:hover {
    background: #001e2b;
    color: #ffffff;
    border-color: #001e2b;
    box-shadow: 0 2px 8px rgba(0, 30, 43, 0.2);
}

/* ── Product scroll area ── */
.products-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 4px 16px 16px;
    scrollbar-width: thin;
    scrollbar-color: #c1ccd6 transparent;
}

.products-scroll::-webkit-scrollbar {
    width: 4px;
}

.products-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.products-scroll::-webkit-scrollbar-thumb {
    background: #c1ccd6;
    border-radius: 4px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
}

/* ── Empty State ── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 64px 24px;
    text-align: center;
    gap: 8px;
}

.empty-state__icon {
    width: 48px;
    height: 48px;
    color: #c1ccd6;
    margin-bottom: 8px;
}

.empty-state__title {
    font-size: 15px;
    font-weight: 600;
    color: #5c6c7a;
}

.empty-state__sub {
    font-size: 13px;
    color: #a8b3bc;
}
</style>
