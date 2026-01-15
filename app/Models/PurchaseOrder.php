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
        'expected_arrival',
        'status',
        'items',
        'total_quantity',
        'note',
    ];

    protected $casts = [
        'items' => 'array',      // JSON product list
        'order_date' => 'datetime',
        'expected_arrival' => 'datetime',
    ];

    /**
     * Supplier that provides the goods
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * PO related stock logs (optional if needed later)
     */
    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }
}
