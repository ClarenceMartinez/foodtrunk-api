<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-promotions');
    }

    public function rules(): array
    {
        return [
            'food_truck_id' => ['nullable', 'integer', 'exists:food_trucks,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['sometimes', Rule::in(['percentage', 'fixed'])],
            'discount_value' => ['sometimes', 'numeric', 'min:0'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', Rule::in(['active', 'scheduled', 'expired', 'cancelled'])],
        ];
    }
}
