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
        $user = new User([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);
        $user->role = 'cashier';
        $user->is_active = true;
        $user->save();

        return back()->with('success', 'Akun kasir berhasil ditambahkan.');
    }

    public function update(UpdateCashierRequest $request, User $cashier)
    {
        $cashier->name = $request->validated('name');
        $cashier->email = $request->validated('email');
        $cashier->is_active = $request->boolean('is_active');

        if ($request->filled('password')) {
            $cashier->password = Hash::make($request->validated('password'));
        }

        $cashier->save();
        return back()->with('success', 'Akun kasir berhasil diperbarui.');
    }
}
