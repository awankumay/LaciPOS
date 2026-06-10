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
        Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    
    // Product & Stock Routes
    Route::get('/products/{product}/stock', [App\Http\Controllers\StockController::class, 'index'])->name('stock.index');
    Route::post('/products/{product}/stock', [App\Http\Controllers\StockController::class, 'store'])->name('stock.store');
        Route::resource('products', ProductController::class)->except(['show']);

        // Route untuk reports, settings
        Route::get('/reports', fn () => redirect()->route('reports.revenue'))->name('reports.index');
        Route::get('/reports/revenue', [\App\Http\Controllers\ReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('/reports/revenue/export-pdf', [\App\Http\Controllers\ExportController::class, 'exportRevenue'])->name('reports.revenue.export-pdf');
        Route::get('/reports/revenue/export-csv', [\App\Http\Controllers\ExportController::class, 'exportRevenueCsv'])->name('reports.revenue.export-csv');
        Route::get('/reports/profit-loss', [\App\Http\Controllers\ReportController::class, 'profitLoss'])->name('reports.profit-loss');
        Route::get('/reports/profit-loss/export-pdf', [\App\Http\Controllers\ExportController::class, 'exportProfitLoss'])->name('reports.profit-loss.export-pdf');
        Route::get('/reports/profit-loss/export-csv', [\App\Http\Controllers\ExportController::class, 'exportProfitLossCsv'])->name('reports.profit-loss.export-csv');
        Route::get('/reports/best-sellers', [\App\Http\Controllers\ReportController::class, 'bestSellers'])->name('reports.best-sellers');
        Route::get('/reports/best-sellers/export-csv', [\App\Http\Controllers\ExportController::class, 'exportBestSellersCsv'])->name('reports.best-sellers.export-csv');
        Route::get('/settings/printer', [App\Http\Controllers\PrinterSettingController::class, 'index'])->name('settings.printer.index');
        Route::post('/settings/printer', [App\Http\Controllers\PrinterSettingController::class, 'store'])->name('settings.printer.store');
        Route::post('/settings/printer/test', [App\Http\Controllers\PrinterSettingController::class, 'testPrint'])->name('settings.printer.test');

        // Store Settings
        Route::get('/settings', fn () => redirect()->route('settings.store'))->name('settings');
        Route::get('/settings/store', [App\Http\Controllers\SettingsController::class, 'store'])->name('settings.store');
        Route::post('/settings/store', [App\Http\Controllers\SettingsController::class, 'updateStore'])->name('settings.store.update');

        // Cashiers Settings
        Route::get('/settings/cashiers', [App\Http\Controllers\CashierController::class, 'index'])->name('settings.cashiers');
        Route::post('/settings/cashiers', [App\Http\Controllers\CashierController::class, 'store']);
        Route::put('/settings/cashiers/{cashier}', [App\Http\Controllers\CashierController::class, 'update']);
        Route::patch('/settings/cashiers/{cashier}/toggle', [App\Http\Controllers\CashierController::class, 'toggleActive']);

        // Inventory Settings
        Route::get('/settings/inventory', [App\Http\Controllers\SettingsController::class, 'inventory'])->name('settings.inventory');
        Route::post('/settings/inventory', [App\Http\Controllers\SettingsController::class, 'updateInventory'])->name('settings.inventory.update');

        // Backup Settings
        Route::get('/settings/backup', [App\Http\Controllers\BackupController::class, 'index'])->name('settings.backup');
        Route::post('/settings/backup', [App\Http\Controllers\BackupController::class, 'store'])->name('settings.backup.store');
        Route::get('/settings/backup/{filename}/download', [App\Http\Controllers\BackupController::class, 'download'])->name('settings.backup.download');
    });

    // Route yang bisa diakses owner DAN cashier
    Route::middleware('role:owner,cashier')->group(function () {
        // Route POS akan ditambahkan di task T027
        Route::get('/pos', [App\Http\Controllers\POSController::class, 'index'])->name('pos.index');
        
        // Orders
        Route::post('/orders', [App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}/success', [App\Http\Controllers\OrderController::class, 'success'])->name('order.success');
        Route::post('/orders/{order}/print', [App\Http\Controllers\PrintController::class, 'print'])->name('order.print');
        
        // Riwayat transaksi
        Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [App\Http\Controllers\OrderController::class, 'cancel'])->name('orders.cancel');
    });
});

// Redirect root ke dashboard atau login
Route::get('/', function () {
    return redirect('/dashboard');
});
