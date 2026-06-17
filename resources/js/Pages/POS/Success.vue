<script setup>
import { Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import { CheckCircle2, Receipt, ArrowLeft, Calendar, User } from 'lucide-vue-next';

const props = defineProps({
    order: {
        type: Object,
        required: true
    }
});

const { formatRupiah } = useFormatCurrency();

const paymentMethodLabels = {
    cash: 'Tunai',
    bank_transfer: 'Transfer Bank',
    e_wallet: 'E-Wallet'
};

import { router, usePage } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import ToastNotification from '@/Components/ToastNotification.vue';
import { onMounted, ref } from 'vue';

const toast = useToast();
const page = usePage();
const isPrinting = ref(false);

const printReceipt = () => {
    isPrinting.value = true;
    router.post(`/orders/${props.order.id}/print`, {}, {
        preserveScroll: true,
        onFinish: () => {
            isPrinting.value = false;
        },
        onSuccess: (response) => {
            const flash = response.props.flash;
            if (flash?.success) {
                toast.success(flash.success, 'Berhasil');
            } else if (flash?.print_success) {
                toast.success(flash.print_success, 'Berhasil');
            } else if (flash?.error) {
                toast.error(flash.error, 'Gagal');
            } else if (flash?.warning) {
                toast.warning(flash.warning, 'Perhatian');
            }
        }
    });
};

onMounted(() => {
    if (page.props.flash?.warning) {
        toast.warning(page.props.flash.warning, 'Perhatian');
    }
    if (page.props.flash?.print_success) {
        toast.success(page.props.flash.print_success, 'Berhasil');
    }
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100">
            <!-- Header (Success Animation) -->
            <div class="bg-green-500 p-8 text-center text-white relative overflow-hidden">
                <div class="absolute inset-0 bg-green-600 opacity-20 pattern-diagonal-lines"></div>
                <div class="relative z-10 flex flex-col items-center">
                    <div class="h-20 w-20 bg-white rounded-full flex items-center justify-center mb-4 shadow-lg animate-in zoom-in duration-300">
                        <CheckCircle2 class="h-12 w-12 text-green-500" />
                    </div>
                    <h1 class="text-2xl font-bold mb-1">Transaksi Berhasil!</h1>
                    <p class="text-green-50 font-medium">No. {{ order.order_number }}</p>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- Meta Info -->
                <div class="flex items-center justify-between text-sm text-slate-500 mb-6 pb-6 border-b border-dashed border-slate-200">
                    <div class="flex items-center gap-1.5">
                        <Calendar class="h-4 w-4" />
                        <span>{{ order.created_at }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <User class="h-4 w-4" />
                        <span>{{ order.cashier_name }}</span>
                    </div>
                </div>

                <!-- Items List -->
                <div class="space-y-4 mb-6">
                    <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Rincian Pesanan</h3>
                    <div class="max-h-60 overflow-y-auto pr-2 space-y-3">
                        <div v-for="(item, index) in order.items" :key="index" class="flex justify-between items-start text-sm">
                            <div class="flex-1">
                                <p class="font-medium text-slate-800">{{ item.product_name }}</p>
                                <p v-if="item.variant_label" class="text-xs text-slate-500">{{ item.variant_label }}</p>
                                <p v-if="item.notes" class="text-xs text-slate-400 italic mt-0.5">Catatan: {{ item.notes }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ item.quantity }} x {{ formatRupiah(item.price) }}</p>
                            </div>
                            <p class="font-semibold text-slate-900">{{ formatRupiah(item.subtotal) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="bg-slate-50 rounded-xl p-4 space-y-3 border border-slate-100">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Metode Bayar</span>
                        <span class="font-medium text-slate-900">
                            {{ paymentMethodLabels[order.payment_method] }} 
                            <span v-if="order.payment_provider">({{ order.payment_provider }})</span>
                        </span>
                    </div>
                    
                    <div v-if="order.payment_method === 'cash'" class="flex justify-between text-sm">
                        <span class="text-slate-500">Tunai Diterima</span>
                        <span class="font-medium text-slate-900">{{ formatRupiah(order.cash_received) }}</span>
                    </div>
                    
                    <div v-if="order.payment_method === 'cash'" class="flex justify-between text-sm">
                        <span class="text-slate-500">Kembalian</span>
                        <span class="font-medium text-slate-900">{{ formatRupiah(order.change_amount) }}</span>
                    </div>

                    <div class="pt-3 mt-3 border-t border-slate-200 flex justify-between items-center">
                        <span class="font-bold text-slate-900">Total Pembayaran</span>
                        <span class="text-xl font-bold text-slate-900">{{ formatRupiah(order.total_amount) }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="p-6 bg-slate-50 border-t border-slate-100 grid grid-cols-2 gap-3">
                <Button variant="outline" class="w-full h-12 bg-white" as-child>
                    <Link href="/pos">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Transaksi Baru
                    </Link>
                </Button>
                <!-- Tombol Print (T041) -->
                <Button 
                    class="w-full h-12 bg-slate-900 text-white hover:bg-slate-800" 
                    @click="printReceipt"
                    :disabled="isPrinting"
                >
                    <Receipt class="mr-2 h-4 w-4" />
                    {{ isPrinting ? 'Mencetak...' : 'Cetak Struk' }}
                </Button>
            </div>
        </div>
        <!-- Render Toast Notification -->
        <ToastNotification />
    </div>
</template>

<style scoped>
.pattern-diagonal-lines {
    background-image: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 10px,
        rgba(0,0,0,0.1) 10px,
        rgba(0,0,0,0.1) 20px
    );
}
</style>
