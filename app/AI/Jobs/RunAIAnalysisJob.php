<?php

namespace App\AI\Jobs;

use App\AI\Services\AIOperationsService;
use App\AI\Services\AIFinanceService;
use App\Models\AiReport;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * AI analiz job'u.
 * 
 * Günlük cronjob ile çalışır ve tüm AI servislerini tetikler.
 */
class RunAIAnalysisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected ?Company $company = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        AIOperationsService $operationsService,
        AIFinanceService $financeService
    ): void {
        try {
            $companies = $this->company 
                ? collect([$this->company])
                : Company::where('is_active', true)->get();

            foreach ($companies as $company) {
                // AI özellikleri aktif mi kontrol et
                $aiEnabled = $company->settings()
                    ->where('setting_key', 'ai_enabled')
                    ->value('setting_value');

                if ($aiEnabled !== 'true' && $aiEnabled !== true) {
                    continue;
                }

                // Operasyon analizi
                $operationsReports = $operationsService->analyze();
                foreach ($operationsReports as $report) {
                    AiReport::create([
                        'type' => $report['type'],
                        'summary_text' => $report['summary_text'],
                        'severity' => $report['severity'],
                        'data_snapshot' => $report['data_snapshot'],
                        'generated_at' => $report['generated_at'],
                    ]);
                }

                // Finans analizi
                $financeReports = $financeService->analyze();
                foreach ($financeReports as $report) {
                    AiReport::create([
                        'type' => $report['type'],
                        'summary_text' => $report['summary_text'],
                        'severity' => $report['severity'],
                        'data_snapshot' => $report['data_snapshot'],
                        'generated_at' => $report['generated_at'],
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("AI analiz job hatası: {$e->getMessage()}", [
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
