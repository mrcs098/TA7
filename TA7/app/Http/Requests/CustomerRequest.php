<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($this->route('customer'))],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'required', Rule::in(['Active', 'Inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Please enter the customer\'s full name.',
            'email.required' => 'Please enter an email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already used by another customer.',
        ];
    }

    public function attributes(): array
    {
        return ['full_name' => 'full name'];
    }
}
