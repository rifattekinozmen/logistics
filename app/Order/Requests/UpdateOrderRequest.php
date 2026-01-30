<?php

namespace App\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy'de kontrol edilecek
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:pending,assigned,in_transit,delivered,cancelled',
            'pickup_address' => 'required|string|max:1000',
            'delivery_address' => 'required|string|max:1000',
            'planned_pickup_date' => 'nullable|date',
            'planned_delivery_date' => 'nullable|date|after:planned_pickup_date',
            'actual_pickup_date' => 'nullable|date',
            'delivered_at' => 'nullable|date',
            'total_weight' => 'nullable|numeric|min:0',
            'total_volume' => 'nullable|numeric|min:0',
            'is_dangerous' => 'boolean',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Durum seçimi zorunludur.',
            'status.in' => 'Geçersiz durum seçimi.',
            'planned_delivery_date.after' => 'Teslimat tarihi alış tarihinden sonra olmalıdır.',
        ];
    }
}
