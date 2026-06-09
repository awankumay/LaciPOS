<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PrintService;

class PrintController extends Controller
{
    public function print(Order $order, PrintService $printService)
    {
        $result = $printService->printReceipt($order);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }
}
