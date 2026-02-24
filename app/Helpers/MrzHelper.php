<?php

namespace App\Helpers;

class MrzHelper
{
    /**
     * ICAO 9303 check digit: weights 7,3,1 repeating. Values: 0-9=0-9, A-Z=10-35, <=0.
     */
    public static function checkDigit(string $s): string
    {
        $weights = [7, 3, 1];
        $sum = 0;
        $chars = str_split($s);
        foreach ($chars as $i => $c) {
            $val = match ($c) {
                '<' => 0,
                default => ctype_digit($c) ? (int) $c : (ord($c) - 55),
            };
            $sum += $val * $weights[$i % 3];
        }

        return (string) ($sum % 10);
    }

    /**
     * TD1 MRZ satırlarını üretir (T.C. Kimlik Kartı formatı).
     *
     * @return array{0: string, 1: string, 2: string}
     */
    public static function td1Lines(
        string $documentNumber,
        ?string $tckn,
        string $dob,
        string $sex,
        string $expiry,
        string $nationality,
        string $surname,
        string $givenNames
    ): array {
        $docNo = strtoupper(preg_replace('/[^A-Z0-9<]/', '', $documentNumber));
        $docNo = str_pad(substr($docNo, 0, 9), 9, '<');
        $docCheck = self::checkDigit($docNo);

        $tcknDigits = preg_replace('/\D/', '', $tckn ?? '');
        $opt1 = $tcknDigits ? str_pad('<'.$tcknDigits, 15, '<', STR_PAD_RIGHT) : str_repeat('<', 15);

        $line1 = 'I<TUR'.$docNo.$docCheck.$opt1;

        $dobCheck = self::checkDigit($dob);
        $expCheck = self::checkDigit($expiry);
        $opt2 = str_repeat('<', 11);
        $line2WithoutCheck = $dob.$dobCheck.$sex.$expiry.$expCheck.$nationality.$opt2;

        $compositeStr = substr($line1, 5, 25).substr($line2WithoutCheck, 0, 7).substr($line2WithoutCheck, 8, 7).substr($line2WithoutCheck, 18, 11);
        $composite = self::checkDigit($compositeStr);

        $line2 = $line2WithoutCheck.$composite;

        $mrzSurname = strtoupper(preg_replace('/[^A-Za-z]/', '', \Illuminate\Support\Str::ascii($surname ?: 'XXX')));
        $givenClean = preg_replace('/[^A-Za-z\s]/', '', \Illuminate\Support\Str::ascii($givenNames ?: 'XXX'));
        $mrzGiven = strtoupper(str_replace(' ', '<', $givenClean));
        $name = $mrzSurname.'<<'.$mrzGiven;

        $line3 = str_pad(substr($name, 0, 30), 30, '<');

        return [$line1, $line2, $line3];
    }
}
