<?php

namespace App\Enums;

enum CalismaDurumu: string
{
    case Aktif = 'aktif';
    case Deneme = 'deneme';
    case Izinli = 'izinli';
    case Ayrilan = 'ayrilan';

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::Deneme => 'Deneme Sürecinde',
            self::Izinli => 'İzinli',
            self::Ayrilan => 'Ayrılan',
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

