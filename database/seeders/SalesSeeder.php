<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $sales = [
            [
                'invoice_number' => 'INV-0001',
                'user_id' => 1,
                'sale_date' => Carbon::now(),
                'total_amount' => 120000,
                'total_cost' => 80000,
                'profit' => 40000,
                'payment_method' => 'cash',
            ],
            [
                'invoice_number' => 'INV-0002',
                'user_id' => 1,
                'sale_date' => Carbon::now()->subDays(1),
                'total_amount' => 95000,
                'total_cost' => 65000,
                'profit' => 30000,
                'payment_method' => 'qris',
            ],
            [
                'invoice_number' => 'INV-0003',
                'user_id' => 1,
                'sale_date' => Carbon::now()->subDays(2),
                'total_amount' => 150000,
                'total_cost' => 100000,
                'profit' => 50000,
                'payment_method' => 'cash',
            ],
            [
                'invoice_number' => 'INV-0004',
                'user_id' => 1,
                'sale_date' => Carbon::now()->subDays(3),
                'total_amount' => 70000,
                'total_cost' => 45000,
                'profit' => 25000,
                'payment_method' => 'transfer',
            ],
            [
                'invoice_number' => 'INV-0005',
                'user_id' => 1,
                'sale_date' => Carbon::now()->subDays(4),
                'total_amount' => 180000,
                'total_cost' => 130000,
                'profit' => 50000,
                'payment_method' => 'cash',
            ],
            [
                'invoice_number' => 'INV-0006',
                'user_id' => 1,
                'sale_date' => Carbon::now()->subDays(5),
                'total_amount' => 150000,
                'total_cost' => 130000,
                'profit' => 20000,
                'payment_method' => 'cash',
            ],
            [
                'invoice_number' => 'INV-0007',
                'user_id' => 1,
                'sale_date' => Carbon::now()->subDays(6),
                'total_amount' => 280000,
                'total_cost' => 130000,
                'profit' => 150000,
                'payment_method' => 'cash',
            ],
            [
                'invoice_number' => 'INV-0008',
                'user_id' => 1,
                'sale_date' => Carbon::now()->subDays(7),
                'total_amount' => 190000,
                'total_cost' => 130000,
                'profit' => 60000,
                'payment_method' => 'cash',
            ],
            [
                'invoice_number' => 'INV-0009',
                'user_id' => 1,
                'sale_date' => Carbon::now()->subDays(8),
                'total_amount' => 140000,
                'total_cost' => 130000,
                'profit' => 10000,
                'payment_method' => 'cash',
            ],
        ];

        foreach ($sales as $sale) {
            Sale::create($sale);
        }
    }
}
