import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export interface CartItem {
    cartId: string;         // Unique ID per entry di cart (product_id + variant combo)
    productId: string;
    productName: string;
    variantLabel: string | null;
    price: number;          // Harga jual final (termasuk modifier)
    cogs: number;           // Harga modal final (termasuk modifier)
    quantity: number;
    stock: number;          // Stok produk asli
    notes: string;
    photoUrl: string | null;
}

// State cart — menggunakan ref biasa (tidak persisted)
const items = ref<CartItem[]>([]);

export function useCart() {
    const page = usePage();
    const storeSettings = computed(() => page.props.storeSettings as any);

    /**
     * Tambah item ke cart.
     * Jika item dengan productId + variantLabel yang sama sudah ada, tambah quantity.
     */
    const addItem = (item: Omit<CartItem, 'cartId' | 'quantity' | 'notes'>): void => {
        const cartId = `${item.productId}_${item.variantLabel || 'default'}`;

        const existing = items.value.find(i => i.cartId === cartId);
        if (existing) {
            existing.quantity += 1;
        } else {
            items.value.push({
                ...item,
                cartId,
                quantity: 1,
                notes: '',
            });
        }
    };

    /**
     * Hapus item dari cart berdasarkan cartId.
     */
    const removeItem = (cartId: string): void => {
        items.value = items.value.filter(i => i.cartId !== cartId);
    };

    /**
     * Update quantity item. Jika quantity <= 0, hapus item.
     */
    const updateQuantity = (cartId: string, quantity: number): void => {
        if (quantity <= 0) {
            removeItem(cartId);
            return;
        }
        const item = items.value.find(i => i.cartId === cartId);
        if (item) {
            item.quantity = quantity;
        }
    };

    /**
     * Update catatan per item.
     */
    const updateNotes = (cartId: string, notes: string): void => {
        const item = items.value.find(i => i.cartId === cartId);
        if (item) {
            item.notes = notes;
        }
    };

    /**
     * Kosongkan seluruh cart.
     */
    const clearCart = (): void => {
        items.value = [];
    };

    /**
     * Hitung subtotal per item = price × quantity.
     */
    const getSubtotal = (cartId: string): number => {
        const item = items.value.find(i => i.cartId === cartId);
        return item ? item.price * item.quantity : 0;
    };

    /**
     * Subtotal seluruh cart.
     */
    const subtotal = computed<number>(() => {
        return items.value.reduce((sum, item) => sum + item.price * item.quantity, 0);
    });

    const taxType = computed<string>(() => storeSettings.value?.tax_type || 'percentage');
    const taxValue = computed<number>(() => Number(storeSettings.value?.tax_value) || 0);

    const taxAmount = computed<number>(() => {
        const settings = storeSettings.value;
        if (!settings?.tax_enabled) return 0;
        
        const value = taxValue.value;
        if (taxType.value === 'percentage') {
            return subtotal.value * (value / 100);
        }
        return value;
    });

    const serviceChargeType = computed<string>(() => storeSettings.value?.service_charge_type || 'percentage');
    const serviceChargeValue = computed<number>(() => Number(storeSettings.value?.service_charge_value) || 0);

    const serviceChargeAmount = computed<number>(() => {
        const settings = storeSettings.value;
        if (!settings?.service_charge_enabled) return 0;
        
        const value = serviceChargeValue.value;
        if (serviceChargeType.value === 'percentage') {
            return subtotal.value * (value / 100);
        }
        return value;
    });

    /**
     * Total akhir cart (subtotal + tax + service charge).
     */
    const finalTotal = computed<number>(() => {
        return subtotal.value + taxAmount.value + serviceChargeAmount.value;
    });

    /**
     * Total item di cart.
     */
    const totalItems = computed<number>(() => {
        return items.value.reduce((sum, item) => sum + item.quantity, 0);
    });

    /**
     * Apakah cart kosong?
     */
    const isEmpty = computed<boolean>(() => items.value.length === 0);

    const getQuantityByProductId = (productId: string): number => {
        return items.value.filter(i => i.productId === productId).reduce((sum, item) => sum + item.quantity, 0);
    };

    return {
        items,
        addItem,
        removeItem,
        updateQuantity,
        updateNotes,
        clearCart,
        getSubtotal,
        subtotal,
        taxType,
        taxValue,
        taxAmount,
        serviceChargeType,
        serviceChargeValue,
        serviceChargeAmount,
        finalTotal,
        totalItems,
        isEmpty,
        getQuantityByProductId,
    };
}
