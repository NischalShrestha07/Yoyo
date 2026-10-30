<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $business = $this->route('business');
        $service = $this->route('service');
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('services')->where(function ($query) use ($business) {
                return $query->where('business_id', $business->id)->whereNull('deleted_at');
            })->ignore($service->id)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'attributes' => ['nullable', 'array'],
        ];
    }
}
