<?php

namespace App\Enums;

enum MaasOdemeTuru: string
{
    case BankaHavalesi = 'Banka Havalesi';
    case Nakit = 'Nakit';
    case Cek = 'Çek';
    case Eft = 'EFT';

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
