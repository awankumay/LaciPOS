<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockController extends Controller
{
    public function index(Product $product)
    {
        return Inertia::render('Products/StockAdjustment', [
            'product' => array_merge($product->toArray(), [
                'photo_url' => $product->photo_url,
            ]),
            'logs' => $product->stockLogs()->orderByDesc('created_at')->limit(50)->get(),
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:add,reduce'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'type.required' => 'Jenis penyesuaian wajib dipilih.',
            'quantity.required' => 'Jumlah wajib diisi.',
            'quantity.min' => 'Jumlah minimal 1.',
        ]);

        $change = $validated['type'] === 'add' ? $validated['quantity'] : -$validated['quantity'];
        $reason = $validated['type'] === 'add' ? 'manual_add' : 'manual_reduce';

        try {
            DB::transaction(function () use ($product, $change, $reason, $validated) {
                $freshProduct = Product::lockForUpdate()->findOrFail($product->id);

                if ($freshProduct->stock + $change < 0) {
                    throw new \DomainException('Stok tidak bisa kurang dari 0.');
                }

                $freshProduct->increment('stock', $change);

                $log = new StockLog();
                $log->change = $change;
                $log->fill([
                    'product_id' => $freshProduct->id,
                    'reason' => $reason,
                    'notes' => $validated['notes'],
                ]);
                $log->save();
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Stok berhasil disesuaikan.');
    }
}
