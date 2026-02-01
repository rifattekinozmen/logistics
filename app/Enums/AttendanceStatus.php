<?php

namespace App\Enums;

/**
 * Puantaj / yoklama durumları.
 * Frontend (JS) anahtarları: full, half, izin, yillik, rapor, none
 */
enum AttendanceStatus: string
{
    case FullDay = 'full_day';
    case HalfDay = 'half_day';
    case Leave = 'leave';
    case AnnualLeave = 'annual_leave';
    case SickLeave = 'sick_leave';
    case Absent = 'absent';

    /**
     * Frontend (Alpine/JS) tarafında kullanılan anahtar.
     */
    public function frontendKey(): string
    {
        return match ($this) {
            self::FullDay => 'full',
            self::HalfDay => 'half',
            self::Leave => 'izin',
            self::AnnualLeave => 'yillik',
            self::SickLeave => 'rapor',
            self::Absent => 'none',
        };
    }

    /**
     * Türkçe etiket.
     */
    public function label(): string
    {
        return match ($this) {
            self::FullDay => 'Tam Gün',
            self::HalfDay => 'Yarım Gün',
            self::Leave => 'İzin',
            self::AnnualLeave => 'Yıllık İzin',
            self::SickLeave => 'Rapor',
            self::Absent => 'Devamsızlık',
        };
    }

    /**
     * Grid/context menu sembolü.
     */
    public function symbol(): string
    {
        return match ($this) {
            self::FullDay => '✓',
            self::HalfDay => '◐',
            self::Leave => '☕',
            self::AnnualLeave => '🏖',
            self::SickLeave => '🏥',
            self::Absent => '○',
        };
    }

    /**
     * İzin türü mü (yıllık, rapor, genel izin).
     */
    public function isLeaveType(): bool
    {
        return match ($this) {
            self::Leave, self::AnnualLeave, self::SickLeave => true,
            default => false,
        };
    }

    /**
     * Frontend anahtarından enum döndürür. Geçersiz anahtar için null.
     */
    public static function fromFrontendKey(string $key): ?self
    {
        return match ($key) {
            'full' => self::FullDay,
            'half' => self::HalfDay,
            'izin' => self::Leave,
            'yillik' => self::AnnualLeave,
            'rapor' => self::SickLeave,
            'none' => self::Absent,
            default => null,
        };
    }

    /**
     * Veritabanı değerinden enum. Eski 'report' değeri sick_leave olarak kabul edilir.
     */
    public static function fromDbValue(string $value): ?self
    {
        return match ($value) {
            'full_day' => self::FullDay,
            'half_day' => self::HalfDay,
            'leave' => self::Leave,
            'annual_leave' => self::AnnualLeave,
            'sick_leave', 'report' => self::SickLeave,
            'absent', 'none' => self::Absent,
            default => null,
        };
    }

    /**
     * Veritabanına yazılacak değer. Geriye uyumluluk için 'report' kullanılabilir.
     */
    public function toDbValue(): string
    {
        return $this->value;
    }

    /**
     * Tüm izin türleri (İzin, Yıllık İzin, Rapor).
     *
     * @return array<int, self>
     */
    public static function leaveTypes(): array
    {
        return [
            self::Leave,
            self::AnnualLeave,
            self::SickLeave,
        ];
    }

    /**
     * Tüm durumlar (frontend için).
     *
     * @return array<string, array{key: string, label: string, value: string}>
     */
    public static function forFrontend(): array
    {
        $items = [];
        foreach (self::cases() as $case) {
            $items[$case->frontendKey()] = [
                'key' => $case->frontendKey(),
                'label' => $case->label(),
                'value' => $case->toDbValue(),
                'symbol' => $case->symbol(),
            ];
        }

        return $items;
    }

    /**
     * Sol tık ile döngü sırası: bir sonraki durum.
     * full -> half -> izin -> yillik -> rapor -> none -> full
     *
     * @return array<string, string>
     */
    public static function cycleOrderMap(): array
    {
        $keys = array_map(fn (self $c) => $c->frontendKey(), self::cases());
        $next = [];
        foreach ($keys as $i => $key) {
            $next[$key] = $keys[($i + 1) % count($keys)];
        }

        return $next;
    }
}
