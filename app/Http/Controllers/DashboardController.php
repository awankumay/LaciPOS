<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();

        $baseQuery = Order::where('status', 'completed')
            ->where('created_at', '>=', $today);

        $totalRevenue = (clone $baseQuery)->sum('total_amount');
        $totalTax = (clone $baseQuery)->sum('tax_amount');
        $totalServiceCharge = (clone $baseQuery)->sum('service_charge_amount');
        $netRevenue = $totalRevenue - $totalTax - $totalServiceCharge;
        $totalTransactions = (clone $baseQuery)->count();

        $activeCashiers = User::where('role', 'cashier')->where('is_active', true)->count();

        $totalCogs = OrderItem::whereHas('order', fn ($q) =>
            $q->where('status', 'completed')->where('created_at', '>=', $today)
        )->sum(DB::raw('snapshot_cogs * quantity'));

        $grossProfit = $netRevenue - $totalCogs;

        $restockProducts = Product::active()
            ->lowStock()
            ->with('category')
            ->orderBy('stock')
            ->limit(10)
            ->get(['id', 'name', 'stock', 'min_stock_alert', 'category_id']);

        $recentTransactions = Order::with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(7)
            ->get();

        $salesData = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->startOfDay();
            $dayOrders = Order::where('status', 'completed')->whereDate('created_at', $date);
            $dayTotal = (float) $dayOrders->sum('total_amount');
            $dayTax = (float) $dayOrders->sum('tax_amount');
            $daySc = (float) $dayOrders->sum('service_charge_amount');
            return [
                'date' => $date->format('d/m'),
                'full_date' => $date->toDateString(),
                'day' => $date->translatedFormat('D'),
                'revenue' => $dayTotal - $dayTax - $daySc,
            ];
        });

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'totalRevenue' => $netRevenue,
                'totalTransactions' => $totalTransactions,
                'grossProfit' => $grossProfit,
                'activeCashiers' => $activeCashiers,
            ],
            'restockProducts' => $restockProducts,
            'salesData' => $salesData,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
