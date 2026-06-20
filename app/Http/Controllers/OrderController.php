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
            
            // Cek kuota diskon: hanya hitung kuantitas item yang meminta diskon
            $discountedQuantity = collect($items)
                ->where('productId', $productId)
                ->filter(function ($item) {
                    return !empty($item['discount_type']);
                })
                ->sum('quantity');

            if ($discountedQuantity > 0) {
                if ($product->discount && $product->discount->quota !== null) {
                    $remainingQuota = max(0, $product->discount->quota - $product->discount->quota_used);
                    if ($discountedQuantity > $remainingQuota) {
                        return back()->with('error', "Pembelian {$product->name} dengan diskon gagal. Sisa kuota diskon hanya {$remainingQuota} item.");
                    }
                }
            }
        }

        // Hitung total dengan memperhitungkan diskon per item
        $subtotal = 0;
        foreach ($items as $item) {
            $price = $item['price'];
            $itemDiscountAmount = 0;
            if (!empty($item['discount_type']) && !empty($item['discount_value'])) {
                if ($item['discount_type'] === 'percentage') {
                    $itemDiscountAmount = $price * ($item['discount_value'] / 100);
                } else {
                    $itemDiscountAmount = $item['discount_value'];
                }
            }
            $subtotal += ($price - $itemDiscountAmount) * $item['quantity'];
        }

        // Hitung Diskon Keranjang
        $cartDiscountAmount = 0;
        if (!empty($validated['discount_type']) && !empty($validated['discount_value'])) {
            if ($validated['discount_type'] === 'percentage') {
                $cartDiscountAmount = $subtotal * ($validated['discount_value'] / 100);
            } else {
                $cartDiscountAmount = $validated['discount_value'];
            }
        }
        
        $subtotalAfterCartDiscount = max(0, $subtotal - $cartDiscountAmount);

        $storeProfile = \App\Models\StoreProfile::getProfile();

        $taxRate = 0;
        $taxType = null;
        $taxAmount = 0;
        if ($storeProfile && $storeProfile->tax_enabled) {
            $taxRate = $storeProfile->tax_value;
            $taxType = $storeProfile->tax_type;
            if ($taxType === 'percentage') {
                $taxAmount = $subtotalAfterCartDiscount * ($taxRate / 100);
            } else {
                $taxAmount = $taxRate;
            }
        }

        $serviceChargeRate = 0;
        $serviceChargeType = null;
        $serviceChargeAmount = 0;
        if ($storeProfile && $storeProfile->service_charge_enabled) {
            $serviceChargeRate = $storeProfile->service_charge_value;
            $serviceChargeType = $storeProfile->service_charge_type;
            if ($serviceChargeType === 'percentage') {
                $serviceChargeAmount = $subtotalAfterCartDiscount * ($serviceChargeRate / 100);
            } else {
                $serviceChargeAmount = $serviceChargeRate;
            }
        }

        $totalAmount = round($subtotalAfterCartDiscount + $taxAmount + $serviceChargeAmount);

        // Validasi uang cukup untuk cash
        if ($validated['payment_method'] === 'cash') {
            if ($validated['cash_received'] < $totalAmount) {
                return back()->with('error', 'Uang yang diterima kurang dari total.');
            }
        }

        $order = DB::transaction(function () use ($validated, $items, $subtotal, $cartDiscountAmount, $taxRate, $taxType, $taxAmount, $serviceChargeRate, $serviceChargeType, $serviceChargeAmount, $totalAmount, $request) {
            // Buat order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $request->user()->id,
                'status' => 'pending', // Akan diubah ke completed setelah items dibuat
                'payment_method' => $validated['payment_method'],
                'payment_provider' => $validated['payment_provider'] ?? null,
                'subtotal' => $subtotal,
                'discount_type' => $validated['discount_type'] ?? null,
                'discount_value' => $validated['discount_value'] ?? null,
                'discount_amount' => $cartDiscountAmount,
                'discount_note' => $validated['discount_note'] ?? null,
                'tax_rate' => $taxRate,
                'tax_type' => $taxType,
                'tax_amount' => $taxAmount,
                'service_charge_rate' => $serviceChargeRate,
                'service_charge_type' => $serviceChargeType,
                'service_charge_amount' => $serviceChargeAmount,
                'total_amount' => $totalAmount,
                'cash_received' => $validated['cash_received'] ?? null,
                'change_amount' => $validated['payment_method'] === 'cash'
                    ? ($validated['cash_received'] - $totalAmount) : null,
                'customer_name' => $validated['customer_name'] ?? null,
                'table_number' => $validated['table_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Buat order items dengan PRICE SNAPSHOT dan potong kuota diskon
            foreach ($items as $item) {
                $itemDiscountAmount = 0;
                if (!empty($item['discount_type']) && !empty($item['discount_value'])) {
                    if ($item['discount_type'] === 'percentage') {
                        $itemDiscountAmount = $item['price'] * ($item['discount_value'] / 100);
                    } else {
                        $itemDiscountAmount = $item['discount_value'];
                    }
                    
                    // Potong kuota diskon pada master diskon
                    $product = \App\Models\Product::with('discount')->find($item['productId']);
                    if ($product && $product->discount && $product->discount->quota !== null) {
                        $product->discount->increment('quota_used', $item['quantity']);
                    }
                }

                $order->items()->create([
                    'product_id' => $item['productId'],
                    'product_name_snapshot' => $item['productName'],
                    'snapshot_cogs' => $item['cogs'],
                    'snapshot_price' => $item['price'],
                    'variant_label' => $item['variantLabel'] ?? null,
                    'quantity' => $item['quantity'],
                    'snapshot_discount_type' => $item['discount_type'] ?? null,
                    'snapshot_discount_value' => $item['discount_value'] ?? null,
                    'snapshot_discount_amount' => $itemDiscountAmount,
                    'subtotal' => ($item['price'] - $itemDiscountAmount) * $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Update status ke completed (trigger observer untuk kurangi stok)
            $order->update(['status' => 'completed']);

            return $order;
        });

        $redirect = redirect()->route('order.success', $order->id)->with('success', 'Transaksi berhasil!');

        // Auto print jika setting aktif
        if ($storeProfile && $storeProfile->auto_print) {
            $printService->printReceipt($order);
            
            $redirect->with('print_success', 'Perintah cetak struk otomatis telah dikirim.');
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
                'subtotal' => $order->subtotal,
                'tax_type' => $order->tax_type,
                'tax_rate' => $order->tax_rate,
                'tax_amount' => $order->tax_amount,
                'service_charge_type' => $order->service_charge_type,
                'service_charge_rate' => $order->service_charge_rate,
                'service_charge_amount' => $order->service_charge_amount,
                'discount_type' => $order->discount_type,
                'discount_value' => $order->discount_value,
                'discount_amount' => $order->discount_amount,
                'discount_note' => $order->discount_note,
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
                    'discount_amount' => $item->snapshot_discount_amount,
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

        if ($request->input('search')) {
            $query->where('order_number', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->user()->isCashier()) {
            $query->where('user_id', $request->user()->id);
        }

        $orders = $query->paginate(20)->withQueryString();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['start_date', 'end_date', 'status', 'payment_method', 'search']),
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
