<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Role extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* ===========================
       🔗 RELATION
    =========================== */

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'role_access_menu')
            ->withPivot(['can_view', 'can_create', 'can_update', 'can_delete'])
            ->withTimestamps();
    }
}
