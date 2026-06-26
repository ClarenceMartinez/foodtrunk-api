<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-plans');
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'billing_cycle' => ['sometimes', Rule::in(['monthly', 'yearly'])],
            'max_food_trucks' => ['nullable', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }
}
