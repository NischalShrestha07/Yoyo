<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'stock_location_id' => ['required', 'exists:stock_locations,id'],
            'quantity' => ['required', 'integer'],
            'reason' => ['required', 'in:adjustment,damage,expired,theft,other'],
            'notes' => ['required', 'string'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'batch_info' => ['nullable', 'array'],
            'serial_info' => ['nullable', 'array'],
        ];
    }
}
