<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $categoryId = $this->route('category') ? $this->route('category')->id ?? null : null;

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('categories', 'name')->ignore($categoryId)],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'description' => ['nullable', 'string']
        ];
    }
}
