<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['items.service', 'statusLogs', 'voucher'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function show(Request $request, Order $order)
    {
        return response()->json($order->load(['items.service', 'statusLogs', 'voucher']));
    }

    public function store(CreateOrderRequest $request)
    {
        DB::beginTransaction();

        try {
            $datePrefix = date('Ymd');
            $latestOrder = Order::where('order_code', 'like', "GC-{$datePrefix}-%")->orderBy('id', 'desc')->first();
            $sequence = $latestOrder ? intval(substr($latestOrder->order_code, -3)) + 1 : 1;
            $orderCode = "GC-{$datePrefix}-" . str_pad($sequence, 3, '0', STR_PAD_LEFT);

            $totalPrice = collect($request->items)->sum('subtotal');
            $discountAmount = 0;

            // Apply voucher if provided
            $voucherId = $request->voucher_id;
            if ($request->voucher_code) {
                $v = Voucher::where('code', $request->voucher_code)->first();
                if ($v) $voucherId = $v->id;
            }

            if ($voucherId) {
                $voucher = Voucher::find($voucherId);
                if ($voucher && $voucher->isValid()) {
                    if ($voucher->type === 'percentage') {
                        $discountAmount = (int) ($totalPrice * $voucher->value / 100);
                        if ($voucher->max_discount) {
                            $discountAmount = min($discountAmount, $voucher->max_discount);
                        }
                    } else {
                        $discountAmount = $voucher->value;
                    }
                    $voucher->increment('used_count');
                }
            }

            $status = $request->status ?? 'pending';

            $order = Order::create([
                'order_code' => $orderCode,
                'created_by' => $request->user()->id,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'pickup_address' => $request->pickup_address,
                'notes' => $request->notes,
                'status' => $status,
                'total_price' => $totalPrice - $discountAmount,
                'voucher_id' => $voucherId,
                'discount_amount' => $discountAmount,
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
                'status' => $status,
                'changed_by' => $request->user()->id,
            ]);

            DB::commit();
            return response()->json($order->load('items.service'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create order', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'customer_name' => 'string|max:150',
            'customer_phone' => 'nullable|string|max:20',
            'pickup_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'total_price' => 'nullable|integer',
        ]);

        $order->update($validated);
        return response()->json($order);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,picked_up,in_process,done,delivered,cancelled',
            'note' => 'nullable|string',
        ]);

        $order->update(['status' => $request->status]);

        OrderStatusLog::create([
            'order_id' => $order->id,
            'status' => $request->status,
            'note' => $request->note,
            'changed_by' => $request->user()->id,
        ]);

        return response()->json($order);
    }

    public function updatePayment(Request $request, Order $order)
    {
        $request->validate([
            'is_paid' => 'required|boolean',
            'payment_method' => 'nullable|in:cash,transfer',
        ]);

        $order->update([
            'is_paid' => $request->is_paid,
            'payment_method' => $request->payment_method ?? $order->payment_method,
        ]);

        return response()->json($order);
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(['message' => 'Order deleted']);
    }
}
