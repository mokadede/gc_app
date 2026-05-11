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
        // 1. Create Owner User
        User::factory()->create([
            'name' => 'Owner Gift Clean',
            'username' => 'owner',
            'phone' => '081111111111',
            'email' => 'owner@giftclean.com',
            'role' => 'owner',
            'password' => Hash::make('admin123'),
        ]);

        // 2. Create Karyawan User
        User::factory()->create([
            'name' => 'Karyawan Gift Clean',
            'username' => 'staff',
            'phone' => '082222222222',
            'email' => 'karyawan@giftclean.com',
            'role' => 'karyawan',
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
