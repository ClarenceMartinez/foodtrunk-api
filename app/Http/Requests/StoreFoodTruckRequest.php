<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFoodTruckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-food-trucks');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'cuisine_type' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'logo_url' => ['nullable', 'url'],
            'cover_image_url' => ['nullable', 'url'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'pending'])],
        ];
    }
}
