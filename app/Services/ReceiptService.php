<?php
namespace App\Services;

use App\Models\Order;
use App\Models\StoreProfile;

class ReceiptService
{
    public function generateReceiptData(Order $order): array
    {
        $store = StoreProfile::getProfile();
        $order->load(['items', 'user']);

        return [
            'store' => [
                'name' => $store?->store_name ?? 'Toko',
                'address' => $store?->address,
                'phone' => $store?->phone,
                'logo_url' => $store?->logo_url,
                'receipt_footer' => $store?->receipt_footer,
            ],
            'order' => [
                'number' => $order->order_number,
                'date' => $order->created_at->format('d M Y, H:i'),
                'cashier' => $order->user->name,
                'payment_method' => $order->payment_method,
                'payment_provider' => $order->payment_provider,
                'subtotal' => $order->subtotal,
                'tax_rate' => $order->tax_rate,
                'tax_type' => $order->tax_type,
                'tax_amount' => $order->tax_amount,
                'service_charge_rate' => $order->service_charge_rate,
                'service_charge_type' => $order->service_charge_type,
                'service_charge_amount' => $order->service_charge_amount,
                'discount_type' => $order->discount_type,
                'discount_value' => $order->discount_value,
                'discount_amount' => $order->discount_amount,
                'discount_note' => $order->discount_note,
                'total' => $order->total_amount,
                'cash_received' => $order->cash_received,
                'change' => $order->change_amount,
                'customer_name' => $order->customer_name,
                'table_number' => $order->table_number,
                'notes' => $order->notes,
            ],
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->product_name_snapshot,
                'variant' => $item->variant_label,
                'quantity' => $item->quantity,
                'price' => $item->snapshot_price,
                'discount_amount' => $item->snapshot_discount_amount,
                'subtotal' => $item->subtotal,
            ])->toArray(),
        ];
    }

    public function renderReceiptHtml(Order $order, string $paperSize = '80mm'): string
    {
        $data = $this->generateReceiptData($order);
        return view('receipts.thermal', array_merge($data, ['paperSize' => $paperSize]))->render();
    }
}
