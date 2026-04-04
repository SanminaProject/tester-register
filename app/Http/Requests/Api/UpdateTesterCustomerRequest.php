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
            'name' => ['sometimes', 'string', 'max:100', Rule::unique('tester_customers', 'name')->ignore($customerId)],
        ];
    }
}
