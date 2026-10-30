<?php

namespace App\Http\Requests\Product;

use App\Models\Business;
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
        /** @var Business $business */
        $business = $this->route('business');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'sku' => [
                'required', 'string', 'max:255',
                Rule::unique('products', 'sku')->where('business_id', $business->id),
            ],
            'barcode' => [
                'nullable', 'string', 'max:255',
                Rule::unique('products', 'barcode')->where('business_id', $business->id),
            ],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('business_id', $business->id),
            ],
            'unit_of_measurement_id' => [
                'nullable',
                Rule::exists('units_of_measurement', 'id')->where('business_id', $business->id),
            ],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:physical,composite,ingredient'],
            'purchase_cost' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'track_inventory' => ['boolean'],
            'is_active' => ['boolean'],
            'has_variants' => ['boolean'],
            'allow_backorder' => ['boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string', 'max:2048'],
            'image' => ['nullable', 'image', 'max:4096'],
            'attributes' => ['nullable', 'array'],
            'min_stock_level' => ['nullable', 'integer', 'min:0'],
            'max_stock_level' => ['nullable', 'integer', 'min:0', 'gte:min_stock_level'],
        ];
    }
}
