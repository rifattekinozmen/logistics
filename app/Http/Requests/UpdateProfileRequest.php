<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        $user = $this->user();
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];

        // username kolonu varsa validation ekle
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'username')) {
            $rules['username'] = ['nullable', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)];
        }

        return $rules;
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
            'username.unique' => 'Bu kullanıcı adı zaten kullanılıyor.',
            'username.alpha_dash' => 'Kullanıcı adı sadece harf, rakam, tire ve alt çizgi içerebilir.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta adresi zaten kullanılıyor.',
            'avatar.image' => 'Avatar bir resim dosyası olmalıdır.',
            'avatar.mimes' => 'Avatar jpeg, png, jpg veya gif formatında olmalıdır.',
            'avatar.max' => 'Avatar dosyası en fazla 2MB olabilir.',
        ];
    }
}
