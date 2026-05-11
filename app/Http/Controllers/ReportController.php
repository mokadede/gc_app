<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        $orders = Order::whereDate('created_at', $date)->get();
        $paidOrders = $orders->where('is_paid', true);

        return response()->json([
            'date' => $date,
            'total_orders' => $orders->count(),
            'total_revenue' => $paidOrders->sum('total_price'),
            'paid_count' => $paidOrders->count(),
            'unpaid_count' => $orders->where('is_paid', false)->where('status', '!=', 'cancelled')->count(),
            'cancelled_count' => $orders->where('status', 'cancelled')->count(),
            'status_breakdown' => [
                'pending' => $orders->where('status', 'pending')->count(),
                'picked_up' => $orders->where('status', 'picked_up')->count(),
                'in_process' => $orders->where('status', 'in_process')->count(),
                'done' => $orders->where('status', 'done')->count(),
                'delivered' => $orders->where('status', 'delivered')->count(),
                'cancelled' => $orders->where('status', 'cancelled')->count(),
            ],
        ]);
    }

    public function monthly(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $orders = Order::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();

        $paidOrders = $orders->where('is_paid', true);

        // Daily breakdown
        $dailyBreakdown = Order::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('is_paid', true)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return response()->json([
            'month' => $month,
            'year' => $year,
            'total_orders' => $orders->count(),
            'total_revenue' => $paidOrders->sum('total_price'),
            'paid_count' => $paidOrders->count(),
            'unpaid_count' => $orders->where('is_paid', false)->where('status', '!=', 'cancelled')->count(),
            'average_order_value' => $paidOrders->count() > 0
                ? round($paidOrders->sum('total_price') / $paidOrders->count())
                : 0,
            'daily_breakdown' => $dailyBreakdown,
        ]);
    }
}
