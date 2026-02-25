<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number',
        'user_id',
        'sale_date',
        'payment_method',
        'transaction_status',
        'items_snapshot',
        'total_amount',
        'total_cost',
        'profit',
        'order_id',
    ];

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
