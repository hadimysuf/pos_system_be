<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * GET /admin/roles
     */
    public function index()
    {
        return response()->json([
            'data' => Role::orderBy('id')->get()
        ]);
    }

    /**
     * POST /admin/roles
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
        ]);

        $role = Role::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Role created',
            'data' => $role
        ], 201);
    }

    /**
     * GET /admin/roles/{role}
     */
    public function show(Role $role)
    {
        return response()->json([
            'data' => $role
        ]);
    }

    /**
     * PUT /admin/roles/{role}
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name,' . $role->id,
        ]);

        $role->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Role updated',
            'data' => $role
        ]);
    }

    /**
     * DELETE /admin/roles/{role}
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            'message' => 'Role deleted'
        ]);
    }
}
