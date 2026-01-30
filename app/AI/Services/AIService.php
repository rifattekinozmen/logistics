<?php

namespace App\AI\Services;

/**
 * Base AI Service.
 * 
 * Tüm AI servisleri bu sınıftan türetilebilir.
 */
abstract class AIService
{
    /**
     * AI analizi çalıştır ve sonuçları döndür.
     */
    abstract public function analyze(): array;

    /**
     * Analiz sonuçlarını severity'ye göre filtrele.
     */
    protected function filterBySeverity(array $results, string $severity): array
    {
        return array_filter($results, fn($result) => $result['severity'] === $severity);
    }

    /**
     * AI raporu oluştur.
     */
    protected function createReport(string $type, string $summary, string $severity, array $data = []): array
    {
        return [
            'type' => $type,
            'summary_text' => $summary,
            'severity' => $severity,
            'data_snapshot' => $data,
            'generated_at' => now(),
        ];
    }
}
