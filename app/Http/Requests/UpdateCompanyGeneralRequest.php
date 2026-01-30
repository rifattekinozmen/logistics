<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyGeneralRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $company = $this->route('company');
        $companyId = $company instanceof \App\Models\Company ? $company->id : $company;

        return [
            // Temel bilgiler
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            
            // Vergi bilgileri
            'tax_number' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('companies', 'tax_number')->ignore($companyId),
            ],
            'tax_office' => 'required|string|max:255',
            'tax_office_city_id' => 'required|exists:cities,id',
            'mersis_no' => 'nullable|string|max:20',
            'trade_registry_no' => 'nullable|string|max:50',
            
            // Lokasyon bilgileri
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'required|exists:districts,id',
            'address' => 'required|string',
            'postal_code' => 'nullable|string|max:10',
            'headquarters_city' => 'required|string|max:255',
            
            // İletişim bilgileri
            'mobile_phone' => 'required|string|max:20',
            'landline_phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'authorized_email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            
            // Yetkili kişi
            'authorized_person_name' => 'required|string|max:255',
            
            // e-Fatura ve e-İrsaliye
            'e_invoice_pk_tag' => 'nullable|string|max:255',
            'e_waybill_pk_tag' => 'nullable|string|max:255',
            'e_invoice_gb_tag' => 'nullable|string|max:255',
            'e_waybill_gb_tag' => 'nullable|string|max:255',
            
            // Diğer
            'capital_amount' => 'nullable|numeric|min:0',
            'api_key' => 'nullable|string|max:255',
            'currency' => 'required|string|size:3',
            'default_vat_rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Ticari Ünvan / Ad Soyad alanı zorunludur.',
            'tax_number.required' => 'VKN/TCKN alanı zorunludur.',
            'tax_office.required' => 'Vergi Dairesi Adı alanı zorunludur.',
            'tax_office_city_id.required' => 'Vergi Dairesi İli alanı zorunludur.',
            'tax_office_city_id.exists' => 'Seçilen vergi dairesi ili geçersiz.',
            'country_id.required' => 'Ülke alanı zorunludur.',
            'country_id.exists' => 'Seçilen ülke geçersiz.',
            'city_id.required' => 'İl alanı zorunludur.',
            'city_id.exists' => 'Seçilen il geçersiz.',
            'district_id.required' => 'İlçe alanı zorunludur.',
            'district_id.exists' => 'Seçilen ilçe geçersiz.',
            'address.required' => 'Adres alanı zorunludur.',
            'headquarters_city.required' => 'İşletme Merkezi alanı zorunludur.',
            'mobile_phone.required' => 'Cep Telefonu alanı zorunludur.',
            'authorized_email.required' => 'Yetkili e-Posta alanı zorunludur.',
            'authorized_email.email' => 'Yetkili e-Posta geçerli bir e-posta adresi olmalıdır.',
            'authorized_person_name.required' => 'Ad Soyad alanı zorunludur.',
            'website.url' => 'İnternet Sitesi geçerli bir URL olmalıdır.',
        ];
    }
}
