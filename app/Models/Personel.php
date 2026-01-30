<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'email',
        'telefon',
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
            'maas' => 'decimal:2',
            'aktif' => 'boolean',
        ];
    }
}
