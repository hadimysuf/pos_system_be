<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'sale_date' => $this->sale_date,
            'payment_method' => $this->payment_method,

            // 💰 Ringkasan Keuangan
            'total_amount' => (float) $this->total_amount,
            'total_cost' => (float) $this->total_cost,
            'profit' => (float) $this->profit,

            // 📦 Detail item (jika diload)
            'items' => SaleItemResource::collection(
                $this->whenLoaded('items')
            ),

            // 🕒 Timestamp
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
