<script setup>
import { ref } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { useCart } from '@/composables/useCart';
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import { Trash2, Plus, Minus, MessageSquare, ShoppingBag } from 'lucide-vue-next';

const emit = defineEmits(['checkout']);

const { 
    items, 
    removeItem, 
    updateQuantity, 
    updateNotes, 
    clearCart, 
    getSubtotal, 
    total, 
    totalItems, 
    isEmpty 
} = useCart();

const { formatRupiah } = useFormatCurrency();

// State untuk melacak item mana yang input catatannya sedang terbuka
const showNotes = ref({});

const toggleNotes = (cartId) => {
    showNotes.value[cartId] = !showNotes.value[cartId];
};
</script>

<template>
    <div class="flex flex-col h-full bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2">
                <ShoppingBag class="h-5 w-5 text-slate-700" />
                <h2 class="font-semibold text-slate-900">Pesanan</h2>
                <span class="bg-slate-900 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                    {{ totalItems }}
                </span>
            </div>
            <Button 
                v-if="!isEmpty" 
                variant="ghost" 
                size="sm" 
                class="text-red-500 hover:text-red-600 hover:bg-red-50 h-8 px-2"
                @click="clearCart"
            >
                Kosongkan
            </Button>
        </div>

        <!-- Body (Scrollable List) -->
        <div class="flex-1 overflow-y-auto p-4">
            <div v-if="isEmpty" class="h-full flex flex-col items-center justify-center text-slate-400 space-y-3">
                <ShoppingBag class="h-12 w-12 opacity-20" />
                <p>Keranjang masih kosong</p>
            </div>

            <div v-else class="space-y-4">
                <div v-for="item in items" :key="item.cartId" class="group relative flex flex-col gap-2 p-3 rounded-lg border border-slate-100 bg-white shadow-sm hover:border-slate-200 transition-colors">
                    
                    <div class="flex justify-between items-start gap-2">
                        <!-- Product Info -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-medium text-sm text-slate-900 line-clamp-2 leading-tight">
                                {{ item.productName }}
                            </h3>
                            <p v-if="item.variantLabel" class="text-xs text-slate-500 mt-1 line-clamp-1">
                                {{ item.variantLabel }}
                            </p>
                            <p class="text-sm font-semibold text-slate-700 mt-1.5">
                                {{ formatRupiah(item.price) }}
                            </p>
                        </div>

                        <!-- Actions (Delete & Note toggle) -->
                        <div class="flex flex-col items-end gap-1">
                            <Button 
                                variant="ghost" 
                                size="sm" 
                                class="h-6 w-6 p-0 text-slate-400 hover:text-red-500"
                                @click="removeItem(item.cartId)"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </Button>
                            <Button 
                                variant="ghost" 
                                size="sm" 
                                class="h-6 w-6 p-0"
                                :class="showNotes[item.cartId] || item.notes ? 'text-blue-500' : 'text-slate-400 hover:text-blue-500'"
                                @click="toggleNotes(item.cartId)"
                            >
                                <MessageSquare class="h-3.5 w-3.5" />
                            </Button>
                        </div>
                    </div>

                    <!-- Quantity Control & Subtotal -->
                    <div class="flex items-center justify-between mt-1 pt-2 border-t border-slate-50">
                        <div class="flex items-center bg-slate-100 rounded-md border border-slate-200">
                            <button 
                                class="h-7 w-7 flex items-center justify-center text-slate-600 hover:bg-slate-200 rounded-l-md transition-colors"
                                @click="updateQuantity(item.cartId, item.quantity - 1)"
                            >
                                <Minus class="h-3 w-3" />
                            </button>
                            <input 
                                type="number" 
                                class="h-7 w-10 bg-transparent border-none text-center text-sm font-medium focus:ring-0 p-0"
                                :value="item.quantity"
                                @change="e => updateQuantity(item.cartId, parseInt(e.target.value) || 1)"
                            />
                            <button 
                                class="h-7 w-7 flex items-center justify-center text-slate-600 hover:bg-slate-200 rounded-r-md transition-colors"
                                @click="updateQuantity(item.cartId, item.quantity + 1)"
                            >
                                <Plus class="h-3 w-3" />
                            </button>
                        </div>
                        <p class="text-sm font-bold text-slate-900">
                            {{ formatRupiah(getSubtotal(item.cartId)) }}
                        </p>
                    </div>

                    <!-- Notes Input -->
                    <div v-if="showNotes[item.cartId] || item.notes" class="mt-1">
                        <Input 
                            type="text" 
                            placeholder="Catatan pesanan..." 
                            class="h-8 text-xs bg-slate-50"
                            :value="item.notes"
                            @input="e => updateNotes(item.cartId, e.target.value)"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Footer -->
        <div class="border-t border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-slate-500">Total Tagihan</span>
                <span class="text-xl font-bold text-slate-900">{{ formatRupiah(total) }}</span>
            </div>
            <Button 
                class="w-full h-12 text-base font-semibold" 
                :disabled="isEmpty"
                @click="$emit('checkout')"
            >
                Bayar Sekarang
            </Button>
        </div>
    </div>
</template>
