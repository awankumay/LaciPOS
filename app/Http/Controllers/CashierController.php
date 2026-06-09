<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCashierRequest;
use App\Http\Requests\UpdateCashierRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class CashierController extends Controller
{
    public function index()
    {
        $cashiers = User::where('role', 'cashier')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'is_active', 'created_at']);

        return Inertia::render('Settings/Cashiers', [
            'cashiers' => $cashiers,
        ]);
    }

    public function store(StoreCashierRequest $request)
    {
        User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        return back()->with('success', 'Akun kasir berhasil ditambahkan.');
    }

    public function update(UpdateCashierRequest $request, User $cashier)
    {
        $data = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $cashier->update($data);
        return back()->with('success', 'Akun kasir berhasil diperbarui.');
    }

    public function toggleActive(User $cashier)
    {
        $cashier->update(['is_active' => !$cashier->is_active]);

        $status = $cashier->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun kasir berhasil {$status}.");
    }
}
