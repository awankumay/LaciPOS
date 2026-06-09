<?php
namespace App\Observers;

use App\Models\Order;
use App\Services\StockService;

class OrderObserver
{
    public function __construct(private StockService $stockService) {}

    public function updating(Order $order): void
    {
        $originalStatus = $order->getOriginal('status');
        $newStatus = $order->status;

        // Stok berkurang saat status berubah ke completed
        if ($originalStatus !== 'completed' && $newStatus === 'completed') {
            $this->stockService->decreaseStockForOrder($order);
        }

        // Stok dikembalikan saat completed di-cancel
        if ($originalStatus === 'completed' && $newStatus === 'cancelled') {
            $this->stockService->restoreStockForOrder($order);
        }
    }
}
