<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreTesterCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255|unique:tester_customers,company_name',
            'address' => 'required|string|min:3',
            'contact_person' => 'required|string|min:2',
            'phone' => 'required|string|regex:/^[0-9\-\+\(\)\ ]+$/',
            'email' => 'required|email',
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.unique' => 'Company name already exists',
            'phone.regex' => 'Phone number format is invalid',
            'email.email' => 'Email address is invalid',
        ];
    }
}
