<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run()
    {
        DB::table('menus')->insert([
            ['name' => 'Dashboard', 'code' => 'DASHBOARD', 'route' => '/dashboard', 'icon' => 'dashboard', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Produk', 'code' => 'PRODUCT_MANAGE', 'route' => '/products', 'icon' => 'box', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kategori', 'code' => 'CATEGORY_MANAGE', 'route' => '/categories', 'icon' => 'tags', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Stok Masuk', 'code' => 'STOCK_IN', 'route' => '/stock-in', 'icon' => 'download', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Stok Keluar', 'code' => 'STOCK_OUT', 'route' => '/stock-out', 'icon' => 'upload', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Transaksi', 'code' => 'SALES', 'route' => '/sales', 'icon' => 'shopping-cart', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Laporan', 'code' => 'REPORTS', 'route' => '/reports', 'icon' => 'bar-chart', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Notifikasi', 'code' => 'NOTIFS', 'route' => '/notifications', 'icon' => 'bell', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'User Management', 'code' => 'USER_MANAGE', 'route' => '/users', 'icon' => 'users', 'created_at' => now(), 'updated_at' => now()],
            [
                'code'  => 'ROLE_MANAGE',
                'name'  => 'Role Management',
                'route' => '/roles',
                'icon'  => 'shield',
                'order' => 0,
            ]
        ]);
    }
}
