<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();

        $todayOrders = Order::where('status', 'completed')
            ->where('created_at', '>=', $today);

        $totalRevenue = (clone $todayOrders)->sum('total_amount');
        $totalTransactions = (clone $todayOrders)->count();

        $activeCashiers = \App\Models\User::where('role', 'cashier')->where('is_active', true)->count();

        $grossProfit = \App\Models\OrderItem::whereHas('order', function ($q) use ($today) {
            $q->where('status', 'completed')->where('created_at', '>=', $today);
        })->sum(\Illuminate\Support\Facades\DB::raw('(snapshot_price - snapshot_cogs) * quantity'));

        $restockProducts = \App\Models\Product::active()
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
            $revenue = Order::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total_amount');
            return [
                'date' => $date->format('d/m'),
                'full_date' => $date->toDateString(),
                'day' => $date->translatedFormat('D'),
                'revenue' => (float) $revenue,
            ];
        });

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'totalRevenue' => $totalRevenue,
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
