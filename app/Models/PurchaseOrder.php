<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'order_date',
        'items',
        'total_quantity',
        'expected_arrival',
        'status',
        'note',
        'approved_at',
    ];

    protected $casts = [
        'items' => 'array',
        'approved_at' => 'datetime',
        'order_date' => 'date',
        'expected_arrival' => 'date',
    ];

    // ================= RELATION =================

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * PO related stock logs (optional if needed later)
     */
    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }
}
