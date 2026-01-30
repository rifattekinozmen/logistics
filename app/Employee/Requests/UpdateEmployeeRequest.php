<?php

namespace App\Employee\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee') ?? $this->route('id');

        return [
            'user_id' => 'nullable|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'position_id' => 'nullable|exists:positions,id',
            'employee_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('employees', 'employee_number')->ignore($employeeId),
            ],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'salary' => 'nullable|numeric|min:0',
            'hire_date' => 'required|date',
            'status' => 'required|integer|in:0,1,2',
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'Şube seçimi zorunludur.',
            'branch_id.exists' => 'Seçilen şube bulunamadı.',
            'first_name.required' => 'Ad zorunludur.',
            'last_name.required' => 'Soyad zorunludur.',
            'hire_date.required' => 'İşe başlama tarihi zorunludur.',
            'employee_number.unique' => 'Bu personel numarası zaten kayıtlı.',
        ];
    }
}
