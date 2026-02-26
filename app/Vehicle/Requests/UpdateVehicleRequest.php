<?php

namespace App\Vehicle\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vehicleId = $this->route('vehicle') ?? $this->route('id');

        return [
            'plate' => [
                'required',
                'string',
                'max:20',
                Rule::unique('vehicles', 'plate')->ignore($vehicleId),
            ],
            'license_number' => 'nullable|string|max:255',
            'brand' => 'required|string|max:100',
            'new_brand' => 'nullable|string|max:100|required_if:brand,other',
            'series' => 'nullable|string|max:100',
            'model' => 'required|string|max:100',
            'new_model' => 'nullable|string|max:100|required_if:model,other',
            'year' => 'nullable|integer|min:1900|max:'.date('Y'),
            'color' => 'nullable|string|max:50',
            'mileage' => 'nullable|integer|min:0',
            'vehicle_type' => 'required|string|max:50',
            'vehicle_subtype' => 'nullable|string|max:50',
            'fuel_type' => 'nullable|string|in:gasoline,diesel,electric,hybrid',
            'transmission' => 'nullable|string|in:manual,automatic,other',
            'owner_type' => 'nullable|string|in:owned,rented',
            'engine_number' => 'nullable|string|max:100',
            'vin_number' => 'nullable|string|max:32',
            'hgs_number' => 'nullable|string|max:100',
            'hgs_bank' => 'nullable|string|max:50',
            'capacity_kg' => 'nullable|numeric|min:0',
            'capacity_m3' => 'nullable|numeric|min:0',
            'status' => 'required|integer|in:0,1,2',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'plate.required' => 'Plaka numarası zorunludur.',
            'plate.unique' => 'Bu plaka numarası zaten kayıtlı.',
            'brand.required' => 'Marka zorunludur.',
            'model.required' => 'Model zorunludur.',
            'vehicle_type.required' => 'Araç tipi zorunludur.',
            'vehicle_type.in' => 'Geçersiz araç tipi.',
            'new_brand.required_if' => 'Yeni marka adı zorunludur.',
            'new_model.required_if' => 'Yeni model adı zorunludur.',
        ];
    }
}
