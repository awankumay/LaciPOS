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
                DB::raw("SUM(total_amount) as gross_revenue"),
                DB::raw("SUM(tax_amount) as total_tax"),
                DB::raw("SUM(service_charge_amount) as total_sc"),
                DB::raw("SUM(payment_admin_fee_amount) as total_mdr")
            )
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'total_transactions' => $row->total_transactions,
                'gross_revenue' => (float) $row->gross_revenue,
                'total_tax' => (float) $row->total_tax,
                'total_sc' => (float) $row->total_sc,
                'net_revenue' => (float) $row->gross_revenue - $row->total_tax - $row->total_sc,
                'total_mdr' => (float) $row->total_mdr,
                'total_settlement' => (float) $row->gross_revenue - $row->total_mdr,
            ]);

        $totals = [
            'gross_revenue' => $dailyRevenue->sum('gross_revenue'),
            'total_tax' => $dailyRevenue->sum('total_tax'),
            'total_sc' => $dailyRevenue->sum('total_sc'),
            'net_revenue' => $dailyRevenue->sum('net_revenue'),
            'total_mdr' => $dailyRevenue->sum('total_mdr'),
            'total_settlement' => $dailyRevenue->sum('total_settlement'),
        ];

        return Inertia::render('Reports/Revenue', [
            'dailyRevenue' => $dailyRevenue,
            'totals' => $totals,
            'filters' => ['start_date' => $startDate, 'end_date' => $endDate],
        ]);
    }

    public function profitLoss(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $ordersQuery = \App\Models\Order::where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        $totals = (clone $ordersQuery)->select(
            DB::raw("SUM(total_amount) as gross_revenue"),
            DB::raw("SUM(tax_amount) as total_tax"),
            DB::raw("SUM(service_charge_amount) as total_sc"),
            DB::raw("SUM(payment_admin_fee_amount) as total_mdr")
        )->first();

        $grossRevenue = (float) $totals->gross_revenue;
        $totalTax = (float) $totals->total_tax;
        $totalSc = (float) $totals->total_sc;
        $totalMdr = (float) $totals->total_mdr;

        $netRevenue = $grossRevenue - $totalTax - $totalSc;

        $totalCogs = (float) \App\Models\OrderItem::whereHas('order', fn ($q) =>
            $q->where('status', 'completed')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
        )->sum(DB::raw('snapshot_cogs * quantity'));

        $grossProfit = $netRevenue - $totalCogs;
        $netAfterMdr = $grossProfit - $totalMdr;

        $marginPercentage = $netRevenue > 0 ? round(($netAfterMdr / $netRevenue) * 100, 2) : 0;

        return Inertia::render('Reports/ProfitLoss', [
            'netRevenue' => $netRevenue,
            'totalCogs' => $totalCogs,
            'grossProfit' => $grossProfit,
            'totalMdr' => $totalMdr,
            'netAfterMdr' => $netAfterMdr,
            'marginPercentage' => $marginPercentage,
            'totalTax' => $totalTax,
            'totalSc' => $totalSc,
            'filters' => ['start_date' => $startDate, 'end_date' => $endDate],
        ]);
    }

    public function taxSummary(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $ordersQuery = \App\Models\Order::where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        $summary = (clone $ordersQuery)->select(
            DB::raw("SUM(tax_amount) as total_tax"),
            DB::raw("SUM(service_charge_amount) as total_sc")
        )->first();

        $details = (clone $ordersQuery)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($order) => [
                'date' => $order->created_at->format('Y-m-d'),
                'order_number' => $order->order_number,
                'subtotal' => (float) $order->subtotal,
                'tax_rate' => $order->tax_type === 'percentage' ? $order->tax_rate . '%' : '-',
                'tax_amount' => (float) $order->tax_amount,
                'service_charge_amount' => (float) $order->service_charge_amount,
                'grand_total' => (float) $order->total_amount,
            ]);

        return Inertia::render('Reports/TaxSummary', [
            'totalTax' => (float) $summary->total_tax,
            'totalSc' => (float) $summary->total_sc,
            'details' => $details,
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
