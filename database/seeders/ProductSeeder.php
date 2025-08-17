<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Lipstik Merah', 'sku' => 'LP001', 'price' => 50000, 'stock' => 50, 'safety_stock' => 10, 'category_id' => 1],
            ['name' => 'Smartphone', 'sku' => 'EL001', 'price' => 2500000, 'stock' => 20, 'safety_stock' => 5, 'category_id' => 2],
            ['name' => 'Kaos Polos', 'sku' => 'PK001', 'price' => 100000, 'stock' => 100, 'safety_stock' => 20, 'category_id' => 3],
        ];

        foreach ($products as $p) {
            Product::create($p);
        }
    }
}
