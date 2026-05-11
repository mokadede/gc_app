<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::all();
        $admin = User::whereIn('role', ['owner', 'karyawan'])->first();
        if ($services->isEmpty()) return;

        $statuses = ['pending', 'picked_up', 'in_process', 'done', 'delivered'];

        // Buat 80 order dummy agar tabel terlihat penuh
        for ($i = 0; $i < 80; $i++) {
            // Sebar dalam 120 hari terakhir
            $date = Carbon::now()->subDays(rand(0, 120));
            $status = $statuses[rand(0, 4)];
            $isPaid = ($status === 'delivered' || $status === 'done') ? true : (rand(0, 1) == 1);
            
            $total = 0;
            $items = [];
            $itemCount = rand(1, 2);
            
            for ($j = 0; $j < $itemCount; $j++) {
                $service = $services->random();
                $qty = rand(1, 3);
                $subtotal = $service->price_min * $qty;
                $total += $subtotal;
                $items[] = [
                    'service_id' => $service->id,
                    'quantity' => $qty,
                    'unit_price' => $service->price_min,
                    'subtotal' => $subtotal,
                ];
            }

            // Gunakan DB insert agar created_at bisa dipaksa sesuai variabel $date
            $orderId = DB::table('orders')->insertGetId([
                'order_code' => 'GC' . strtoupper(bin2hex(random_bytes(3))),
                'created_by' => $admin->id,
                'customer_name' => ['Andi', 'Budi', 'Rina', 'Agus', 'Lani', 'Iwan', 'Siti', 'Dewi', 'Cici', 'Dedi'][rand(0, 9)],
                'customer_phone' => '0812' . rand(10000000, 99999999),
                'status' => $status,
                'is_paid' => $isPaid,
                'payment_method' => 'cash',
                'total_price' => $total,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            foreach ($items as $item) {
                DB::table('order_items')->insert(array_merge($item, [
                    'order_id' => $orderId,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]));
            }
        }
    }
}
