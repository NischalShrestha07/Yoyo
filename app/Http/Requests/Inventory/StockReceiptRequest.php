<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $business = $this->route('business');

        return [
            'product_id' => ['required', Rule::exists('products', 'id')->where('business_id', $business->id)],
            'product_variant_id' => ['nullable', Rule::exists('product_variants', 'id')->where('business_id', $business->id)],
            'stock_location_id' => ['required', Rule::exists('stock_locations', 'id')->where('business_id', $business->id)],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'batch_info' => ['nullable', 'array'],
            'serial_info' => ['nullable', 'array'],
            'serial_info_text' => ['nullable', 'string'],
        ];
    }
}
