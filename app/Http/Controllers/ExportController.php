<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StoreProfile;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    public function exportRevenue(Request $request, ExportService $exportService)
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

        $storeProfile = StoreProfile::getProfile();

        $data = [
            'store_name' => $storeProfile ? $storeProfile->store_name : 'Sistem POS',
            'store_address' => $storeProfile ? $storeProfile->store_address : '',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'dailyRevenue' => $dailyRevenue,
            'totals' => $totals,
            'print_date' => now()->format('d M Y H:i:s'),
        ];

        return $exportService->exportRevenuePdf($data);
    }

    public function exportProfitLoss(Request $request, ExportService $exportService)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $dataDb = OrderItem::whereHas('order', fn ($q) =>
            $q->where('status', 'completed')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
        )->select(
            DB::raw("SUM(snapshot_price * quantity) as total_revenue"),
            DB::raw("SUM(snapshot_cogs * quantity) as total_cogs"),
            DB::raw("SUM((snapshot_price - snapshot_cogs) * quantity) as gross_profit")
        )->first();

        $totalRevenue = (float) $dataDb->total_revenue;
        $totalCogs = (float) $dataDb->total_cogs;
        $grossProfit = (float) $dataDb->gross_profit;

        $ordersStats = Order::where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->select(DB::raw("SUM(payment_admin_fee_amount) as total_mdr"))
            ->first();

        $totalMdr = (float) $ordersStats->total_mdr;
        $netProfit = $grossProfit - $totalMdr;
        $marginPercentage = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 2) : 0;
        $storeProfile = StoreProfile::getProfile();

        $data = [
            'store_name' => $storeProfile ? $storeProfile->store_name : 'Sistem POS',
            'store_address' => $storeProfile ? $storeProfile->store_address : '',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'totalRevenue' => $totalRevenue,
            'totalCogs' => $totalCogs,
            'grossProfit' => $grossProfit,
            'totalMdr' => $totalMdr,
            'netProfit' => $netProfit,
            'marginPercentage' => $marginPercentage,
            'print_date' => now()->format('d M Y H:i:s'),
        ];

        return $exportService->exportProfitLossPdf($data);
    }

    public function exportRevenueCsv(Request $request, ExportService $exportService)
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
            'total_transactions' => $dailyRevenue->sum('total_transactions'),
            'gross_revenue' => $dailyRevenue->sum('gross_revenue'),
            'total_tax' => $dailyRevenue->sum('total_tax'),
            'total_sc' => $dailyRevenue->sum('total_sc'),
            'net_revenue' => $dailyRevenue->sum('net_revenue'),
            'total_mdr' => $dailyRevenue->sum('total_mdr'),
            'total_settlement' => $dailyRevenue->sum('total_settlement'),
        ];

        return $exportService->exportRevenueCsv($dailyRevenue, $totals, $startDate, $endDate);
    }

    public function exportProfitLossCsv(Request $request, ExportService $exportService)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $dataDb = OrderItem::whereHas('order', fn ($q) =>
            $q->where('status', 'completed')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
        )->select(
            DB::raw("SUM(snapshot_price * quantity) as total_revenue"),
            DB::raw("SUM(snapshot_cogs * quantity) as total_cogs"),
            DB::raw("SUM((snapshot_price - snapshot_cogs) * quantity) as gross_profit")
        )->first();

        $totalRevenue = (float) $dataDb->total_revenue;
        $totalCogs = (float) $dataDb->total_cogs;
        $grossProfit = (float) $dataDb->gross_profit;

        $ordersStats = Order::where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->select(DB::raw("SUM(payment_admin_fee_amount) as total_mdr"))
            ->first();

        $totalMdr = (float) $ordersStats->total_mdr;
        $netProfit = $grossProfit - $totalMdr;
        $marginPercentage = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 2) : 0;

        $data = [
            'totalRevenue' => $totalRevenue,
            'totalCogs' => $totalCogs,
            'grossProfit' => $grossProfit,
            'totalMdr' => $totalMdr,
            'netProfit' => $netProfit,
            'marginPercentage' => $marginPercentage,
        ];

        return $exportService->exportProfitLossCsv($data, $startDate, $endDate);
    }

    public function exportTaxSummary(Request $request, ExportService $exportService)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $ordersQuery = Order::where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        $summary = (clone $ordersQuery)->select(
            DB::raw("SUM(tax_amount) as total_tax"),
            DB::raw("SUM(service_charge_amount) as total_sc")
        )->first();

        $details = (clone $ordersQuery)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($order) => [
                'date' => $order->created_at->format('Y-m-d'),
                'order_number' => $order->order_number,
                'subtotal' => (float) $order->subtotal,
                'tax_rate' => $order->tax_type === 'percentage' ? $order->tax_rate . '%' : '-',
                'tax_amount' => (float) $order->tax_amount,
                'service_charge_amount' => (float) $order->service_charge_amount,
                'grand_total' => (float) $order->total_amount,
            ]);

        $storeProfile = StoreProfile::getProfile();

        $data = [
            'store_name' => $storeProfile ? $storeProfile->store_name : 'Sistem POS',
            'store_address' => $storeProfile ? $storeProfile->store_address : '',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'totalTax' => (float) $summary->total_tax,
            'totalSc' => (float) $summary->total_sc,
            'details' => $details,
            'print_date' => now()->format('d M Y H:i:s'),
        ];

        return $exportService->exportTaxSummaryPdf($data);
    }

    public function exportTaxSummaryCsv(Request $request, ExportService $exportService)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $ordersQuery = Order::where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        $summary = (clone $ordersQuery)->select(
            DB::raw("SUM(tax_amount) as total_tax"),
            DB::raw("SUM(service_charge_amount) as total_sc")
        )->first();

        $details = (clone $ordersQuery)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($order) => [
                'date' => $order->created_at->format('Y-m-d'),
                'order_number' => $order->order_number,
                'subtotal' => (float) $order->subtotal,
                'tax_rate' => $order->tax_type === 'percentage' ? $order->tax_rate . '%' : '-',
                'tax_amount' => (float) $order->tax_amount,
                'service_charge_amount' => (float) $order->service_charge_amount,
                'grand_total' => (float) $order->total_amount,
            ]);

        return $exportService->exportTaxSummaryCsv($details, $startDate, $endDate, (float) $summary->total_tax, (float) $summary->total_sc);
    }

    public function exportBestSellersCsv(Request $request, ExportService $exportService)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $products = OrderItem::whereHas('order', fn ($q) =>
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

        return $exportService->exportBestSellersCsv($products, $startDate, $endDate);
    }
}
