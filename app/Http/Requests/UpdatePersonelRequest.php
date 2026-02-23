<?php

namespace App\Http\Requests;

use App\Enums\AskerlikDurumu;
use App\Enums\Cinsiyet;
use App\Enums\KanGrubu;
use App\Enums\MaasOdemeTuru;
use App\Enums\MedeniDurum;
use App\Enums\TahsilDurumu;
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
        $personnel = $this->route('personnel');

        return [
            'personel_kodu' => ['nullable', 'string', 'max:50', Rule::unique('personels', 'personel_kodu')->ignore($personnel)],
            'ad_soyad' => ['required', 'string', 'max:255'],
            'tckn' => ['nullable', 'string', 'size:11', 'regex:/^[0-9]+$/'],
            'kimlik_seri_no' => ['nullable', 'string', 'max:50'],
            'cilt_no' => ['nullable', 'string', 'max:20'],
            'aile_sira_no' => ['nullable', 'string', 'max:20'],
            'sira_no' => ['nullable', 'string', 'max:20'],
            'cuzdan_kayit_no' => ['nullable', 'string', 'max:20'],
            'verilis_tarihi' => ['nullable', 'date'],
            'son_gecerlilik_tarihi' => ['nullable', 'date'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('personels')->ignore($personnel)],
            'telefon' => ['nullable', 'string', 'max:20'],
            'mobil_telefon' => ['nullable', 'string', 'max:20'],
            'acil_iletisim' => ['nullable', 'string', 'max:20'],
            'anne_adi' => ['nullable', 'string', 'max:255'],
            'baba_adi' => ['nullable', 'string', 'max:255'],
            'dogum_tarihi' => ['nullable', 'date'],
            'dogum_yeri' => ['nullable', 'string', 'max:255'],
            'medeni_durum' => ['nullable', Rule::in(array_column(MedeniDurum::cases(), 'value'))],
            'kan_grubu' => ['nullable', Rule::in(array_column(KanGrubu::cases(), 'value'))],
            'cinsiyet' => ['nullable', Rule::in(array_column(Cinsiyet::cases(), 'value'))],
            'cocuk_sayisi' => ['nullable', 'integer', 'min:0', 'max:20'],
            'adres_satir_1' => ['nullable', 'string', 'max:255'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'bulvar' => ['nullable', 'string', 'max:255'],
            'sokak' => ['nullable', 'string', 'max:255'],
            'dis_kapi' => ['nullable', 'string', 'max:20'],
            'ic_kapi' => ['nullable', 'string', 'max:20'],
            'posta_kodu' => ['nullable', 'string', 'max:20'],
            'departman' => ['required', 'string', 'max:255'],
            'pozisyon' => ['required', 'string', 'max:255'],
            'ise_baslama_tarihi' => ['required', 'date'],
            'sgk_baslangic_tarihi' => ['nullable', 'date'],
            'maas' => ['nullable', 'numeric', 'min:0'],
            'tahsil_durumu' => ['nullable', Rule::in(array_column(TahsilDurumu::cases(), 'value'))],
            'mezun_okul' => ['nullable', 'string', 'max:255'],
            'mezun_bolum' => ['nullable', 'string', 'max:255'],
            'mezuniyet_tarihi' => ['nullable', 'date'],
            'bildigi_dil' => ['nullable', 'string', 'max:100'],
            'askerlik_durumu' => ['nullable', Rule::in(array_column(AskerlikDurumu::cases(), 'value'))],
            'sgk_yaslilik_ayligi' => ['nullable', 'boolean'],
            'sgk_30_gunden_az' => ['nullable', 'boolean'],
            'banka_adi' => ['nullable', 'string', 'max:100'],
            'sube_kodu' => ['nullable', 'string', 'max:20'],
            'hesap_no' => ['nullable', 'string', 'max:50'],
            'maas_odeme_turu' => ['nullable', Rule::in(array_column(MaasOdemeTuru::cases(), 'value'))],
            'iban' => ['nullable', 'string', 'max:34', 'regex:/^[A-Z]{2}[0-9]{2}[A-Z0-9]{4}[0-9]{7}([A-Z0-9]?){0,16}$/i'],
            'aktif' => ['nullable', 'boolean'],
            'notlar' => ['nullable', 'string', 'max:65535'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
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
            'personel_kodu.unique' => 'Bu personel kodu zaten kullanılıyor.',
            'departman.required' => 'Departman alanı zorunludur.',
            'pozisyon.required' => 'Pozisyon alanı zorunludur.',
            'ise_baslama_tarihi.required' => 'İşe başlama tarihi zorunludur.',
            'ise_baslama_tarihi.date' => 'Geçerli bir tarih giriniz.',
            'tckn.size' => 'T.C. Kimlik No 11 haneli olmalıdır.',
            'tckn.regex' => 'T.C. Kimlik No sadece rakam içermelidir.',
            'maas.numeric' => 'Maaş sayısal bir değer olmalıdır.',
            'maas.min' => 'Maaş 0 veya daha büyük olmalıdır.',
            'iban.regex' => 'Geçerli bir IBAN formatı giriniz.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('sgk_yaslilik_ayligi') && $this->sgk_yaslilik_ayligi === '') {
            $this->merge(['sgk_yaslilik_ayligi' => null]);
        }
        if ($this->has('sgk_30_gunden_az') && $this->sgk_30_gunden_az === '') {
            $this->merge(['sgk_30_gunden_az' => null]);
        }
        if ($this->has('aktif') && $this->aktif === '') {
            $this->merge(['aktif' => true]);
        }
    }
}
