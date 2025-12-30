<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\RoleAccessMenu;

class RoleAccess
{
    public function handle(Request $request, Closure $next, $menuCode = null)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$menuCode) {
            return response()->json(['message' => 'Menu code not provided'], 403);
        }

        // 🔑 Cari menu berdasarkan CODE
        $menu = Menu::where('code', $menuCode)->first();

        if (!$menu) {
            return response()->json(['message' => 'Menu not registered'], 403);
        }

        $access = RoleAccessMenu::where('role_id', $user->role_id)
            ->where('menu_id', $menu->id)
            ->first();

        if (!$access || !$access->can_view) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return $next($request);
    }
}
