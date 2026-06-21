<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::latest()->get();
        return Inertia::render('Settings/PaymentMethods', [
            'paymentMethods' => $paymentMethods
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:bank_transfer,e_wallet,qris',
            'account_details' => 'nullable|string|max:255',
            'admin_fee_percentage' => 'required|numeric|min:0|max:100',
            'min_amount_for_fee' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        PaymentMethod::create($validated);

        return back()->with('success', 'Metode pembayaran berhasil ditambahkan.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->category === 'cash') {
            // Cash only allows toggling active state
            $paymentMethod->update([
                'is_active' => $request->boolean('is_active')
            ]);
            return back()->with('success', 'Status metode tunai berhasil diperbarui.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:bank_transfer,e_wallet,qris',
            'account_details' => 'nullable|string|max:255',
            'admin_fee_percentage' => 'required|numeric|min:0|max:100',
            'min_amount_for_fee' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $paymentMethod->update($validated);

        return back()->with('success', 'Metode pembayaran berhasil diperbarui.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->category === 'cash') {
            return back()->with('error', 'Metode pembayaran tunai tidak dapat dihapus.');
        }

        $paymentMethod->delete();
        return back()->with('success', 'Metode pembayaran berhasil dihapus.');
    }
}
