<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SaleItem;

class SaleItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'sale_id' => 1,
                'product_id' => 1,
                'quantity' => 2,
                'price' => 25000,
                'cost' => 15000,
                'subtotal' => 50000,
                'profit' => 20000,
            ],
            [
                'sale_id' => 1,
                'product_id' => 2,
                'quantity' => 1,
                'price' => 20000,
                'cost' => 12000,
                'subtotal' => 20000,
                'profit' => 8000,
            ],
            [
                'sale_id' => 2,
                'product_id' => 3,
                'quantity' => 3,
                'price' => 15000,
                'cost' => 8000,
                'subtotal' => 45000,
                'profit' => 21000,
            ],
        ];

        foreach ($items as $item) {
            SaleItem::create($item);
        }
    }
}
