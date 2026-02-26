<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Personel extends Model
{
    /** @use HasFactory<\Database\Factories\PersonelFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'personel_kodu',
        'sirket_vergi_no',
        'sirket_sgk_no',
        'sirket_sicil_no',
        'sirket_unvani',
        'ad_soyad',
        'photo_path',
        'tckn',
        'pasaport_seri_no',
        'kimlik_seri_no',
        'cilt_no',
        'aile_sira_no',
        'sira_no',
        'cuzdan_kayit_no',
        'verilis_tarihi',
        'son_gecerlilik_tarihi',
        'email',
        'telefon',
        'mobil_telefon',
        'acil_iletisim',
        'anne_adi',
        'baba_adi',
        'dogum_tarihi',
        'dogum_yeri',
        'medeni_durum',
        'kan_grubu',
        'cinsiyet',
        'cocuk_sayisi',
        'adres_satir_1',
        'adres_satir_2',
        'country_id',
        'city_id',
        'district_id',
        'mahalle',
        'bulvar',
        'sokak',
        'cadde',
        'dis_kapi',
        'ic_kapi',
        'posta_kodu',
        'departman',
        'pozisyon',
        'ise_baslama_tarihi',
        'basvuru_tarihi',
        'referans_tarihi',
        'sgk_baslangic_tarihi',
        'maas',
        'tahsil_durumu',
        'mezun_okul',
        'mezun_bolum',
        'mezuniyet_tarihi',
        'bildigi_dil',
        'askerlik_durumu',
        'askerlik_turu',
        'askerlik_baslangic_tarihi',
        'askerlik_bitis_tarihi',
        'sgk_yaslilik_ayligi',
        'sgk_30_gunden_az',
        'sgk_sigorta_kodu',
        'sgk_sigorta_adi',
        'csgb_is_kolu_kodu',
        'csgb_is_kolu_adi',
        'kanun_2821_gorev_kodu',
        'kanun_2821_gorev_adi',
        'meslek_kodu',
        'meslek_adi',
        'banka_adi',
        'sube_kodu',
        'hesap_no',
        'maas_odeme_turu',
        'iban',
        'aktif',
        'calisma_durumu',
        'notlar',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ise_baslama_tarihi' => 'date',
            'dogum_tarihi' => 'date',
            'sgk_baslangic_tarihi' => 'date',
            'verilis_tarihi' => 'date',
            'son_gecerlilik_tarihi' => 'date',
            'mezuniyet_tarihi' => 'date',
            'basvuru_tarihi' => 'date',
            'referans_tarihi' => 'date',
            'askerlik_baslangic_tarihi' => 'date',
            'askerlik_bitis_tarihi' => 'date',
            'maas' => 'decimal:2',
            'aktif' => 'boolean',
            'sgk_yaslilik_ayligi' => 'boolean',
            'sgk_30_gunden_az' => 'boolean',
        ];
    }

    /**
     * Kimlik kartı için ad (soyad hariç).
     */
    public function getAdiAttribute(): string
    {
        $parts = preg_split('/\s+/u', trim($this->attributes['ad_soyad'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) <= 1) {
            return $this->attributes['ad_soyad'] ?? '';
        }

        return implode(' ', array_slice($parts, 0, -1));
    }

    /**
     * Kimlik kartı için soyad (son kelime).
     */
    public function getSoyadiAttribute(): string
    {
        $parts = preg_split('/\s+/u', trim($this->attributes['ad_soyad'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);

        return $parts ? end($parts) : ($this->attributes['ad_soyad'] ?? '');
    }

    /**
     * Get the country of the address.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the city of the address.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the district of the address.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the personnel attendance records for the personel.
     */
    public function personnelAttendances(): HasMany
    {
        return $this->hasMany(PersonnelAttendance::class);
    }

    /**
     * Get the documents for the personel.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
