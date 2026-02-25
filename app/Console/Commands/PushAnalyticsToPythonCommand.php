<?php

namespace App\Console\Commands;

use App\Analytics\Services\AnalyticsDashboardService;
use App\Integration\Services\PythonBridgeService;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PushAnalyticsToPythonCommand extends Command
{
    protected $signature = 'analytics:push-python {companyId? : Belirli bir şirket ID\'si (boş bırakılırsa tüm şirketler)} {--days=30 : Analiz edilecek gün sayısı}';

    protected $description = 'Şirket bazlı finans/operasyon/filo analitik özetlerini Python ara katmana gönderir (Faz 2 POC).';

    public function handle(AnalyticsDashboardService $analytics, PythonBridgeService $pythonBridge): int
    {
        $companyId = $this->argument('companyId');
        $days = (int) $this->option('days');
        if ($days <= 0) {
            $days = 30;
        }

        $query = Company::query()->where('is_active', 1);
        if ($companyId !== null) {
            $query->where('id', (int) $companyId);
        }

        $companies = $query->get();
        if ($companies->isEmpty()) {
            $this->warn('Aktif şirket bulunamadı.');

            return Command::SUCCESS;
        }

        $start = Carbon::now()->subDays($days);
        $end = Carbon::now();

        foreach ($companies as $company) {
            /** @var Company $company */
            $financial = $analytics->getFinancialMetrics($company, $start, $end);
            $operations = $analytics->getOperationalKpis($company);
            $fleet = $analytics->getFleetPerformance($company);

            $snapshot = [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'window_days' => $days,
                'period' => [
                    'start' => $start->toDateString(),
                    'end' => $end->toDateString(),
                ],
                'financial' => $financial,
                'operations' => $operations,
                'fleet' => $fleet,
            ];

            $pythonBridge->sendToPythonAsync([
                'source' => 'analytics_snapshot',
                'payload' => $snapshot,
            ], 'analytics');

            $this->info("Analytics snapshot Python\'a kuyruğa alındı: company_id={$company->id}, window_days={$days}");
        }

        return Command::SUCCESS;
    }
}

