<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin User
        User::factory()->create([
            'name' => 'Admin Gift Clean',
            'phone' => '081111111111',
            'email' => 'admin@giftclean.com',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
        ]);

        // 2. Create Kasir User
        User::factory()->create([
            'name' => 'Kasir Gift Clean',
            'phone' => '082222222222',
            'email' => 'kasir@giftclean.com',
            'role' => 'kasir',
            'password' => Hash::make('kasir123'),
        ]);

        // 3. Create 10 Random Customers
        User::factory(10)->create([
            'role' => 'customer',
        ]);

        // 4. Seed Services (37 layanan dari PRD)
        $this->call(ServiceSeeder::class);

        // 5. Seed Orders (50 order dummy)
        $this->call(OrderSeeder::class);
    }
}
