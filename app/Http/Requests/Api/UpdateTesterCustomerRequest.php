<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTesterCustomerRequest extends FormRequest
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
        $customerId = $this->route('customer')?->id;

        return [
            'company_name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('tester_customers', 'company_name')->ignore($customerId),
            ],
            'address' => 'sometimes|string|min:3',
            'contact_person' => 'sometimes|string|min:2',
            'phone' => 'sometimes|string|regex:/^[0-9\-\+\(\)\ ]+$/',
            'email' => 'sometimes|email',
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
