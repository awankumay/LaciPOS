<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function revenue(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $dailyRevenue = Order::where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->select(
                DB::raw("DATE(created_at) as date"),
                DB::raw("COUNT(*) as total_transactions"),
                DB::raw("SUM(total_amount) as total_revenue")
            )
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy('date', 'desc')
            ->get();

        $grandTotal = $dailyRevenue->sum('total_revenue');

        return Inertia::render('Reports/Revenue', [
            'dailyRevenue' => $dailyRevenue,
            'grandTotal' => $grandTotal,
            'filters' => ['start_date' => $startDate, 'end_date' => $endDate],
        ]);
    }

    public function profitLoss(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $data = \App\Models\OrderItem::whereHas('order', fn ($q) =>
            $q->where('status', 'completed')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
        )->select(
            DB::raw("SUM(snapshot_price * quantity) as total_revenue"),
            DB::raw("SUM(snapshot_cogs * quantity) as total_cogs"),
            DB::raw("SUM((snapshot_price - snapshot_cogs) * quantity) as gross_profit")
        )->first();

        $totalRevenue = (float) $data->total_revenue;
        $totalCogs = (float) $data->total_cogs;
        $grossProfit = (float) $data->gross_profit;
        $marginPercentage = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 2) : 0;

        return Inertia::render('Reports/ProfitLoss', [
            'totalRevenue' => $totalRevenue,
            'totalCogs' => $totalCogs,
            'grossProfit' => $grossProfit,
            'marginPercentage' => $marginPercentage,
            'filters' => ['start_date' => $startDate, 'end_date' => $endDate],
        ]);
    }

    public function bestSellers(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $products = \App\Models\OrderItem::whereHas('order', fn ($q) =>
            $q->where('status', 'completed')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
        )->select(
            'product_id',
            DB::raw("MAX(product_name_snapshot) as product_name"),
            DB::raw("SUM(quantity) as total_qty"),
            DB::raw("SUM(subtotal) as total_revenue")
        )->groupBy('product_id')
        ->orderByDesc('total_qty')
        ->limit(50)
        ->get();

        return Inertia::render('Reports/BestSellers', [
            'products' => $products,
            'filters' => ['start_date' => $startDate, 'end_date' => $endDate],
        ]);
    }
}
