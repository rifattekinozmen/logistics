<?php

namespace App\Casts;

use App\Enums\AttendanceStatus;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AttendanceStatusCast implements CastsAttributes
{
    /**
     * Veritabanı değerini enum'a çevirir. Eski 'report' değeri SickLeave olarak okunur.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?AttendanceStatus
    {
        if ($value === null || $value === '') {
            return null;
        }

        return AttendanceStatus::fromDbValue((string) $value);
    }

    /**
     * Enum veya frontend anahtarını veritabanı değerine çevirir.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof AttendanceStatus) {
            return $value->toDbValue();
        }

        $status = AttendanceStatus::fromFrontendKey((string) $value)
            ?? AttendanceStatus::fromDbValue((string) $value);

        return $status?->toDbValue();
    }
}
