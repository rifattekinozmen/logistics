<?php

namespace App\Integration\Services;

use App\Models\Company;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LogoIntegrationService
{
    /**
     * Logo ERP'ye fatura gönder.
     */
    public function sendInvoice(array $invoiceData, Company $company): array
    {
        try {
            // Logo API endpoint (örnek)
            $endpoint = $company->settings()
                ->where('setting_key', 'logo_api_endpoint')
                ->value('setting_value') ?? config('services.logo.endpoint');

            $apiKey = $company->settings()
                ->where('setting_key', 'logo_api_key')
                ->value('setting_value') ?? config('services.logo.api_key');

            if (! $endpoint || ! $apiKey) {
                throw new Exception('Logo API yapılandırması eksik.');
            }

            // Logo formatına dönüştür
            $logoFormat = $this->convertToLogoFormat($invoiceData, $company);

            // API'ye gönder
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post($endpoint, $logoFormat);

            if (! $response->successful()) {
                throw new Exception("Logo API hatası: {$response->body()}");
            }

            return [
                'success' => true,
                'logo_invoice_id' => $response->json('invoice_id'),
                'response' => $response->json(),
            ];
        } catch (Exception $e) {
            Log::error("Logo entegrasyon hatası: {$e->getMessage()}", [
                'company_id' => $company->id,
                'invoice_data' => $invoiceData,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Veriyi Logo formatına dönüştür.
     */
    protected function convertToLogoFormat(array $invoiceData, Company $company): array
    {
        return [
            'header' => [
                'invoice_number' => $invoiceData['header']['invoice_number'] ?? null,
                'invoice_date' => $invoiceData['header']['invoice_date'] ?? now()->format('Y-m-d'),
                'customer_code' => $this->getCustomerCode($company),
                'currency' => $invoiceData['header']['currency'] ?? 'TRY',
                'subtotal' => $invoiceData['header']['subtotal'] ?? 0,
                'vat_amount' => $invoiceData['header']['vat_amount'] ?? 0,
                'total' => $invoiceData['header']['total'] ?? 0,
            ],
            'lines' => $invoiceData['lines'] ?? [],
        ];
    }

    /**
     * Müşteri kodunu al (Logo'da).
     */
    protected function getCustomerCode(Company $company): string
    {
        // Logo'da müşteri kodu mapping'i
        return $company->settings()
            ->where('setting_key', 'logo_customer_code')
            ->value('setting_value') ?? $company->tax_number ?? 'DEFAULT';
    }
}
