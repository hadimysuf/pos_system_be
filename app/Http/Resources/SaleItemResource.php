<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product->name ?? null,

            'quantity' => $this->quantity,
            'price' => (float) $this->price,
            'cost' => (float) $this->cost,
            'subtotal' => (float) $this->subtotal,
            'profit' => (float) $this->profit,
        ];
    }
}
