<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->latest()->paginate(10);
        return UserResource::collection($users);
    }

    public function store(UserRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'role_id'  => $request->role_id,
            'is_active' => true
        ]);

        return new UserResource($user->load('role'));
    }

    public function show(User $user)
    {
        return new UserResource($user->load('role'));
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();

        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return new UserResource($user->load('role'));
    }

    public function destroy(User $user)
    {
        $user->update(['is_active' => false]);

        return response()->json([
            'message' => 'User deactivated'
        ]);
    }
}
