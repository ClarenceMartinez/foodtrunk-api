<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOperatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-operators');
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'suspended'])],
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }
}
