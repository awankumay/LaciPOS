<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Guest routes (hanya bisa diakses saat belum login)
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// Auth-only routes (tanpa onboarding guard — bisa diakses sebelum onboarding selesai)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Route onboarding TIDAK pakai middleware 'onboarding' (mencegah infinite redirect loop)
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
});

// Protected routes (auth + onboarding harus selesai)
Route::middleware(['auth', 'onboarding'])->group(function () {
    // Route khusus owner
    Route::middleware('role:owner')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard/Index');
        })->name('dashboard');

        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    
    // Product & Stock Routes
    Route::get('/products/{product}/stock', [App\Http\Controllers\StockController::class, 'index'])->name('stock.index');
    Route::post('/products/{product}/stock', [App\Http\Controllers\StockController::class, 'store'])->name('stock.store');
    Route::resource('products', ProductController::class)->except(['show']);

        // Route untuk reports, settings
        Route::get('/settings/printer', [App\Http\Controllers\PrinterSettingController::class, 'index'])->name('settings.printer.index');
        Route::post('/settings/printer', [App\Http\Controllers\PrinterSettingController::class, 'store'])->name('settings.printer.store');
    });

    // Route yang bisa diakses owner DAN cashier
    Route::middleware('role:owner,cashier')->group(function () {
        // Route POS akan ditambahkan di task T027
        Route::get('/pos', [App\Http\Controllers\POSController::class, 'index'])->name('pos.index');
        
        // Orders
        Route::post('/orders', [App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}/success', [App\Http\Controllers\OrderController::class, 'success'])->name('order.success');
        Route::post('/orders/{order}/print', [App\Http\Controllers\PrintController::class, 'print'])->name('order.print');
        
        // Route riwayat transaksi akan ditambahkan di task T049
    });
});

// Redirect root ke dashboard atau login
Route::get('/', function () {
    return redirect('/dashboard');
});
