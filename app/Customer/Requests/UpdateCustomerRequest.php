<?php

namespace App\Customer\Requests;

use App\Enums\CustomerPriority;
use App\Enums\CustomerType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_type' => $this->customer_type ?: null,
            'priority_level' => $this->priority_level ?: null,
            'tax_office_id' => $this->tax_office_id ?: null,
        ]);
    }

    public function rules(): array
    {
        $customerTypes = implode(',', array_column(CustomerType::cases(), 'value'));
        $priorities = implode(',', array_column(CustomerPriority::cases(), 'value'));

        return [
            'name' => 'required|string|max:255',
            'customer_code' => 'nullable|string|max:50',
            'customer_type' => 'nullable|string|in:'.$customerTypes,
            'priority_level' => 'nullable|string|in:'.$priorities,
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'tax_office' => 'nullable|string|max:100',
            'tax_office_id' => 'nullable|exists:tax_offices,id',
            'address' => 'nullable|string|max:1000',
            'status' => 'required|integer|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Müşteri adı zorunludur.',
            'status.required' => 'Durum alanı zorunludur.',
        ];
    }
}
