<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::all();
        $admin = User::where('role', 'admin')->first();
        if ($services->isEmpty()) return;

        $statuses = ['pending', 'picked_up', 'in_process', 'done', 'delivered'];

        // Buat 60 order dummy
        for ($i = 0; $i < 60; $i++) {
            $date = Carbon::now()->subDays(rand(0, 90));
            $status = $statuses[rand(0, 4)];
            $isPaid = ($status === 'delivered' || $status === 'done') ? true : (rand(0, 1) == 1);
            
            $order = Order::create([
                'order_code' => 'GC' . strtoupper(bin2hex(random_bytes(3))),
                'created_by' => $admin->id,
                'customer_name' => ['Budi', 'Siti', 'Agus', 'Dewi', 'Iwan', 'Lani', 'Andi', 'Rina'][rand(0, 7)],
                'customer_phone' => '0812' . rand(10000000, 99999999),
                'status' => $status,
                'is_paid' => $isPaid,
                'payment_method' => 'cash',
                'total_price' => 0,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            $total = 0;
            $itemCount = rand(1, 2);
            for ($j = 0; $j < $itemCount; $j++) {
                $service = $services->random();
                $qty = rand(1, 3);
                $subtotal = $service->price_min * $qty;
                OrderItem::create([
                    'order_id' => $order->id,
                    'service_id' => $service->id,
                    'quantity' => $qty,
                    'unit_price' => $service->price_min,
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }
            $order->update(['total_price' => $total]);
        }
    }
}
