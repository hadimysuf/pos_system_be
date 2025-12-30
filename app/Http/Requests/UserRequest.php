<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function rules()
    {
        $userId = $this->route('user')?->id;

        return [
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:users,email,' . $userId,
            'password' => $userId ? 'nullable|min:6' : 'required|min:6',
            'role_id'  => 'required|exists:roles,id',
        ];
    }
}
