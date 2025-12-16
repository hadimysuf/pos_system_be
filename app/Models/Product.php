<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'stock',
        'low_stock_threshold',
        'price',
        'cost',
        'image',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->sku)) {
                $lastId = self::max('id') + 1;
                $product->sku = 'PRD-' . str_pad($lastId, 6, '0', STR_PAD_LEFT);
            }
        });
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->low_stock_threshold;
    }
}
