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
                DB::raw("SUM(total_amount) as total_revenue")
            )
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy('date', 'desc')
            ->get();

        $grandTotal = $dailyRevenue->sum('total_revenue');
        $storeProfile = StoreProfile::getProfile();

        $data = [
            'store_name' => $storeProfile ? $storeProfile->store_name : 'Sistem POS',
            'store_address' => $storeProfile ? $storeProfile->store_address : '',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'dailyRevenue' => $dailyRevenue,
            'grandTotal' => $grandTotal,
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
        $marginPercentage = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 2) : 0;
        $storeProfile = StoreProfile::getProfile();

        $data = [
            'store_name' => $storeProfile ? $storeProfile->store_name : 'Sistem POS',
            'store_address' => $storeProfile ? $storeProfile->store_address : '',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'totalRevenue' => $totalRevenue,
            'totalCogs' => $totalCogs,
            'grossProfit' => $grossProfit,
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
                DB::raw("SUM(total_amount) as total_revenue")
            )
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy('date', 'desc')
            ->get();

        return $exportService->exportRevenueCsv($dailyRevenue, $startDate, $endDate);
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
        $marginPercentage = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 2) : 0;

        $data = [
            'totalRevenue' => $totalRevenue,
            'totalCogs' => $totalCogs,
            'grossProfit' => $grossProfit,
            'marginPercentage' => $marginPercentage,
        ];

        return $exportService->exportProfitLossCsv($data, $startDate, $endDate);
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
