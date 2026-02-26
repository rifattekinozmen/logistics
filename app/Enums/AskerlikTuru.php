<?php

namespace App\Enums;

enum AskerlikTuru: string
{
    case KisaDonem = 'kisa_donem';
    case UzunDonem = 'uzun_donem';
    case Bedelli = 'bedelli';
    case YedekSubay = 'yedek_subay';

    public function label(): string
    {
        return match ($this) {
            self::KisaDonem => 'Kısa Dönem',
            self::UzunDonem => 'Uzun Dönem',
            self::Bedelli => 'Bedelli',
            self::YedekSubay => 'Yedek Subay',
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

