<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonelRequest extends FormRequest
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
            'tckn' => ['nullable', 'string', 'size:11', 'regex:/^[0-9]+$/'],
            'kimlik_seri_no' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('personels')->ignore($this->personel)],
            'telefon' => ['nullable', 'string', 'max:20'],
            'mobil_telefon' => ['nullable', 'string', 'max:20'],
            'acil_iletisim' => ['nullable', 'string', 'max:20'],
            'anne_adi' => ['nullable', 'string', 'max:255'],
            'baba_adi' => ['nullable', 'string', 'max:255'],
            'dogum_tarihi' => ['nullable', 'date'],
            'dogum_yeri' => ['nullable', 'string', 'max:255'],
            'medeni_durum' => ['nullable', 'string', 'max:50'],
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
            'tckn.size' => 'T.C. Kimlik No 11 haneli olmalıdır.',
            'tckn.regex' => 'T.C. Kimlik No sadece rakam içermelidir.',
            'maas.numeric' => 'Maaş sayısal bir değer olmalıdır.',
            'maas.min' => 'Maaş 0 veya daha büyük olmalıdır.',
        ];
    }
}
