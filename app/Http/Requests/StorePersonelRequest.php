<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonelRequest extends FormRequest
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
        return [
            'ad_soyad' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:personels,email'],
            'telefon' => ['nullable', 'string', 'max:20'],
            'departman' => ['required', 'string', 'max:255'],
            'pozisyon' => ['required', 'string', 'max:255'],
            'ise_baslama_tarihi' => ['required', 'date'],
            'maas' => ['nullable', 'numeric', 'min:0'],
            'aktif' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ad_soyad.required' => 'Ad Soyad alanı zorunludur.',
            'email.required' => 'E-posta alanı zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta adresi zaten kullanılıyor.',
            'departman.required' => 'Departman alanı zorunludur.',
            'pozisyon.required' => 'Pozisyon alanı zorunludur.',
            'ise_baslama_tarihi.required' => 'İşe başlama tarihi zorunludur.',
            'ise_baslama_tarihi.date' => 'Geçerli bir tarih giriniz.',
            'maas.numeric' => 'Maaş sayısal bir değer olmalıdır.',
            'maas.min' => 'Maaş 0 veya daha büyük olmalıdır.',
        ];
    }
}
