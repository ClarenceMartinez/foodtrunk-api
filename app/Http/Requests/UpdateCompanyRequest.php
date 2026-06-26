<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Platform Owner puede editar cualquier empresa.
        // Company Admin solo puede editar la suya (ya filtrado por el scope).
        return $this->user()->can('manage-companies')
            || $this->user()->hasRole('company-admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('companies', 'email')->ignore($this->route('company'))],
            'phone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:2'],
            'logo_url' => ['nullable', 'url'],
        ];
    }
}
