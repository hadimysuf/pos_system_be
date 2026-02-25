<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run()
    {
        DB::table('menus')->insert([
            // Dashboard
            [
                'code' => 'DASHBOARD',
                'name' => 'Dashboard',
                'route' => '/dashboard',
                'icon' => 'dashboard',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Produk & Kategori
            [
                'code' => 'PRODUCT_MANAGE',
                'name' => 'Produk',
                'route' => '/products',
                'icon' => 'box',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'CATEGORY_MANAGE',
                'name' => 'Kategori',
                'route' => '/categories',
                'icon' => 'tags',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Gudang
            [
                'code' => 'STOCK_IN',
                'name' => 'Stok Masuk',
                'route' => '/stock-in',
                'icon' => 'download',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'STOCK_OUT',
                'name' => 'Stok Keluar',
                'route' => '/stock-out',
                'icon' => 'upload',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PURCHASE_ORDERS',
                'name' => 'Purchase Order',
                'route' => '/purchase-orders',
                'icon' => 'file-text',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WAREHOUSE_ACCESS',
                'name' => 'Warehouse',
                'route' => '/warehouse',
                'icon' => 'warehouse',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Kasir
            [
                'code' => 'SALES',
                'name' => 'Transaksi',
                'route' => '/sales',
                'icon' => 'shopping-cart',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Laporan
            [
                'code' => 'REPORTS',
                'name' => 'Laporan',
                'route' => '/reports',
                'icon' => 'bar-chart',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Sistem
            [
                'code' => 'NOTIFS',
                'name' => 'Notifikasi',
                'route' => '/notifications',
                'icon' => 'bell',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Admin
            [
                'code' => 'USER_MANAGE',
                'name' => 'User Management',
                'route' => '/users',
                'icon' => 'users',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ROLE_MANAGE',
                'name' => 'Role Management',
                'route' => '/roles',
                'icon' => 'shield',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'code' => 'RESTOCK_RECOMMENDATION',
                'name' => 'Restock Recommendation',
                'route' => '/warehouse/restock-recommendation',
                'icon' => 'stock',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ROLE_ACCESS',
                'name' => 'Role Access',
                'route' => '/admin/role-access',
                'icon' => 'person',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SUPPLIER_MANAGE',
                'name' => 'Supplier Manage',
                'route' => '/admin/suppliers',
                'icon' => 'UserStar',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'STOCK_LOGS',
                'name' => 'Stock Logs',
                'route' => '/gudang/logs',
                'icon' => 'items',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
