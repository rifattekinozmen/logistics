<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'ad_soyad',
        'tckn',
        'kimlik_seri_no',
        'email',
        'telefon',
        'mobil_telefon',
        'acil_iletisim',
        'anne_adi',
        'baba_adi',
        'dogum_tarihi',
        'dogum_yeri',
        'medeni_durum',
        'departman',
        'pozisyon',
        'ise_baslama_tarihi',
        'maas',
        'aktif',
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
            'maas' => 'decimal:2',
            'aktif' => 'boolean',
        ];
    }

    /**
     * Get the personnel attendance records for the personel.
     */
    public function personnelAttendances(): HasMany
    {
        return $this->hasMany(PersonnelAttendance::class);
    }
}
