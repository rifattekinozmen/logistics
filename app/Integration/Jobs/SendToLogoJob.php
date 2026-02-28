<?php

namespace App\Integration\Jobs;

use App\Integration\Services\LogoIntegrationService;
use App\Models\Company;
use App\Models\Payment;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Logo ERP'ye fatura gönderme job'u.
 * Kritik: ödeme/fatura senkronu için tekrarlı deneme yapılır.
 */
class SendToLogoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $invoiceData,
        protected Company $company
    ) {
        $this->onQueue('critical');
    }

    /**
     * Execute the job.
     */
    public function handle(LogoIntegrationService $logoService): void
    {
        try {
            $payment = Payment::find($this->invoiceData['payment_id'] ?? null);

            if (! $payment) {
                throw new Exception('Payment not found for LOGO export');
            }

            $result = $logoService->exportInvoice($payment);

            Log::info("Logo'ya fatura gönderildi", [
                'company_id' => $this->company->id,
                'payment_id' => $payment->id,
            ]);
        } catch (Exception $e) {
            Log::error("Logo'ya fatura gönderim hatası: {$e->getMessage()}", [
                'company_id' => $this->company->id,
                'invoice_data' => $this->invoiceData,
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
