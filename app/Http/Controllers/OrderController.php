<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Order::with(['items.service', 'user', 'statusLogs']);

        if ($user->role === 'customer') {
            $query->where('user_id', $user->id);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json($orders);
    }

    public function show(Request $request, Order $order)
    {
        $user = $request->user();
        
        if ($user->role === 'customer' && $order->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order->load(['items.service', 'user', 'statusLogs']));
    }

    public function store(CreateOrderRequest $request)
    {
        DB::beginTransaction();

        try {
            // Generate order code: GC-YYYYMMDD-XXX
            $datePrefix = date('Ymd');
            $latestOrder = Order::where('order_code', 'like', "GC-{$datePrefix}-%")->orderBy('id', 'desc')->first();
            $sequence = $latestOrder ? intval(substr($latestOrder->order_code, -3)) + 1 : 1;
            $orderCode = "GC-{$datePrefix}-" . str_pad($sequence, 3, '0', STR_PAD_LEFT);

            // Calculate total price based on items
            $totalPrice = collect($request->items)->sum('subtotal');

            $order = Order::create([
                'order_code' => $orderCode,
                'user_id' => $request->user()->id,
                'pickup_address' => $request->pickup_address,
                'notes' => $request->notes,
                'status' => 'pending',
                'total_price' => $totalPrice,
            ]);

            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'service_id' => $item['service_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'changed_by' => $request->user()->id,
            ]);

            DB::commit();

            return response()->json($order->load('items.service'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create order', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,picked_up,in_process,done,delivered,cancelled',
            'note' => 'nullable|string'
        ]);

        $user = $request->user();
        if ($user->role === 'customer' && $request->status !== 'cancelled') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->update(['status' => $request->status]);

        OrderStatusLog::create([
            'order_id' => $order->id,
            'status' => $request->status,
            'note' => $request->note,
            'changed_by' => $user->id,
        ]);

        return response()->json($order);
    }

    public function updatePayment(Request $request, Order $order)
    {
        $request->validate([
            'is_paid' => 'required|boolean',
            'payment_method' => 'nullable|in:cash,transfer'
        ]);

        $user = $request->user();
        if ($user->role === 'customer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->update([
            'is_paid' => $request->is_paid,
            'payment_method' => $request->payment_method ?? $order->payment_method
        ]);

        return response()->json($order);
    }
}
