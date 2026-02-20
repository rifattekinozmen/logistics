<?php

namespace App\Analytics\Controllers\Web;

use App\Analytics\Services\AnalyticsDashboardService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsDashboardService $analyticsService
    ) {}

    public function finance(Request $request): View
    {
        $company = Company::findOrFail(session('active_company_id'));

        $period = $request->get('period', '30');
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays((int) $period);

        $metrics = $this->analyticsService->getFinancialMetrics($company, $startDate, $endDate);

        return view('admin.analytics.finance', [
            'metrics' => $metrics,
            'period' => $period,
            'company' => $company,
        ]);
    }

    public function operations(Request $request): View
    {
        $company = Company::findOrFail(session('active_company_id'));

        $kpis = $this->analyticsService->getOperationalKpis($company);

        return view('admin.analytics.operations', [
            'kpis' => $kpis,
            'company' => $company,
        ]);
    }

    public function fleet(Request $request): View
    {
        $company = Company::findOrFail(session('active_company_id'));

        $performance = $this->analyticsService->getFleetPerformance($company);

        return view('admin.analytics.fleet', [
            'performance' => $performance,
            'company' => $company,
        ]);
    }
}
