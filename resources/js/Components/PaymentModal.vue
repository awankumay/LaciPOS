<script setup>
import { ref, watch, computed } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import { Banknote, CreditCard, Smartphone } from 'lucide-vue-next';

const props = defineProps({
    open: { type: Boolean, default: false },
    total: { type: Number, default: 0 },
});

const emit = defineEmits(['close', 'confirm-payment']);
const { formatRupiah } = useFormatCurrency();

const paymentMethod = ref(null);
const paymentProvider = ref('');

// Constants for providers
const BANK_PROVIDERS = ['BCA', 'Mandiri', 'BNI', 'BRI', 'BSI', 'Lainnya'];
const EWALLET_PROVIDERS = ['GoPay', 'OVO', 'DANA', 'ShopeePay', 'LinkAja', 'Lainnya'];

// Reset state when modal opens
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        paymentMethod.value = null;
        paymentProvider.value = '';
    }
});

const selectMethod = (method) => {
    paymentMethod.value = method;
    paymentProvider.value = '';
};

// Validasi form sebelum confirm
const canConfirm = computed(() => {
    if (!paymentMethod.value) return false;
    
    if (paymentMethod.value === 'bank_transfer' || paymentMethod.value === 'e_wallet') {
        return paymentProvider.value.length > 0;
    }
    
    // Untuk tunai selalu true di T033 (T034 akan implement logic cash)
    return true;
});

const handleConfirm = () => {
    if (!canConfirm.value) return;

    emit('confirm-payment', {
        payment_method: paymentMethod.value,
        payment_provider: paymentMethod.value === 'cash' ? null : paymentProvider.value,
        cash_received: paymentMethod.value === 'cash' ? props.total : null, // placeholder until T034
        change_amount: paymentMethod.value === 'cash' ? 0 : null, // placeholder until T034
    });
    
    emit('close');
};
</script>

<template>
    <Dialog :open="open" @update:open="$emit('close')">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle class="text-center text-xl">Pembayaran</DialogTitle>
            </DialogHeader>

            <div class="space-y-6 py-4">
                <!-- Total Display -->
                <div class="rounded-lg bg-slate-50 p-4 text-center">
                    <p class="text-sm font-medium text-slate-500 mb-1">Total Tagihan</p>
                    <p class="text-3xl font-bold text-slate-900">{{ formatRupiah(total) }}</p>
                </div>

                <!-- Payment Methods -->
                <div class="space-y-3">
                    <Label class="text-sm text-slate-500 font-medium block">Metode Pembayaran</Label>
                    <div class="grid grid-cols-3 gap-3">
                        <!-- Tunai -->
                        <button
                            type="button"
                            @click="selectMethod('cash')"
                            class="flex flex-col items-center justify-center gap-2 p-3 rounded-xl border-2 transition-all"
                            :class="paymentMethod === 'cash' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'"
                        >
                            <Banknote class="h-6 w-6" />
                            <span class="text-xs font-medium">Tunai</span>
                        </button>
                        
                        <!-- Transfer Bank -->
                        <button
                            type="button"
                            @click="selectMethod('bank_transfer')"
                            class="flex flex-col items-center justify-center gap-2 p-3 rounded-xl border-2 transition-all"
                            :class="paymentMethod === 'bank_transfer' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'"
                        >
                            <CreditCard class="h-6 w-6" />
                            <span class="text-xs font-medium">Transfer</span>
                        </button>
                        
                        <!-- E-Wallet -->
                        <button
                            type="button"
                            @click="selectMethod('e_wallet')"
                            class="flex flex-col items-center justify-center gap-2 p-3 rounded-xl border-2 transition-all"
                            :class="paymentMethod === 'e_wallet' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'"
                        >
                            <Smartphone class="h-6 w-6" />
                            <span class="text-xs font-medium">E-Wallet</span>
                        </button>
                    </div>
                </div>

                <!-- Method Details -->
                <div v-if="paymentMethod === 'cash'" class="p-4 border border-slate-200 rounded-lg bg-slate-50/50">
                    <p class="text-sm text-slate-500 text-center">
                        Modul kalkulator kembalian tunai akan hadir di T034.
                    </p>
                </div>
                
                <div v-if="paymentMethod === 'bank_transfer'" class="space-y-3 animate-in fade-in slide-in-from-top-2 duration-200">
                    <Label class="text-sm font-medium text-slate-700">Pilih Bank</Label>
                    <select 
                        v-model="paymentProvider"
                        class="flex h-10 w-full items-center justify-between rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2"
                    >
                        <option value="" disabled>-- Pilih Bank --</option>
                        <option v-for="bank in BANK_PROVIDERS" :key="bank" :value="bank">{{ bank }}</option>
                    </select>
                </div>
                
                <div v-if="paymentMethod === 'e_wallet'" class="space-y-3 animate-in fade-in slide-in-from-top-2 duration-200">
                    <Label class="text-sm font-medium text-slate-700">Pilih E-Wallet</Label>
                    <select 
                        v-model="paymentProvider"
                        class="flex h-10 w-full items-center justify-between rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2"
                    >
                        <option value="" disabled>-- Pilih E-Wallet --</option>
                        <option v-for="wallet in EWALLET_PROVIDERS" :key="wallet" :value="wallet">{{ wallet }}</option>
                    </select>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" class="w-full" @click="$emit('close')">Batal</Button>
                <Button class="w-full" :disabled="!canConfirm" @click="handleConfirm">
                    Konfirmasi Pembayaran
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
