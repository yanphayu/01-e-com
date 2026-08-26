<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'condition' => ['nullable', 'in:new,used,refurbished'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'location' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['string'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
