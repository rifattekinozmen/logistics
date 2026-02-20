<?php

namespace App\Pricing\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePricingConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'condition_type' => 'required|string|in:weight_based,distance_based,flat,zone_based',
            'name' => 'required|string|max:150',
            'route_origin' => 'nullable|string|max:100',
            'route_destination' => 'nullable|string|max:100',
            'weight_from' => 'nullable|numeric|min:0',
            'weight_to' => 'nullable|numeric|min:0',
            'distance_from' => 'nullable|numeric|min:0',
            'distance_to' => 'nullable|numeric|min:0',
            'price_per_kg' => 'nullable|numeric|min:0',
            'price_per_km' => 'nullable|numeric|min:0',
            'flat_rate' => 'nullable|numeric|min:0',
            'min_charge' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'vehicle_type' => 'nullable|string|max:50',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'status' => 'required|integer|in:0,1',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'condition_type.required' => 'Fiyatlandırma koşul türü zorunludur.',
            'condition_type.in' => 'Geçersiz fiyatlandırma koşul türü.',
            'name.required' => 'Koşul adı zorunludur.',
            'currency.size' => 'Para birimi 3 karakter olmalıdır (örn. TRY, USD).',
            'valid_to.after_or_equal' => 'Bitiş tarihi, başlangıç tarihinden önce olamaz.',
        ];
    }
}
