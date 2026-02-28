<?php

namespace App\EInvoice\Jobs;

use App\EInvoice\Models\EInvoice;
use App\EInvoice\Services\EArchiveService;
use App\EInvoice\Services\GibIntegrationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendEInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 300;

    public function __construct(
        public EInvoice $eInvoice
    ) {
        $this->onQueue('critical');
    }

    public function handle(): void
    {
        if (! config('einvoice.enabled')) {
            Log::info("E-Invoice sending disabled, skipping: {$this->eInvoice->invoice_uuid}");

            return;
        }

        if ($this->eInvoice->gib_status === EInvoice::GIB_STATUS_APPROVED) {
            Log::info("E-Invoice already approved, skipping: {$this->eInvoice->invoice_uuid}");

            return;
        }

        try {
            if ($this->eInvoice->invoice_type === EInvoice::TYPE_E_ARCHIVE) {
                $service = app(EArchiveService::class);
                $result = $service->sendToIntegrator($this->eInvoice);
            } else {
                $service = app(GibIntegrationService::class);
                $result = $service->sendToGib($this->eInvoice);
            }

            if ($result) {
                Log::info("E-Invoice sent successfully: {$this->eInvoice->invoice_uuid}");
            } else {
                Log::warning("E-Invoice sending returned false: {$this->eInvoice->invoice_uuid}");

                if ($this->attempts() < $this->tries) {
                    $this->release($this->backoff);
                }
            }
        } catch (Exception $e) {
            Log::error("E-Invoice sending failed: {$this->eInvoice->invoice_uuid}", [
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->eInvoice->update([
                    'gib_status' => EInvoice::GIB_STATUS_REJECTED,
                    'gib_response' => [
                        'error' => $e->getMessage(),
                        'attempts' => $this->attempts(),
                    ],
                ]);

                Log::error("E-Invoice max retries exceeded: {$this->eInvoice->invoice_uuid}");
            } else {
                $this->release($this->backoff);
            }

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("E-Invoice job failed permanently: {$this->eInvoice->invoice_uuid}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        $this->eInvoice->update([
            'gib_status' => EInvoice::GIB_STATUS_REJECTED,
            'gib_response' => [
                'error' => 'Job failed after all retry attempts',
                'exception' => $exception->getMessage(),
            ],
        ]);
    }
}
