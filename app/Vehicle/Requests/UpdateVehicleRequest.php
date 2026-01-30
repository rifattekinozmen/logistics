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
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'nullable|integer|min:1900|max:'.date('Y'),
            'vehicle_type' => 'required|string|in:truck,van,car,trailer',
            'capacity_kg' => 'nullable|numeric|min:0',
            'capacity_m3' => 'nullable|numeric|min:0',
            'status' => 'required|integer|in:0,1,2',
            'branch_id' => 'nullable|exists:branches,id',
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
        ];
    }
}
