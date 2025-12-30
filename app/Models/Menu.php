<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Role;

class Menu extends Model
{
    protected $fillable = [

        'code',
        'name',
        'route',
        'icon',
        'order'

    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_access_menu')
            ->withPivot(['can_view', 'can_create', 'can_update', 'can_delete'])
            ->withTimestamps();
    }

    // public function access()
    // {
    //     return $this->hasMany(RoleAccessMenu::class);
    // }
}
