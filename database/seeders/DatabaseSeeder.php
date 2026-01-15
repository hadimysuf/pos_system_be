<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            MenuSeeder::class,
            RoleAccessMenuSeeder::class,
            SalesSeeder::class,
            SaleItemSeeder::class,
            SupplierSeeder::class,
        ]);
    }
}
