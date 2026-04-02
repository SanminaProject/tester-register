<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTesterCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customerRoute = $this->route('customer');
        $customerId = is_object($customerRoute) ? $customerRoute->getKey() : $customerRoute;

        return [
            'company_name' => ['sometimes', 'string', 'max:255', Rule::unique('tester_customers', 'company_name')->ignore($customerId)],
            'address' => ['sometimes', 'string', 'max:255'],
            'contact_person' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:50', 'regex:/^[0-9\-\+\(\)\ ]+$/'],
            'email' => ['sometimes', 'email', 'max:255'],
        ];
    }
}
