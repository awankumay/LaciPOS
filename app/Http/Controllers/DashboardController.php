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

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'totalRevenue' => $totalRevenue,
                'totalTransactions' => $totalTransactions,
            ],
        ]);
    }
}
