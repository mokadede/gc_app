<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // === CATEGORIES ===
        $kiloan = ServiceCategory::create(['name' => 'Kiloan', 'description' => 'Laundry kiloan cuci kering']);
        $kiloanSetrika = ServiceCategory::create(['name' => 'Kiloan + Setrika', 'description' => 'Laundry kiloan cuci kering + setrika']);
        $satuan = ServiceCategory::create(['name' => 'Satuan', 'description' => 'Laundry satuan per pcs']);
        $perlengkapan = ServiceCategory::create(['name' => 'Perlengkapan Tidur', 'description' => 'Laundry perlengkapan tidur']);

        // === KILOAN ===
        $kiloanItems = [
            ['name' => 'Same Day', 'duration_days' => 0, 'duration_label' => 'Same Day', 'price_min' => 15000],
            ['name' => '1 Hari', 'duration_days' => 1, 'duration_label' => '1 Hari', 'price_min' => 10000],
            ['name' => '2 Hari', 'duration_days' => 2, 'duration_label' => '2 Hari', 'price_min' => 8000],
            ['name' => '3 Hari', 'duration_days' => 3, 'duration_label' => '3 Hari', 'price_min' => 7000],
            ['name' => '4 Hari', 'duration_days' => 4, 'duration_label' => '4 Hari', 'price_min' => 5000],
        ];
        foreach ($kiloanItems as $item) {
            Service::create(array_merge($item, ['category_id' => $kiloan->id, 'unit' => 'kg']));
        }

        // === KILOAN + SETRIKA ===
        $kiloanSetrikaItems = [
            ['name' => 'Same Day', 'duration_days' => 0, 'duration_label' => 'Same Day', 'price_min' => 18000],
            ['name' => '1 Hari', 'duration_days' => 1, 'duration_label' => '1 Hari', 'price_min' => 12500],
            ['name' => '2 Hari', 'duration_days' => 2, 'duration_label' => '2 Hari', 'price_min' => 10500],
            ['name' => '3 Hari', 'duration_days' => 3, 'duration_label' => '3 Hari', 'price_min' => 9500],
            ['name' => '4 Hari', 'duration_days' => 4, 'duration_label' => '4 Hari', 'price_min' => 7500],
        ];
        foreach ($kiloanSetrikaItems as $item) {
            Service::create(array_merge($item, ['category_id' => $kiloanSetrika->id, 'unit' => 'kg']));
        }

        // === SATUAN ===
        $satuanItems = [
            ['name' => 'T-Shirts', 'difficulty' => 'normal', 'price_min' => 10000],
            ['name' => 'T-Shirts', 'difficulty' => 'hard', 'price_min' => 15000, 'price_max' => 20000],
            ['name' => 'Shirts', 'difficulty' => 'normal', 'price_min' => 15000],
            ['name' => 'Shirts', 'difficulty' => 'hard', 'price_min' => 18000, 'price_max' => 35000],
            ['name' => 'Jacket', 'difficulty' => 'normal', 'price_min' => 25000],
            ['name' => 'Jacket', 'difficulty' => 'hard', 'price_min' => 35000, 'price_max' => 50000],
            ['name' => 'Short Pants', 'difficulty' => 'normal', 'price_min' => 10000, 'price_max' => 15000],
            ['name' => 'Short Pants', 'difficulty' => 'hard', 'price_min' => 20000, 'price_max' => 25000],
            ['name' => 'Trousers', 'difficulty' => 'normal', 'price_min' => 10000, 'price_max' => 15000],
            ['name' => 'Trousers', 'difficulty' => 'hard', 'price_min' => 18000, 'price_max' => 35000],
            ['name' => 'Suits', 'difficulty' => 'normal', 'price_min' => 50000],
            ['name' => 'Suits', 'difficulty' => 'hard', 'price_min' => 75000],
        ];
        foreach ($satuanItems as $item) {
            Service::create(array_merge($item, ['category_id' => $satuan->id, 'unit' => 'pcs']));
        }

        // === PERLENGKAPAN TIDUR ===
        $perlengkapanItems = [
            ['name' => 'Bed Cover', 'size' => 'S', 'difficulty' => 'normal', 'price_min' => 25000],
            ['name' => 'Blanket / Selimut', 'size' => 'M', 'difficulty' => 'normal', 'price_min' => 30000],
            ['name' => 'Blanket / Selimut', 'size' => 'L', 'difficulty' => 'normal', 'price_min' => 40000],
            ['name' => 'Blanket / Selimut', 'size' => 'XL', 'difficulty' => 'normal', 'price_min' => 45000],
            ['name' => 'Bed Sheet / Sprei', 'size' => 'S', 'difficulty' => 'normal', 'price_min' => 10000],
            ['name' => 'Bed Sheet / Sprei', 'size' => 'S', 'difficulty' => 'hard', 'price_min' => 25000],
            ['name' => 'Bed Sheet / Sprei', 'size' => 'M', 'difficulty' => 'normal', 'price_min' => 25000],
            ['name' => 'Bed Sheet / Sprei', 'size' => 'M', 'difficulty' => 'hard', 'price_min' => 35000],
            ['name' => 'Bed Sheet / Sprei', 'size' => 'L', 'difficulty' => 'normal', 'price_min' => 25000],
            ['name' => 'Bed Sheet / Sprei', 'size' => 'L', 'difficulty' => 'hard', 'price_min' => 40000],
            ['name' => 'Bed Sheet / Sprei', 'size' => 'XL', 'difficulty' => 'normal', 'price_min' => 30000],
            ['name' => 'Bed Sheet / Sprei', 'size' => 'XL', 'difficulty' => 'hard', 'price_min' => 45000],
            ['name' => 'Pillow Case / Bolster Case', 'difficulty' => 'normal', 'price_min' => 7000],
            ['name' => 'Pillow Case / Bolster Case', 'difficulty' => 'hard', 'price_min' => 12000],
            ['name' => 'Towel / Handuk', 'difficulty' => 'normal', 'price_min' => 10000],
        ];
        foreach ($perlengkapanItems as $item) {
            Service::create(array_merge($item, ['category_id' => $perlengkapan->id, 'unit' => 'pcs']));
        }
    }
}
