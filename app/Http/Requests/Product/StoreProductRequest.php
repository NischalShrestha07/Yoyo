<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $business = $this->route('business');
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', Rule::unique('products')->where(function ($query) use ($business) {
                return $query->where('business_id', $business->id)->whereNull('deleted_at');
            })],
            'barcode' => ['nullable', 'string', 'max:255', Rule::unique('products')->where(function ($query) use ($business) {
                return $query->where('business_id', $business->id)->whereNull('deleted_at');
            })],
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit_of_measurement_id' => ['nullable', 'exists:units_of_measurement,id'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:physical,composite,ingredient'],
            'purchase_cost' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'track_inventory' => ['boolean'],
            'is_active' => ['boolean'],
            'has_variants' => ['boolean'],
            'allow_backorder' => ['boolean'],
            'images' => ['nullable', 'array'],
            'attributes' => ['nullable', 'array'],
            'min_stock_level' => ['nullable', 'integer', 'min:0'],
            'max_stock_level' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
