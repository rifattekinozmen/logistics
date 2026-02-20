<?php

namespace App\Integration\Services;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Payment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LogoIntegrationService
{
    /**
     * Export an invoice to LOGO ERP system.
     *
     * @param  Payment  $payment  Payment/Invoice record
     * @return bool Success status
     *
     * @throws Exception On export failure
     */
    public function exportInvoice(Payment $payment): bool
    {
        if (! config('logo.enabled')) {
            Log::info('LOGO integration disabled, skipping invoice export', ['payment_id' => $payment->id]);

            return false;
        }

        $payload = $this->buildInvoicePayload($payment);

        try {
            $response = $this->getHttpClient()
                ->post(config('logo.api_url').'/invoices', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                $logoInvoiceId = $responseData['invoice_id'] ?? null;

                Log::info('Invoice exported to LOGO', [
                    'payment_id' => $payment->id,
                    'logo_invoice_id' => $logoInvoiceId,
                ]);

                return true;
            }

            throw new Exception('LOGO API returned error: '.$response->status());
        } catch (Exception $e) {
            Log::error('LOGO invoice export failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync a customer to LOGO ERP system.
     *
     * @param  Customer  $customer  Customer record
     * @return bool Success status
     *
     * @throws Exception On sync failure
     */
    public function syncCustomer(Customer $customer): bool
    {
        if (! config('logo.enabled')) {
            Log::info('LOGO integration disabled, skipping customer sync', ['customer_id' => $customer->id]);

            return false;
        }

        $payload = $this->buildCustomerPayload($customer);

        try {
            $response = $this->getHttpClient()
                ->post(config('logo.api_url').'/customers', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                $logoCustomerId = $responseData['customer_id'] ?? null;

                Log::info('Customer synced to LOGO', [
                    'customer_id' => $customer->id,
                    'logo_customer_id' => $logoCustomerId,
                ]);

                return true;
            }

            throw new Exception('LOGO API returned error: '.$response->status());
        } catch (Exception $e) {
            Log::error('LOGO customer sync failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get accounting data from LOGO for a specific date.
     *
     * @param  Company  $company  Company record
     * @param  Carbon  $date  Date to fetch data for
     * @return array<string, mixed> Accounting data
     *
     * @throws Exception On fetch failure
     */
    public function getAccountingData(Company $company, Carbon $date): array
    {
        if (! config('logo.enabled')) {
            return [];
        }

        try {
            $response = $this->getHttpClient()
                ->get(config('logo.api_url').'/accounting', [
                    'company_code' => $company->code ?? $company->id,
                    'date' => $date->format('Y-m-d'),
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('LOGO API returned error: '.$response->status());
        } catch (Exception $e) {
            Log::error('LOGO accounting data fetch failed', [
                'company_id' => $company->id,
                'date' => $date->format('Y-m-d'),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Build invoice payload for LOGO API.
     *
     * @param  Payment  $payment  Payment record
     * @return array<string, mixed> LOGO API payload
     */
    protected function buildInvoicePayload(Payment $payment): array
    {
        $related = $payment->related;

        return [
            'invoice_number' => $payment->reference_number,
            'invoice_date' => $payment->due_date?->format('Y-m-d'),
            'customer_code' => $related instanceof Customer ? $related->id : null,
            'amount' => (float) $payment->amount,
            'currency' => 'TRY',
            'payment_method' => $payment->payment_method,
            'notes' => $payment->notes,
        ];
    }

    /**
     * Build customer payload for LOGO API.
     *
     * @param  Customer  $customer  Customer record
     * @return array<string, mixed> LOGO API payload
     */
    protected function buildCustomerPayload(Customer $customer): array
    {
        return [
            'customer_code' => $customer->id,
            'name' => $customer->name,
            'tax_number' => $customer->tax_number,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'address' => $customer->address,
            'status' => $customer->status === 1 ? 'active' : 'inactive',
        ];
    }

    /**
     * Get configured HTTP client for LOGO API.
     *
     * @return PendingRequest HTTP client instance
     */
    protected function getHttpClient(): PendingRequest
    {
        return Http::timeout(config('logo.timeout', 30))
            ->withToken(config('logo.api_token'))
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);
    }
}
