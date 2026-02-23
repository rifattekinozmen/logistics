<?php

namespace App\Enums;

enum AddressType: string
{
    case Pickup = 'pickup';
    case Delivery = 'delivery';
    case Both = 'both';
    case TeslimatNoktasi = 'teslimat_noktasi';
    case Depo = 'depo';
    case Merkez = 'merkez';
    case Ofis = 'ofis';
    case Fabrika = 'fabrika';
    case Sube = 'sube';
    case Liman = 'liman';
    case Showroom = 'showroom';

    /**
     * Türkçe etiket.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pickup => 'Alış Adresi',
            self::Delivery => 'Teslimat Adresi',
            self::Both => 'Her İkisi',
            self::TeslimatNoktasi => 'Teslimat Noktası',
            self::Depo => 'Depo',
            self::Merkez => 'Merkez',
            self::Ofis => 'Ofis',
            self::Fabrika => 'Fabrika',
            self::Sube => 'Şube',
            self::Liman => 'Liman',
            self::Showroom => 'Showroom',
        };
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

    /**
     * Validasyon için tüm değerler.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * String değerden etiket döndür.
     */
    public static function labelFor(?string $value): string
    {
        if (! $value) {
            return '-';
        }

        $enum = self::tryFrom($value);

        return $enum?->label() ?? $value;
    }
}
