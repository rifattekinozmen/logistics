<?php

namespace App\EInvoice\Services;

use App\EInvoice\Models\EInvoice;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EArchiveService
{
    /**
     * Generate E-Archive invoice for retail customers.
     */
    public function generateEArchive(Payment $payment): EInvoice
    {
        if (! $this->checkThreshold($payment)) {
            throw new Exception('Payment amount below e-archive threshold');
        }

        $eInvoice = EInvoice::create([
            'related_type' => Payment::class,
            'related_id' => $payment->id,
            'company_id' => $payment->related->company_id ?? session('active_company_id'),
            'invoice_uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'invoice_type' => EInvoice::TYPE_E_ARCHIVE,
            'invoice_number' => $this->generateInvoiceNumber(),
            'invoice_date' => now(),
            'gib_status' => EInvoice::GIB_STATUS_PENDING,
            'amount' => $payment->amount,
            'currency' => 'TRY',
            'customer_name' => $payment->related->name ?? 'Retail Customer',
            'customer_tax_number' => $payment->related->tax_number ?? null,
            'customer_email' => $payment->related->email ?? null,
        ]);

        $xmlService = new EInvoiceXmlService;
        $xml = $xmlService->generateXml($eInvoice);

        $eInvoice->update(['xml_content' => $xml]);

        return $eInvoice;
    }

    /**
     * Send e-archive invoice to integrator.
     */
    public function sendToIntegrator(EInvoice $eInvoice): bool
    {
        if (! config('einvoice.e_archive.enabled')) {
            Log::info('E-Archive sending disabled');

            return false;
        }

        $integratorUrl = config('einvoice.gib_url').'/e-archive/send';

        try {
            $response = Http::timeout(config('einvoice.timeout'))
                ->withBasicAuth(
                    config('einvoice.gib_username'),
                    config('einvoice.gib_password')
                )
                ->post($integratorUrl, [
                    'invoice_uuid' => $eInvoice->invoice_uuid,
                    'xml_content' => $eInvoice->xml_content,
                    'customer_email' => $eInvoice->customer_email,
                ]);

            if ($response->successful()) {
                $eInvoice->update([
                    'gib_status' => EInvoice::GIB_STATUS_SENT,
                    'gib_response' => $response->json(),
                    'sent_at' => now(),
                ]);

                Log::info("E-Archive invoice sent successfully: {$eInvoice->invoice_uuid}");

                return true;
            }

            throw new Exception('Integrator returned error: '.$response->body());
        } catch (Exception $e) {
            $eInvoice->update([
                'gib_status' => EInvoice::GIB_STATUS_REJECTED,
                'gib_response' => ['error' => $e->getMessage()],
            ]);

            Log::error("E-Archive sending failed: {$eInvoice->invoice_uuid}", [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if payment amount meets e-archive threshold.
     */
    public function checkThreshold(Payment $payment): bool
    {
        $threshold = config('einvoice.e_archive.threshold_amount', 5000);

        return $payment->amount >= $threshold;
    }

    /**
     * Generate unique invoice number.
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'EARC';
        $year = now()->format('Y');
        $sequence = EInvoice::where('invoice_type', EInvoice::TYPE_E_ARCHIVE)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('%s%s%06d', $prefix, $year, $sequence);
    }
}
