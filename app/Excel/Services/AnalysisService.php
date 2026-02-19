<?php

namespace App\Excel\Services;

class AnalysisService
{
    /**
     * Excel verilerini analiz et ve özet çıkar.
     */
    public function analyze(array $data, array $groupBy = ['period_key']): array
    {
        $grouped = [];

        foreach ($data as $row) {
            $key = $this->buildGroupKey($row, $groupBy);

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'count' => 0,
                    'total_amount' => 0,
                    'items' => [],
                ];
            }

            $grouped[$key]['count']++;
            $grouped[$key]['total_amount'] += $row['amount'] ?? 0;
            $grouped[$key]['items'][] = $row;
        }

        return $grouped;
    }

    /**
     * Grup key'i oluştur.
     */
    protected function buildGroupKey(array $row, array $groupBy): string
    {
        $parts = [];
        foreach ($groupBy as $field) {
            $parts[] = $row[$field] ?? 'unknown';
        }

        return implode('|', $parts);
    }

    /**
     * Toplam istatistikler.
     */
    public function getSummary(array $data): array
    {
        return [
            'total_rows' => count($data),
            'total_amount' => array_sum(array_column($data, 'amount')),
            'avg_amount' => count($data) > 0 ? array_sum(array_column($data, 'amount')) / count($data) : 0,
            'min_amount' => min(array_column($data, 'amount') ?: [0]),
            'max_amount' => max(array_column($data, 'amount') ?: [0]),
        ];
    }

    /**
     * Periyot bazlı analiz.
     */
    public function analyzeByPeriod(array $data): array
    {
        return $this->analyze($data, ['period_key']);
    }
}
