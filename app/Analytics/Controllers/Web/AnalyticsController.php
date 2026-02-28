<?php

namespace App\Analytics\Controllers\Web;

use App\Analytics\Services\AnalyticsDashboardService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Vehicle;
use App\Models\VehicleGpsPosition;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
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

    /**
     * Filo harita ekranı (Faz 3 – GPS konumları tablo/placeholder).
     */
    public function fleetMap(Request $request): View
    {
        $company = Company::findOrFail(session('active_company_id'));

        return view('admin.analytics.fleet-map', [
            'company' => $company,
        ]);
    }

    /**
     * Aktif şirketin araçları için son GPS konumları (JSON, filo harita için).
     *
     * @return JsonResponse
     */
    public function fleetMapPositions(Request $request): JsonResponse
    {
        $company = Company::findOrFail(session('active_company_id'));

        $vehicleIds = Vehicle::query()
            ->whereHas('branch', fn ($q) => $q->where('company_id', $company->id))
            ->pluck('id');

        if ($vehicleIds->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $positions = VehicleGpsPosition::query()
            ->whereIn('vehicle_id', $vehicleIds)
            ->with('vehicle:id,plate')
            ->orderByDesc('recorded_at')
            ->get()
            ->groupBy('vehicle_id')
            ->map(fn ($group) => $group->first())
            ->values()
            ->map(fn ($p) => [
                'vehicle_id' => $p->vehicle_id,
                'plate' => $p->vehicle?->plate,
                'latitude' => (float) $p->latitude,
                'longitude' => (float) $p->longitude,
                'recorded_at' => $p->recorded_at->toIso8601String(),
                'source' => $p->source,
            ]);

        return response()->json(['data' => $positions->values()->all()]);
    }
}
