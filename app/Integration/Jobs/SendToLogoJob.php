<?php

namespace App\Integration\Jobs;

use App\Integration\Services\LogoIntegrationService;
use App\Models\Company;
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
            $result = $logoService->sendInvoice($this->invoiceData, $this->company);

            Log::info("Logo'ya fatura gönderildi", [
                'company_id' => $this->company->id,
                'logo_invoice_id' => $result['logo_invoice_id'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error("Logo'ya fatura gönderim hatası: {$e->getMessage()}", [
                'company_id' => $this->company->id,
                'invoice_data' => $this->invoiceData,
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
