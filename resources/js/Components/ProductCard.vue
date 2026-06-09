<script setup>
import { Package } from 'lucide-vue-next';
import { Badge } from '@/Components/ui/badge';
import { useFormatCurrency } from '@/composables/useFormatCurrency';

const props = defineProps({
    product: { type: Object, required: true },
});

const emit = defineEmits(['click']);
const { formatRupiah } = useFormatCurrency();
</script>

<template>
    <button
        @click="$emit('click', product)"
        class="flex flex-col rounded-lg border border-slate-200 bg-white p-3 text-left transition-all hover:border-slate-300 hover:shadow-md active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-slate-400"
        :class="{ 'opacity-50': product.stock <= 0 }"
        :disabled="product.stock <= 0"
    >
        <!-- Photo -->
        <div class="mb-2 aspect-square w-full overflow-hidden rounded-md bg-slate-100 relative">
            <img v-if="product.photo_url" :src="product.photo_url" :alt="product.name" class="h-full w-full object-cover" />
            <div v-else class="flex h-full w-full items-center justify-center">
                <Package class="h-8 w-8 text-slate-300" />
            </div>
            
            <div v-if="product.has_variants" class="absolute top-2 right-2 bg-slate-900/70 text-white text-[10px] px-1.5 py-0.5 rounded shadow-sm font-medium">
                Varian
            </div>
        </div>

        <!-- Name -->
        <p class="text-sm font-medium text-slate-900 line-clamp-2 mb-1 flex-1">{{ product.name }}</p>

        <!-- Price -->
        <p class="text-sm font-semibold text-slate-700">{{ formatRupiah(product.price) }}</p>

        <!-- Stock Info -->
        <div class="mt-1 h-5 flex items-end">
            <Badge v-if="product.stock <= 0" variant="destructive" class="text-xs px-1.5 py-0">Habis</Badge>
            <Badge v-else-if="product.stock <= product.min_stock_alert" variant="outline" class="text-[10px] px-1.5 py-0 text-orange-600 border-orange-200 bg-orange-50">
                Sisa {{ product.stock }}
            </Badge>
        </div>
    </button>
</template>
