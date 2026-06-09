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

        $grossProfit = \App\Models\OrderItem::whereHas('order', function ($q) use ($today) {
            $q->where('status', 'completed')->where('created_at', '>=', $today);
        })->sum(\Illuminate\Support\Facades\DB::raw('(snapshot_price - snapshot_cogs) * quantity'));

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'totalRevenue' => $totalRevenue,
                'totalTransactions' => $totalTransactions,
                'grossProfit' => $grossProfit,
            ],
        ]);
    }
}
