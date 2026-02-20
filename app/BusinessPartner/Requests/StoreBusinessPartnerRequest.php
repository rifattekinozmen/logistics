<?php

namespace App\BusinessPartner\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessPartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|integer|exists:companies,id',
            'partner_type' => 'required|string|in:customer,vendor,carrier,both',
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'tax_office' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'currency' => 'required|string|size:3',
            'payment_terms' => 'nullable|string|in:NET30,NET60,NET90,IMMEDIATE',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'required|integer|in:0,1',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'partner_type.required' => 'İş ortağı türü zorunludur.',
            'partner_type.in' => 'Geçersiz iş ortağı türü.',
            'name.required' => 'İş ortağı adı zorunludur.',
            'currency.size' => 'Para birimi 3 karakter olmalıdır (örn. TRY, USD).',
        ];
    }
}
