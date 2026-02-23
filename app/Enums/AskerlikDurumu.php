<?php

namespace App\Enums;

enum AskerlikDurumu: string
{
    case Muaf = 'Muaf';
    case Yapildi = 'Yapıldı';
    case Tecilli = 'Tecilli';
    case Er = 'Er';

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
