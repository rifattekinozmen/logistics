<?php

namespace App\Integration\Jobs;

use App\Integration\Services\LogoIntegrationService;
use App\Models\Company;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Logo ERP'ye fatura gönderme job'u.
 */
class SendToLogoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $invoiceData,
        protected Company $company
    ) {}

    /**
     * Execute the job.
     */
    public function handle(LogoIntegrationService $logoService): void
    {
        try {
            $payment = \App\Models\Payment::find($this->invoiceData['payment_id'] ?? null);
            
            if (!$payment) {
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
