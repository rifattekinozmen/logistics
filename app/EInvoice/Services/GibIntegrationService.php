<?php

namespace App\EInvoice\Services;

use App\EInvoice\Models\EInvoice;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GibIntegrationService
{
    /**
     * Send E-Invoice to GIB (Revenue Administration).
     *
     * @param  EInvoice  $eInvoice  E-Invoice model
     * @param  string  $xmlContent  UBL-TR XML content
     * @return bool Success status
     *
     * @throws Exception On send failure
     */
    public function sendToGib(EInvoice $eInvoice, string $xmlContent): bool
    {
        if (! config('einvoice.enabled')) {
            Log::info('E-Invoice integration disabled', ['invoice_id' => $eInvoice->id]);

            return false;
        }

        $url = config('einvoice.gib_url');
        $username = config('einvoice.gib_username');
        $password = config('einvoice.gib_password');

        try {
            $response = Http::timeout(config('einvoice.timeout', 30))
                ->withBasicAuth($username, $password)
                ->withHeaders([
                    'Content-Type' => 'application/xml',
                ])
                ->post($url, $xmlContent);

            if ($response->successful()) {
                $eInvoice->update([
                    'gib_status' => 'sent',
                    'gib_response' => $response->body(),
                    'sent_at' => now(),
                    'gib_error' => null,
                ]);

                Log::info('E-Invoice sent to GIB successfully', [
                    'invoice_id' => $eInvoice->id,
                    'invoice_uuid' => $eInvoice->invoice_uuid,
                ]);

                return true;
            }

            throw new Exception('GIB API returned error: '.$response->status());
        } catch (Exception $e) {
            $eInvoice->update([
                'gib_status' => 'error',
                'gib_error' => $e->getMessage(),
            ]);

            Log::error('E-Invoice send to GIB failed', [
                'invoice_id' => $eInvoice->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Check E-Invoice status from GIB.
     *
     * @param  EInvoice  $eInvoice  E-Invoice model
     * @return array<string, mixed> Status response
     *
     * @throws Exception On check failure
     */
    public function checkStatus(EInvoice $eInvoice): array
    {
        if (! config('einvoice.enabled')) {
            return ['status' => 'disabled'];
        }

        $url = config('einvoice.gib_url').'/status';
        $username = config('einvoice.gib_username');
        $password = config('einvoice.gib_password');

        try {
            $response = Http::timeout(config('einvoice.timeout', 30))
                ->withBasicAuth($username, $password)
                ->get($url, [
                    'invoice_uuid' => $eInvoice->invoice_uuid,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['status'] ?? 'unknown';

                if ($status === 'approved') {
                    $eInvoice->update([
                        'gib_status' => 'approved',
                        'approved_at' => now(),
                    ]);
                } elseif ($status === 'rejected') {
                    $eInvoice->update([
                        'gib_status' => 'rejected',
                        'gib_error' => $data['error'] ?? 'Rejected by GIB',
                    ]);
                }

                return $data;
            }

            throw new Exception('GIB status check failed: '.$response->status());
        } catch (Exception $e) {
            Log::error('E-Invoice status check failed', [
                'invoice_id' => $eInvoice->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Cancel E-Invoice in GIB.
     *
     * @param  EInvoice  $eInvoice  E-Invoice model
     * @param  string  $reason  Cancellation reason
     * @return bool Success status
     *
     * @throws Exception On cancel failure
     */
    public function cancelInvoice(EInvoice $eInvoice, string $reason): bool
    {
        if (! config('einvoice.enabled')) {
            return false;
        }

        if (! $eInvoice->isSent()) {
            throw new Exception('Invoice has not been sent to GIB yet');
        }

        $url = config('einvoice.gib_url').'/cancel';
        $username = config('einvoice.gib_username');
        $password = config('einvoice.gib_password');

        try {
            $response = Http::timeout(config('einvoice.timeout', 30))
                ->withBasicAuth($username, $password)
                ->post($url, [
                    'invoice_uuid' => $eInvoice->invoice_uuid,
                    'reason' => $reason,
                ]);

            if ($response->successful()) {
                Log::info('E-Invoice cancelled in GIB', [
                    'invoice_id' => $eInvoice->id,
                    'reason' => $reason,
                ]);

                return true;
            }

            throw new Exception('GIB cancel request failed: '.$response->status());
        } catch (Exception $e) {
            Log::error('E-Invoice cancellation failed', [
                'invoice_id' => $eInvoice->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
