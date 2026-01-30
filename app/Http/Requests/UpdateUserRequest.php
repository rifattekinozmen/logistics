<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $user = $this->route('user');
        $userId = $user instanceof \App\Models\User ? $user->id : $user;

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($userId)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'integer', 'in:0,1'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:custom_roles,id'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $roles = $this->input('roles', []);
            
            if (empty($roles)) {
                return;
            }

            // Müşteri portalı rolleri
            $customerRoleIds = \App\Models\CustomRole::whereIn('name', ['customer', 'customer_user', 'customer_viewer'])
                ->pluck('id')
                ->toArray();
            
            // Sistem rolleri
            $systemRoleIds = \App\Models\CustomRole::whereNotIn('name', ['customer', 'customer_user', 'customer_viewer'])
                ->pluck('id')
                ->toArray();

            $hasCustomerRole = !empty(array_intersect($roles, $customerRoleIds));
            $hasSystemRole = !empty(array_intersect($roles, $systemRoleIds));

            if ($hasCustomerRole && $hasSystemRole) {
                $validator->errors()->add('roles', 'Müşteri portalı rolleri ile sistem rolleri birlikte seçilemez.');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Ad soyad zorunludur.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta adresi zaten kullanılıyor.',
            'username.unique' => 'Bu kullanıcı adı zaten kullanılıyor.',
            'username.alpha_dash' => 'Kullanıcı adı sadece harf, rakam, tire ve alt çizgi içerebilir.',
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Şifre onayı eşleşmiyor.',
            'status.required' => 'Durum seçimi zorunludur.',
            'roles.*.exists' => 'Seçilen rollerden biri geçersiz.',
        ];
    }
}
