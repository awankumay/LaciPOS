<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, \App\Services\PrintService $printService)
    {
        $validated = $request->validated();
        $items = $validated['items'];

        // Hitung total
        $totalAmount = collect($items)->sum(fn ($item) => $item['price'] * $item['quantity']);

        // Validasi uang cukup untuk cash
        if ($validated['payment_method'] === 'cash') {
            if ($validated['cash_received'] < $totalAmount) {
                return back()->with('error', 'Uang yang diterima kurang dari total.');
            }
        }

        $order = DB::transaction(function () use ($validated, $items, $totalAmount, $request) {
            // Buat order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $request->user()->id,
                'status' => 'pending', // Akan diubah ke completed setelah items dibuat
                'payment_method' => $validated['payment_method'],
                'payment_provider' => $validated['payment_provider'] ?? null,
                'total_amount' => $totalAmount,
                'cash_received' => $validated['cash_received'] ?? null,
                'change_amount' => $validated['payment_method'] === 'cash'
                    ? ($validated['cash_received'] - $totalAmount) : null,
            ]);

            // Buat order items dengan PRICE SNAPSHOT
            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['productId'],
                    'product_name_snapshot' => $item['productName'],
                    'snapshot_cogs' => $item['cogs'],
                    'snapshot_price' => $item['price'],
                    'variant_label' => $item['variantLabel'] ?? null,
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Update status ke completed (trigger observer untuk kurangi stok)
            $order->update(['status' => 'completed']);

            return $order;
        });

        $redirect = redirect()->route('order.success', $order->id)->with('success', 'Transaksi berhasil!');

        // Auto print jika setting aktif
        $storeProfile = \App\Models\StoreProfile::getProfile();
        if ($storeProfile && $storeProfile->auto_print) {
            $printResult = $printService->printReceipt($order);
            if (!$printResult['success']) {
                $redirect->with('warning', $printResult['message']);
            }
        }

        return $redirect;
    }

    public function success(Order $order)
    {
        $order->load(['items', 'user']);

        return Inertia::render('POS/Success', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'payment_provider' => $order->payment_provider,
                'total_amount' => $order->total_amount,
                'cash_received' => $order->cash_received,
                'change_amount' => $order->change_amount,
                'created_at' => $order->created_at->format('d M Y, H:i'),
                'cashier_name' => $order->user->name,
                'items' => $order->items->map(fn ($item) => [
                    'product_name' => $item->product_name_snapshot,
                    'variant_label' => $item->variant_label,
                    'quantity' => $item->quantity,
                    'price' => $item->snapshot_price,
                    'subtotal' => $item->subtotal,
                    'notes' => $item->notes,
                ]),
            ],
        ]);
    }
}
