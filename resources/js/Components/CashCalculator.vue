<script setup>
import { ref, computed, watch } from 'vue';
import { Input } from '@/Components/ui/input';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import { useFormatCurrency } from '@/composables/useFormatCurrency';

const props = defineProps({
    totalAmount: { type: Number, required: true },
});

const emit = defineEmits(['update:cashReceived']);
const { formatRupiah } = useFormatCurrency();

const cashReceived = ref(0);

const changeAmount = computed(() => {
    return cashReceived.value - props.totalAmount;
});

const isEnough = computed(() => cashReceived.value >= props.totalAmount);

// Quick amount buttons
const quickAmounts = computed(() => {
    const total = props.totalAmount;
    const amounts = [];
    
    // Suggest rounded amounts above total
    const base = Math.ceil(total / 10000) * 10000;
    if (base >= total) amounts.push(base);
    
    amounts.push(50000, 100000, 150000, 200000);
    
    // Remove duplicates and sort
    return [...new Set(amounts)]
        .filter(a => a >= total)
        .sort((a, b) => a - b)
        .slice(0, 4);
});

const setQuickAmount = (amount) => {
    cashReceived.value = amount;
};

// Uang pas
const setExactAmount = () => {
    cashReceived.value = props.totalAmount;
};

watch(cashReceived, (val) => {
    emit('update:cashReceived', val);
});
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="cash_received" class="text-sm text-slate-500 font-medium block">Uang Diterima</Label>
            <Input 
                id="cash_received" 
                v-model.number="cashReceived" 
                type="number" 
                min="0" 
                step="1000"
                class="text-xl font-bold text-center mt-2 h-12" 
                autofocus 
            />
        </div>

        <!-- Quick Amount Buttons -->
        <div class="grid grid-cols-2 gap-2">
            <Button variant="outline" size="sm" class="h-10 border-slate-300" @click="setExactAmount">
                Uang Pas
            </Button>
            <Button 
                v-for="amount in quickAmounts" 
                :key="amount" 
                variant="outline" 
                size="sm"
                class="h-10 border-slate-300"
                @click="setQuickAmount(amount)"
            >
                {{ formatRupiah(amount) }}
            </Button>
        </div>

        <!-- Change Display -->
        <div 
            class="rounded-lg p-4 text-center transition-colors"
            :class="isEnough && cashReceived > 0 ? 'bg-green-50 border border-green-200' : (cashReceived > 0 ? 'bg-red-50 border border-red-200' : 'bg-slate-50 border border-slate-200')"
        >
            <p 
                class="text-sm font-medium mb-1" 
                :class="isEnough && cashReceived > 0 ? 'text-green-600' : (cashReceived > 0 ? 'text-red-600' : 'text-slate-500')"
            >
                {{ isEnough && cashReceived > 0 ? 'Kembalian' : (cashReceived > 0 ? 'Kurang' : 'Status Kembalian') }}
            </p>
            <p 
                class="text-3xl font-bold" 
                :class="isEnough && cashReceived > 0 ? 'text-green-700' : (cashReceived > 0 ? 'text-red-700' : 'text-slate-400')"
            >
                {{ cashReceived > 0 ? formatRupiah(Math.abs(changeAmount)) : 'Rp 0' }}
            </p>
        </div>
    </div>
</template>
