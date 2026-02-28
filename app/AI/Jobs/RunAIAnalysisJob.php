<?php

namespace App\AI\Jobs;

use App\AI\Services\AIDocumentService;
use App\AI\Services\AIFinanceService;
use App\AI\Services\AIFleetService;
use App\AI\Services\AIHRService;
use App\AI\Services\AIOperationsService;
use App\Models\AiReport;
use App\Models\Company;
use Exception;
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
 * Kritik değil: başarısız olursa ertesi gün tekrar çalışır.
 */
class RunAIAnalysisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected ?Company $company = null
    ) {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(
        AIOperationsService $operationsService,
        AIFinanceService $financeService,
        AIHRService $hrService,
        AIFleetService $fleetService,
        AIDocumentService $documentService
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

                // HR analizi (Turnover prediction)
                $turnoverPrediction = $hrService->predictTurnover($company);
                if ($turnoverPrediction['at_risk_count'] > 0) {
                    AiReport::create([
                        'type' => 'hr_turnover_risk',
                        'summary_text' => $turnoverPrediction['at_risk_count'].' çalışan işten ayrılma riski taşıyor',
                        'severity' => $turnoverPrediction['at_risk_count'] > 3 ? 'high' : 'medium',
                        'data_snapshot' => $turnoverPrediction,
                        'generated_at' => now(),
                    ]);
                }

                // Fleet analizi (anomali + optimize)
                $fleetReports = $fleetService->analyze($company->id);
                foreach ($fleetReports as $report) {
                    AiReport::create([
                        'type' => $report['type'],
                        'summary_text' => $report['summary_text'],
                        'severity' => $report['severity'],
                        'data_snapshot' => $report['data_snapshot'],
                        'generated_at' => $report['generated_at'],
                    ]);
                }
            }

            // Belge uygunluk analizi (global, şirket bağımsız)
            $documentReports = $documentService->analyze();
            foreach ($documentReports as $report) {
                AiReport::create([
                    'type' => $report['type'],
                    'summary_text' => $report['summary_text'],
                    'severity' => $report['severity'],
                    'data_snapshot' => $report['data_snapshot'],
                    'generated_at' => $report['generated_at'],
                ]);
            }
        } catch (Exception $e) {
            Log::error("AI analiz job hatası: {$e->getMessage()}", [
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
