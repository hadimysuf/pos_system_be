<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleAccessMenuSeeder extends Seeder
{
    public function run(): void
    {
        $roles = DB::table('roles')->pluck('id', 'name');
        $menus = DB::table('menus')->pluck('id', 'code');

        $rolePermissions = [
            'Admin' => [
                // FULL ACCESS KE SEMUA MENU
                'DASHBOARD'               => [1,1,1,1],
                'PRODUCT_MANAGE'          => [1,1,1,1],
                'CATEGORY_MANAGE'         => [1,1,1,1],
                'STOCK_IN'                => [1,1,1,1],
                'STOCK_OUT'               => [1,1,1,1],
                'PURCHASE_ORDERS'         => [1,1,1,1],
                'WAREHOUSE_ACCESS'        => [1,1,1,1],
                'SALES'                   => [1,1,1,1],
                'REPORTS'                 => [1,1,1,1],
                'NOTIFS'                  => [1,1,1,1],
                'USER_MANAGE'             => [1,1,1,1],
                'ROLE_MANAGE'             => [1,1,1,1],
                'ROLE_ACCESS'             => [1,1,1,1],
                'SUPPLIER_MANAGE'         => [1,1,1,1],
                'STOCK_LOGS'              => [1,1,1,1],
                'RESTOCK_RECOMMENDATION'  => [1,1,1,1],
            ],

            'Kasir' => [
                'DASHBOARD' => [1,0,0,0],
                'SALES'     => [1,1,1,0],
                'NOTIFS'    => [1,0,0,0],
            ],

            'Gudang' => [
                'DASHBOARD'               => [1,0,0,0],
                'PRODUCT_MANAGE'          => [1,0,1,0],
                'STOCK_IN'                => [1,1,1,0],
                'STOCK_OUT'               => [1,1,1,0],
                'PURCHASE_ORDERS'         => [1,1,1,0],
                'WAREHOUSE_ACCESS'        => [1,1,1,1],
                'STOCK_LOGS'              => [1,1,0,0],
                'RESTOCK_RECOMMENDATION'  => [1,0,0,0],
                'NOTIFS'                  => [1,0,0,0],
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $roleId = $roles[$roleName] ?? null;
            if (!$roleId) continue;

            foreach ($permissions as $menuCode => $perm) {
                $menuId = $menus[$menuCode] ?? null;
                if (!$menuId) continue;

                DB::table('role_access_menu')->updateOrInsert(
                    [
                        'role_id' => $roleId,
                        'menu_id' => $menuId,
                    ],
                    [
                        'can_view'   => $perm[0],
                        'can_create' => $perm[1],
                        'can_update' => $perm[2],
                        'can_delete' => $perm[3],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
