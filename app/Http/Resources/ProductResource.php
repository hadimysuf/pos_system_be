<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ],
            'stock' => $this->stock,
            'low_stock_threshold' => $this->low_stock_threshold,
            'is_low_stock' => $this->isLowStock(),
            'price' => $this->price,
            'cost' => $this->cost,
            'profit_per_item' => $this->price - $this->cost,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
