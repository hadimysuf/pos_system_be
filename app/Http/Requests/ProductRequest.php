<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
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
