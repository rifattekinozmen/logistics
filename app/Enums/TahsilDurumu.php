<?php

namespace App\Enums;

enum TahsilDurumu: string
{
    case Ilkokul = 'İlkokul';
    case Ortaokul = 'Ortaokul';
    case Lise = 'Lise';
    case OnLisans = 'Ön Lisans';
    case Lisans = 'Lisans';
    case YuksekLisans = 'Yüksek Lisans';
    case Doktora = 'Doktora';
    case Mezun = 'Mezun';

    /**
     * Türkçe etiket.
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Select için [value => label] array.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
