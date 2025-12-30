<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ReportController;

class SaleReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        
        return [
            'invoice_number' => $this->invoice_number,
            'sale_date'      => $this->sale_date,
            'total_amount'   => $this->total_amount,
            'total_cost'     => $this->total_cost,
            'profit'         => $this->profit,
        ];
    }
}

    

