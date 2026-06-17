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

        // Cek ketersediaan stok
        $requiredQuantities = [];
        foreach ($items as $item) {
            $productId = $item['productId'];
            $requiredQuantities[$productId] = ($requiredQuantities[$productId] ?? 0) + $item['quantity'];
        }

        foreach ($requiredQuantities as $productId => $quantity) {
            $product = \App\Models\Product::find($productId);
            if (!$product) {
                return back()->with('error', "Produk tidak valid.");
            }
            if ($product->stock < $quantity) {
                return back()->with('error', "Stok produk '{$product->name}' tidak mencukupi. Sisa stok: {$product->stock}.");
            }
        }

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
            } else {
                $redirect->with('print_success', $printResult['message']);
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

    public function index(\Illuminate\Http\Request $request)
    {
        $query = Order::with('user')->orderByDesc('created_at');

        if ($request->input('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->input('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        if ($request->input('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->input('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        if ($request->user()->isCashier()) {
            $query->where('user_id', $request->user()->id);
        }

        $orders = $query->paginate(20)->withQueryString();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['start_date', 'end_date', 'status', 'payment_method']),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['items', 'user']);

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }

    public function cancel(Order $order)
    {
        if ($order->status !== 'completed') {
            return back()->with('error', 'Hanya transaksi completed yang bisa dibatalkan.');
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Transaksi berhasil dibatalkan. Stok telah dikembalikan.');
    }
}
