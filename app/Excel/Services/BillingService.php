<?php

namespace App\Excel\Services;

use App\Models\Company;
use Carbon\Carbon;

class BillingService
{
    /**
     * Faturalandırma datası üret.
     * 
     * Analiz edilmiş verilerden faturalandırma için gerekli formatı oluşturur.
     */
    public function generateBillingData(array $analyzedData, Company $company): array
    {
        $billingItems = [];

        foreach ($analyzedData as $periodKey => $periodData) {
            $billingItems[] = [
                'company_id' => $company->id,
                'period_key' => $periodKey,
                'period_start' => $periodData['period_start'] ?? null,
                'period_end' => $periodData['period_end'] ?? null,
                'item_count' => $periodData['count'] ?? 0,
                'total_amount' => $periodData['total_amount'] ?? 0,
                'currency' => $company->currency ?? 'TRY',
                'vat_rate' => $company->default_vat_rate ?? 0,
                'subtotal' => $this->calculateSubtotal($periodData['total_amount'] ?? 0, $company->default_vat_rate ?? 0),
                'vat_amount' => $this->calculateVat($periodData['total_amount'] ?? 0, $company->default_vat_rate ?? 0),
                'total' => $periodData['total_amount'] ?? 0,
                'generated_at' => now()->toIso8601String(),
            ];
        }

        return $billingItems;
    }

    /**
     * KDV hariç tutarı hesapla.
     */
    protected function calculateSubtotal(float $total, float $vatRate): float
    {
        if ($vatRate == 0) {
            return $total;
        }

        return round($total / (1 + ($vatRate / 100)), 2);
    }

    /**
     * KDV tutarını hesapla.
     */
    protected function calculateVat(float $total, float $vatRate): float
    {
        if ($vatRate == 0) {
            return 0;
        }

        $subtotal = $this->calculateSubtotal($total, $vatRate);
        return round($total - $subtotal, 2);
    }

    /**
     * Fatura formatına dönüştür (ERP entegrasyonu için).
     */
    public function formatForERP(array $billingData): array
    {
        return array_map(function ($item) {
            return [
                'header' => [
                    'invoice_number' => $this->generateInvoiceNumber($item),
                    'invoice_date' => now()->format('Y-m-d'),
                    'customer_id' => $item['company_id'],
                    'currency' => $item['currency'],
                    'subtotal' => $item['subtotal'],
                    'vat_amount' => $item['vat_amount'],
                    'total' => $item['total'],
                ],
                'lines' => [
                    [
                        'description' => "Periyot: {$item['period_key']} - {$item['item_count']} adet işlem",
                        'quantity' => $item['item_count'],
                        'unit_price' => $item['item_count'] > 0 ? $item['subtotal'] / $item['item_count'] : 0,
                        'total' => $item['subtotal'],
                    ],
                ],
            ];
        }, $billingData);
    }

    /**
     * Fatura numarası oluştur.
     */
    protected function generateInvoiceNumber(array $item): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(md5($item['period_key']), 0, 4));
        
        return "{$prefix}-{$date}-{$random}";
    }
}
