<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Menu;
use App\Models\RoleAccessMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleAccessMenuController extends Controller
{
    /**
     * GET role access by role_id
     * /api/admin/role-access?role_id=1
     */
    public function index(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $roleId = $request->role_id;

        $menus = Menu::leftJoin('role_access_menu as ram', function ($join) use ($roleId) {
            $join->on('menus.id', '=', 'ram.menu_id')
                ->where('ram.role_id', '=', $roleId);
        })
            ->select(
                'menus.id as menu_id',
                'menus.name',
                'menus.code',
                'menus.route',
                'menus.icon',
                'menus.order',


                DB::raw('COALESCE(ram.can_view, 0) as can_view'),
                DB::raw('COALESCE(ram.can_create, 0) as can_create'),
                DB::raw('COALESCE(ram.can_update, 0) as can_update'),
                DB::raw('COALESCE(ram.can_delete, 0) as can_delete')
            )
            ->orderBy('menus.order')
            ->get();

        return response()->json([
            'role_id' => $roleId,
            'menus'   => $menus
        ]);
    }

    /**
     * POST save/update role access
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'menus'   => 'required|array'
        ]);

        foreach ($request->menus as $menu) {
            RoleAccessMenu::updateOrCreate(
                [
                    'role_id' => $request->role_id,
                    'menu_id' => $menu['menu_id'],
                ],
                [
                    'can_view'   => $menu['can_view'] ?? false,
                    'can_create' => $menu['can_create'] ?? false,
                    'can_update' => $menu['can_update'] ?? false,
                    'can_delete' => $menu['can_delete'] ?? false,
                ]
            );
        }

        return response()->json([
            'message' => 'Role access updated successfully'
        ]);
    }
}
