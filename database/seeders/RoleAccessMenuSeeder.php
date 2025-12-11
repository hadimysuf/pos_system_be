<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleAccessMenuSeeder extends Seeder
{
    public function run(): void
    {
        $roles = DB::table('roles')->pluck('id', 'name');   // Admin, Kasir, Gudang
        $menus = DB::table('menus')->pluck('id', 'code');   // DASHBOARD, PRODUCT_MANAGE, etc.

        /**
         * Daftar permission per role
         * Format: 'CODE_MENU' => [view, create, update, delete]
         */
        $rolePermissions = [
            'Admin' => [
                'DASHBOARD'        => [1, 1, 1, 1],
                'PRODUCT_MANAGE'   => [1, 1, 1, 1],
                'CATEGORY_MANAGE'  => [1, 1, 1, 1],
                'STOCK_IN'         => [1, 1, 1, 1],
                'STOCK_OUT'        => [1, 1, 1, 1],
                'SALES'            => [1, 1, 1, 1],
                'REPORTS'          => [1, 1, 1, 1],
                'NOTIFS'           => [1, 1, 1, 1],
                'USER_MANAGE'      => [1, 1, 1, 1],
            ],

            'Kasir' => [
                'DASHBOARD'        => [1, 0, 0, 0],
                'SALES'            => [1, 1, 1, 0],
                'NOTIFS'           => [1, 0, 0, 0],
            ],

            'Gudang' => [
                'DASHBOARD'        => [1, 0, 0, 0],
                'PRODUCT_MANAGE'   => [1, 0, 1, 0],
                'STOCK_IN'         => [1, 1, 1, 0],
                'STOCK_OUT'        => [1, 1, 1, 0],
                'NOTIFS'           => [1, 0, 0, 0],
            ],
        ];

        // Insert ke tabel pivot role_access_menu
        foreach ($rolePermissions as $roleName => $permissions) {
            $roleId = $roles[$roleName];

            foreach ($permissions as $menuCode => $perm) {
                $menuId = $menus[$menuCode];

                DB::table('role_access_menu')->insert([
                    'role_id'    => $roleId,
                    'menu_id'    => $menuId,
                    'can_view'   => $perm[0],
                    'can_create' => $perm[1],
                    'can_update' => $perm[2],
                    'can_delete' => $perm[3],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
