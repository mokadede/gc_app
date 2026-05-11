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
            'total_revenue' => (int) $paidOrders->sum('total_price'),
            'paid_count' => $paidOrders->count(),
            'unpaid_count' => $orders->where('is_paid', false)->where('status', '!=', 'cancelled')->count(),
            'cancelled_count' => $orders->where('status', 'cancelled')->count(),
        ]);
    }

    public function monthly(Request $request)
    {
        $month = $request->get('month'); 
        $year = $request->get('year', now()->year);

        // 1. Query Dasar untuk Ringkasan
        $query = Order::whereYear('created_at', $year);
        if ($month && $month != 'null') {
            $query->whereMonth('created_at', $month);
        }

        $orders = $query->get();
        
        // Pastikan is_paid dihitung dengan benar (1 atau true)
        $paidOrders = $orders->filter(fn($o) => $o->is_paid == 1 || $o->is_paid == true);

        // 2. Data Chart (Selalu 12 bulan untuk tahun yang dipilih)
        $monthlyChart = Order::whereYear('created_at', $year)
            ->where(function($q) {
                $q->where('is_paid', 1)->orWhere('is_paid', true);
            })
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get()
            ->keyBy('month');

        $chartData = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartData[] = [
                'month' => $m,
                'revenue' => isset($monthlyChart[$m]) ? (int) $monthlyChart[$m]->revenue : 0,
                'count' => isset($monthlyChart[$m]) ? (int) $monthlyChart[$m]->count : 0,
            ];
        }

        // 3. Layanan Terlaris
        $topServicesQuery = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('services', 'order_items.service_id', '=', 'services.id')
            ->whereYear('orders.created_at', $year);
            
        if ($month && $month != 'null') {
            $topServicesQuery->whereMonth('orders.created_at', $month);
        }

        $topServices = $topServicesQuery
            ->where(function($q) {
                $q->where('orders.is_paid', 1)->orWhere('orders.is_paid', true);
            })
            ->select(
                'services.name as service_name',
                DB::raw('SUM(order_items.quantity) as total_ordered'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('services.name')
            ->orderByDesc('total_ordered')
            ->limit(5)
            ->get();

        $totalQty = $topServices->sum('total_ordered');
        $topServicesFormatted = $topServices->map(function ($s) use ($totalQty) {
            return [
                'service_name' => $s->service_name,
                'total_ordered' => (int) $s->total_ordered,
                'total_revenue' => (int) $s->total_revenue,
                'percentage' => $totalQty > 0 ? round(($s->total_ordered / $totalQty) * 100, 1) : 0,
            ];
        });

        // 4. Rincian Transaksi Lunas
        $paidTransactions = $orders->filter(fn($o) => $o->is_paid == 1 || $o->is_paid == true)
            ->values()
            ->map(function($o) {
                return [
                    'id' => $o->id,
                    'order_code' => $o->order_code,
                    'customer_name' => $o->customer_name,
                    'total_price' => (int) $o->total_price,
                    'created_at' => $o->created_at,
                ];
            });

        return response()->json([
            'total_orders' => $orders->count(),
            'total_revenue' => (int) $paidOrders->sum('total_price'),
            'paid_count' => $paidOrders->count(),
            'unpaid_count' => $orders->count() - $paidOrders->count(),
            'average_order_value' => $paidOrders->count() > 0 ? (int) ($paidOrders->sum('total_price') / $paidOrders->count()) : 0,
            'best_service' => $topServicesFormatted->first()['service_name'] ?? '-',
            'monthly_chart' => $chartData,
            'top_services' => $topServicesFormatted,
            'paid_transactions' => $paidTransactions,
        ]);
    }
}
