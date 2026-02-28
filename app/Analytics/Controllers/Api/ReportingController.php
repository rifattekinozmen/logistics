<?php

namespace App\Analytics\Controllers\Api;

use App\Analytics\Services\AnalyticsDashboardService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Reporting API (Faz 3) – BI araçları için agregasyon verisi.
 * auth:sanctum ile korunur; aynı parametreler için 5 dakika cache (doküman önerisi).
 */
class ReportingController extends Controller
{
    private const CACHE_TTL_SECONDS = 300;

    public function __construct(
        private readonly AnalyticsDashboardService $analyticsService
    ) {}

    /**
     * GET /api/v1/reporting/finance-summary?company_id=1&from=2025-01-01&to=2025-01-31
     */
    public function financeSummary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $end = isset($validated['to']) ? Carbon::parse($validated['to']) : now();
        $start = isset($validated['from']) ? Carbon::parse($validated['from']) : now()->subDays(30);

        $cacheKey = sprintf(
            'reporting:finance:%s:%s:%s',
            $validated['company_id'],
            $start->toDateString(),
            $end->toDateString()
        );

        $metrics = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($company, $start, $end) {
            return $this->analyticsService->getFinancialMetrics($company, $start, $end);
        });

        return response()->json($metrics);
    }

    /**
     * GET /api/v1/reporting/fleet-utilization?company_id=1
     */
    public function fleetUtilization(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $cacheKey = 'reporting:fleet:'.$validated['company_id'];

        $data = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($company) {
            $performance = $this->analyticsService->getFleetPerformance($company);

            return [
                'total_vehicles' => $performance['total_vehicles'],
                'active_vehicles' => $performance['active_vehicles'],
                'idle_vehicles' => $performance['idle_vehicles'],
                'utilization_rate' => $performance['utilization_rate'],
                'vehicle_utilization' => $performance['vehicle_utilization'],
            ];
        });

        return response()->json($data);
    }

    /**
     * GET /api/v1/reporting/operations-kpi?company_id=1
     */
    public function operationsKpi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $cacheKey = 'reporting:operations:'.$validated['company_id'];

        $data = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($company) {
            $kpis = $this->analyticsService->getOperationalKpis($company);

            return [
                'total_orders' => $kpis['total_orders'],
                'completed_orders' => $kpis['completed_orders'],
                'completion_rate' => $kpis['completion_rate'],
                'on_time_delivery_rate' => $kpis['on_time_delivery_rate'],
                'status_breakdown' => $kpis['status_breakdown'],
            ];
        });

        return response()->json($data);
    }
}
