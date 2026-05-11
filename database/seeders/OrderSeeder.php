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

        // Buat 50 order dummy dalam 3 bulan terakhir
        for ($i = 0; $i < 50; $i++) {
            $date = Carbon::now()->subDays(rand(0, 90));
            $isPaid = rand(0, 10) > 2; // 80% lunas
            
            $order = Order::create([
                'order_code' => 'GC' . strtoupper(bin2hex(random_bytes(3))),
                'created_by' => $admin->id,
                'customer_name' => ['Budi', 'Siti', 'Agus', 'Dewi', 'Iwan', 'Lani'][rand(0, 5)],
                'customer_phone' => '0812' . rand(10000000, 99999999),
                'status' => $isPaid ? 'delivered' : 'pending',
                'is_paid' => $isPaid,
                'payment_method' => 'cash',
                'total_price' => 0, // Akan diupdate setelah item
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // Tambah 1-3 item per order
            $total = 0;
            $itemCount = rand(1, 3);
            for ($j = 0; $j < $itemCount; $j++) {
                $service = $services->random();
                $qty = rand(1, 5);
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
