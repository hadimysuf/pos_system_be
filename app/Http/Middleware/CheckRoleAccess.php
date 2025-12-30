<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\RoleAccessMenu;

class CheckRoleAccess
{
    public function handle(Request $request, Closure $next, $menuCode, $permission = 'can_view')
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $hasAccess = RoleAccessMenu::where('role_id', $user->role_id)
            ->whereHas('menu', function ($q) use ($menuCode) {
                $q->where('code', $menuCode);
            })
            ->where($permission, true)
            ->exists();

        if (!$hasAccess) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return $next($request);
    }
}
