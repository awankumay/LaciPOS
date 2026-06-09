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
                'total' => $order->total_amount,
                'cash_received' => $order->cash_received,
                'change' => $order->change_amount,
            ],
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->product_name_snapshot,
                'variant' => $item->variant_label,
                'quantity' => $item->quantity,
                'price' => $item->snapshot_price,
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
