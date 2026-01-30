<?php

namespace App\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy'de kontrol edilecek
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'pickup_address' => 'required|string|max:1000',
            'delivery_address' => 'required|string|max:1000',
            'planned_pickup_date' => 'nullable|date',
            'planned_delivery_date' => 'nullable|date|after:planned_pickup_date',
            'total_weight' => 'nullable|numeric|min:0',
            'total_volume' => 'nullable|numeric|min:0',
            'is_dangerous' => 'boolean',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Müşteri seçimi zorunludur.',
            'customer_id.exists' => 'Seçilen müşteri bulunamadı.',
            'pickup_address.required' => 'Alış adresi zorunludur.',
            'delivery_address.required' => 'Teslimat adresi zorunludur.',
            'planned_delivery_date.after' => 'Teslimat tarihi alış tarihinden sonra olmalıdır.',
        ];
    }
}
