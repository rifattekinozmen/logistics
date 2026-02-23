<?php

namespace App\Enums;

enum MedeniDurum: string
{
    case Bekar = 'Bekar';
    case Evli = 'Evli';
    case Dul = 'Dul';
    case Bosanmis = 'Boşanmış';

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
