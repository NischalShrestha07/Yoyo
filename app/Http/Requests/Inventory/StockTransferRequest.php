<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockTransferRequest extends FormRequest
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
            'from_location_id' => ['required', Rule::exists('stock_locations', 'id')->where('business_id', $business->id)],
            'to_location_id' => ['required', Rule::exists('stock_locations', 'id')->where('business_id', $business->id), 'different:from_location_id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
