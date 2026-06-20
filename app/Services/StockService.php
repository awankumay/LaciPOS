<?php
namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function decreaseStockForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if (!$product) continue;

                $product->decrement('stock', $item->quantity);

                StockLog::create([
                    'product_id' => $product->id,
                    'change' => -$item->quantity,
                    'reason' => 'sale',
                    'reference_id' => $order->id,
                    'notes' => "Order #{$order->order_number}",
                ]);
            }
        });
    }

    public function restoreStockForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if (!$product) continue;

                $product->increment('stock', $item->quantity);

                if ($item->snapshot_discount_type !== null && $product->discount_quota !== null) {
                    $product->decrement('discount_quota_used', $item->quantity);
                    if ($product->discount_quota_used < 0) {
                        $product->discount_quota_used = 0;
                        $product->save();
                    }
                }

                StockLog::create([
                    'product_id' => $product->id,
                    'change' => $item->quantity,
                    'reason' => 'cancellation',
                    'reference_id' => $order->id,
                    'notes' => "Pembatalan Order #{$order->order_number}",
                ]);
            }
        });
    }
}
