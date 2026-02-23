<?php

namespace App\Enums;

enum KanGrubu: string
{
    case APozitif = 'A+';
    case ANegatif = 'A-';
    case BPozitif = 'B+';
    case BNegatif = 'B-';
    case ABPozitif = 'AB+';
    case ABNegatif = 'AB-';
    case OPozitif = 'O+';
    case ONegatif = 'O-';

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
