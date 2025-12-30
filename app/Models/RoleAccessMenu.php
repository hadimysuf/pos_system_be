<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Menu;

class RoleAccessMenu extends Model
{
    protected $table = 'role_access_menu';

    protected $fillable = [
        'role_id',
        'menu_id',
        'can_view',
        'can_create',
        'can_update',
        'can_delete',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
