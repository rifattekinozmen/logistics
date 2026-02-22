<?php

namespace App\AI\Jobs;

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
        AIFinanceService $financeService,
        AIHRService $hrService,
        AIFleetService $fleetService
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

                // Fleet analizi (Deployment optimization)
                $fleetOptimization = $fleetService->optimizeFleetDeployment($company->id);
                if (isset($fleetOptimization['recommendations'])) {
                    AiReport::create([
                        'type' => 'fleet_optimization',
                        'summary_text' => 'Filo kullanım oranı: '.$fleetOptimization['average_utilization'],
                        'severity' => 'low',
                        'data_snapshot' => $fleetOptimization,
                        'generated_at' => now(),
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error("AI analiz job hatası: {$e->getMessage()}", [
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
