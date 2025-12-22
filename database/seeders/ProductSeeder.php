<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run()
    {
        DB::table('products')->insert([
            [
                'category_id' => 1,
                'sku' => 'KMJ-FLN-001',
                'name' => 'Kemeja Flanel',
                'price' => 150000.00,
                'cost' => 90000.00,
                'stock' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => 2,
                'sku' => 'CLN-JNS-001',
                'name' => 'Celana Jeans Slim Fit',
                'price' => 200000.00,
                'cost' => 120000.00,
                'stock' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => 3,
                'sku' => 'JKT-HDY-001',
                'name' => 'Jaket Hoodie Polos',
                'price' => 180000.00,
                'cost' => 110000.00,
                'stock' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
