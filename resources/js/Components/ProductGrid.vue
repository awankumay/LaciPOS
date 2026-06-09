<script setup>
import { ref, computed } from 'vue';
import { Input } from '@/Components/ui/input';
import { Button } from '@/Components/ui/button';
import { Search } from 'lucide-vue-next';
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

    // Filter by category
    if (selectedCategoryId.value) {
        result = result.filter(p => p.category_id === selectedCategoryId.value);
    }

    // Filter by search
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
    <div class="flex flex-col h-full">
        <!-- Search Bar -->
        <div class="mb-3">
            <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                <Input v-model="searchQuery" placeholder="Cari produk..." class="pl-10" />
            </div>
        </div>

        <!-- Category Filter -->
        <div class="mb-3 flex gap-2 flex-wrap">
            <Button
                size="sm"
                :variant="!selectedCategoryId ? 'default' : 'outline'"
                @click="selectedCategoryId = ''"
            >
                Semua
            </Button>
            <Button
                v-for="cat in categories"
                :key="cat.id"
                size="sm"
                :variant="selectedCategoryId === cat.id ? 'default' : 'outline'"
                @click="selectCategory(cat.id)"
            >
                {{ cat.name }}
            </Button>
        </div>

        <!-- Product Grid -->
        <div class="flex-1 overflow-y-auto pr-2 pb-4">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                <ProductCard
                    v-for="product in filteredProducts"
                    :key="product.id"
                    :product="product"
                    @click="$emit('add-to-cart', product)"
                />
            </div>

            <!-- Empty State -->
            <div v-if="filteredProducts.length === 0" class="flex flex-col items-center justify-center h-40 text-slate-400">
                <Search class="h-8 w-8 mb-2 opacity-50" />
                <p>Tidak ada produk yang ditemukan.</p>
            </div>
        </div>
    </div>
</template>
