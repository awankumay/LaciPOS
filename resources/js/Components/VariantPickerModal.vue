<script setup>
import { ref, computed, watch } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import { ShoppingCart } from 'lucide-vue-next';

const props = defineProps({
    open: { type: Boolean, default: false },
    product: { type: Object, default: null },
});

const emit = defineEmits(['close', 'add-to-cart']);
const { formatRupiah } = useFormatCurrency();

// Track selected option per variant
const selectedOptions = ref({});

// Reset selections when product changes
watch(() => props.product, (product) => {
    if (!product) return;
    const defaults = {};
    product.variants.forEach(v => {
        if (v.options.length > 0) {
            defaults[v.id] = v.options[0]; // Default: pilih opsi pertama
        }
    });
    selectedOptions.value = defaults;
}, { immediate: true });

// Hitung total harga (base + modifiers)
const calculatedPrice = computed(() => {
    if (!props.product) return 0;
    let total = Number(props.product.price);
    Object.values(selectedOptions.value).forEach(option => {
        total += Number(option.price_modifier || 0);
    });
    return total;
});

const calculatedCogs = computed(() => {
    if (!props.product) return 0;
    let total = Number(props.product.cogs);
    Object.values(selectedOptions.value).forEach(option => {
        total += Number(option.cogs_modifier || 0);
    });
    return total;
});

// Label gabungan dari semua opsi terpilih
const variantLabel = computed(() => {
    return Object.values(selectedOptions.value)
        .map(opt => opt.label)
        .join(' · ');
});

const selectOption = (variantId, option) => {
    selectedOptions.value = {
        ...selectedOptions.value,
        [variantId]: option,
    };
};

const handleAddToCart = () => {
    emit('add-to-cart', {
        product: props.product,
        selectedOptions: Object.values(selectedOptions.value),
        variantLabel: variantLabel.value,
        finalPrice: calculatedPrice.value,
        finalCogs: calculatedCogs.value,
    });
    emit('close');
};
</script>

<template>
    <Dialog :open="open" @update:open="$emit('close')">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>{{ product?.name }}</DialogTitle>
            </DialogHeader>

            <div class="space-y-4 py-2">
                <!-- Setiap jenis varian -->
                <div v-for="variant in product?.variants" :key="variant.id">
                    <label class="text-sm font-medium text-slate-700 mb-2 block">
                        {{ variant.name }}
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="option in variant.options"
                            :key="option.id"
                            @click="selectOption(variant.id, option)"
                            :class="[
                                'rounded-lg border px-4 py-2 text-sm font-medium transition-colors',
                                selectedOptions[variant.id]?.id === option.id
                                    ? 'border-slate-900 bg-slate-900 text-white'
                                    : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'
                            ]"
                        >
                            {{ option.label }}
                            <span v-if="option.price_modifier > 0" class="text-xs opacity-75 ml-1">
                                +{{ formatRupiah(option.price_modifier) }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Harga Final -->
                <div class="rounded-lg bg-slate-50 p-3 text-center">
                    <p class="text-sm text-slate-500">Harga</p>
                    <p class="text-2xl font-bold text-slate-900">{{ formatRupiah(calculatedPrice) }}</p>
                    <p v-if="variantLabel" class="text-xs text-slate-400 mt-1">{{ variantLabel }}</p>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="$emit('close')">Batal</Button>
                <Button @click="handleAddToCart">
                    <ShoppingCart class="mr-2 h-4 w-4" />
                    Tambah ke Keranjang
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
