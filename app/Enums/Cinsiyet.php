<?php

namespace App\Enums;

enum Cinsiyet: string
{
    case Erkek = 'Erkek';
    case Kadin = 'Kadın';

    /**
     * Türkçe etiket.
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Kimlik kartı için kısa format (E/M, K/F).
     */
    public function idCardShort(): string
    {
        return match ($this) {
            self::Erkek => 'E / M',
            self::Kadin => 'K / F',
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
}
