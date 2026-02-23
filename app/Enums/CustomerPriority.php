<?php

namespace App\Enums;

enum CustomerPriority: string
{
    case Normal = 'Normal';
    case VIP = 'VIP';
    case Oncelikli = 'Öncelikli';

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
