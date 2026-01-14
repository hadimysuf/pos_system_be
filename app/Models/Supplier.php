<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'contact',
        'email',
        'address',
        'is_active'
    ];

    /**
     * Supplier punya banyak log stok
     */
    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class);
    }
}
