<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
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
        ]);
    }

    public function monthly(Request $request)
    {
        $month = $request->get('month'); // null = all months
        $year = $request->get('year', now()->year);

        // Base query
        $query = Order::whereYear('created_at', $year);
        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        $orders = $query->get();
        $paidOrders = $orders->where('is_paid', true);

        // Monthly breakdown for chart (all 12 months)
        $monthlyChart = Order::whereYear('created_at', $year)
            ->where('is_paid', true)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $chartData = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartData[] = [
                'month' => $m,
                'revenue' => (int) ($monthlyChart[$m]->revenue ?? 0),
                'count' => (int) ($monthlyChart[$m]->count ?? 0),
            ];
        }

        // Top services (layanan terlaris)
        $topServicesQuery = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('services', 'order_items.service_id', '=', 'services.id')
            ->whereYear('orders.created_at', $year)
            ->where('orders.is_paid', true);
        if ($month) {
            $topServicesQuery->whereMonth('orders.created_at', $month);
        }
        $topServices = $topServicesQuery
            ->select(
                'services.name as service_name',
                DB::raw('SUM(order_items.quantity) as total_ordered'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('services.name')
            ->orderByDesc('total_ordered')
            ->limit(10)
            ->get();

        $totalOrdered = $topServices->sum('total_ordered');
        $topServicesFormatted = $topServices->map(function ($s) use ($totalOrdered) {
            return [
                'service_name' => $s->service_name,
                'total_ordered' => (int) $s->total_ordered,
                'total_revenue' => (int) $s->total_revenue,
                'percentage' => $totalOrdered > 0 ? round(($s->total_ordered / $totalOrdered) * 100, 1) : 0,
            ];
        });

        // Best selling service name
        $bestService = $topServices->first();

        // Paid transactions list (rincian transaksi lunas)
        $paidTransactionsQuery = Order::whereYear('created_at', $year)
            ->where('is_paid', true);
        if ($month) {
            $paidTransactionsQuery->whereMonth('created_at', $month);
        }
        $paidTransactions = $paidTransactionsQuery
            ->orderByDesc('created_at')
            ->get(['id', 'order_code', 'customer_name', 'total_price', 'created_at']);

        return response()->json([
            'month' => $month,
            'year' => $year,
            'total_orders' => $orders->count(),
            'total_revenue' => (int) $paidOrders->sum('total_price'),
            'paid_count' => $paidOrders->count(),
            'unpaid_count' => $orders->where('is_paid', false)->where('status', '!=', 'cancelled')->count(),
            'average_order_value' => $paidOrders->count() > 0
                ? (int) round($paidOrders->sum('total_price') / $paidOrders->count())
                : 0,
            'best_service' => $bestService ? $bestService->service_name : null,
            'monthly_chart' => $chartData,
            'top_services' => $topServicesFormatted,
            'paid_transactions' => $paidTransactions,
        ]);
    }
}
