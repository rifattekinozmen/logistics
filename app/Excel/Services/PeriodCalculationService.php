<?php

namespace App\Excel\Services;

use Carbon\Carbon;

class PeriodCalculationService
{
    /**
     * Haftalık periyot tespiti.
     * 
     * Verilen tarihten haftanın başlangıç ve bitiş tarihlerini döndürür.
     */
    public function getWeeklyPeriod(Carbon $date): array
    {
        $startOfWeek = $date->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $date->copy()->endOfWeek(Carbon::SUNDAY);

        return [
            'start_date' => $startOfWeek->format('Y-m-d'),
            'end_date' => $endOfWeek->format('Y-m-d'),
            'week_number' => $startOfWeek->week,
            'year' => $startOfWeek->year,
            'period_key' => $startOfWeek->format('Y-W'), // 2026-05 formatında
        ];
    }

    /**
     * Aylık periyot tespiti.
     */
    public function getMonthlyPeriod(Carbon $date): array
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        return [
            'start_date' => $startOfMonth->format('Y-m-d'),
            'end_date' => $endOfMonth->format('Y-m-d'),
            'month' => $startOfMonth->month,
            'year' => $startOfMonth->year,
            'period_key' => $startOfMonth->format('Y-m'), // 2026-01 formatında
        ];
    }

    /**
     * Tarih aralığından periyotları hesapla.
     */
    public function calculatePeriodsFromRange(Carbon $startDate, Carbon $endDate, string $periodType = 'weekly'): array
    {
        $periods = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $period = $periodType === 'weekly' 
                ? $this->getWeeklyPeriod($current)
                : $this->getMonthlyPeriod($current);

            $periods[] = $period;

            // Sonraki periyoda geç
            $current = $periodType === 'weekly'
                ? $current->copy()->addWeek()
                : $current->copy()->addMonth();
        }

        return $periods;
    }

    /**
     * Tarih verisinden otomatik periyot hesapla.
     */
    public function autoDetectPeriod(array $row, string $dateColumn = 'date'): ?array
    {
        if (!isset($row[$dateColumn])) {
            return null;
        }

        try {
            $date = Carbon::parse($row[$dateColumn]);
            return $this->getWeeklyPeriod($date);
        } catch (\Exception $e) {
            return null;
        }
    }
}
