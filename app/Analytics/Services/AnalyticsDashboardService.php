<?php

namespace App\Analytics\Services;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsDashboardService
{
    /**
     * Get financial metrics for a company within a date range.
     *
     * @param  Company  $company  Company to analyze
     * @param  Carbon  $start  Start date
     * @param  Carbon  $end  End date
     * @return array<string, mixed> Financial metrics
     */
    public function getFinancialMetrics(Company $company, Carbon $start, Carbon $end): array
    {
        // Revenue from completed orders
        $revenue = DB::table('orders')
            ->where('company_id', $company->id)
            ->where('status', 'invoiced')
            ->whereBetween('created_at', [$start, $end])
            ->sum('freight_price') ?? 0;

        // Expenses (payments marked as outgoing)
        $expenses = DB::table('payments')
            ->where('payment_type', 'outgoing')
            ->where('status', 1)
            ->whereBetween('paid_date', [$start, $end])
            ->sum('amount') ?? 0;

        $netProfit = $revenue - $expenses;
        $profitMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        // Monthly trend
        $monthlyRevenue = DB::table('orders')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(freight_price) as total')
            ->where('company_id', $company->id)
            ->where('status', 'invoiced')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'period' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
            'summary' => [
                'total_revenue' => round($revenue, 2),
                'total_expenses' => round($expenses, 2),
                'net_profit' => round($netProfit, 2),
                'profit_margin' => round($profitMargin, 2).'%',
            ],
            'monthly_trend' => $monthlyRevenue->map(fn ($item) => [
                'month' => $item->month,
                'revenue' => round($item->total, 2),
            ])->toArray(),
        ];
    }

    /**
     * Get operational KPIs for a company.
     *
     * @param  Company  $company  Company to analyze
     * @return array<string, mixed> Operational KPIs
     */
    public function getOperationalKpis(Company $company): array
    {
        $last30Days = now()->subDays(30);

        // Orders
        $totalOrders = DB::table('orders')
            ->where('company_id', $company->id)
            ->whereBetween('created_at', [$last30Days, now()])
            ->count();

        $completedOrders = DB::table('orders')
            ->where('company_id', $company->id)
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$last30Days, now()])
            ->count();

        $completionRate = $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0;

        // Shipments
        $activeShipments = DB::table('shipments')
            ->whereIn('status', ['assigned', 'loaded', 'in_transit'])
            ->count();

        $onTimeDeliveries = DB::table('shipments')
            ->where('status', 'delivered')
            ->whereRaw('delivery_date <= pickup_date')
            ->whereBetween('created_at', [$last30Days, now()])
            ->count();

        $totalDelivered = DB::table('shipments')
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$last30Days, now()])
            ->count();

        $onTimeRate = $totalDelivered > 0 ? ($onTimeDeliveries / $totalDelivered) * 100 : 0;

        // Order status distribution
        $statusDistribution = DB::table('orders')
            ->selectRaw('status, COUNT(*) as count')
            ->where('company_id', $company->id)
            ->whereBetween('created_at', [$last30Days, now()])
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->status => $item->count])
            ->toArray();

        return [
            'period' => 'Last 30 days',
            'orders' => [
                'total' => $totalOrders,
                'completed' => $completedOrders,
                'completion_rate' => round($completionRate, 2).'%',
                'status_distribution' => $statusDistribution,
            ],
            'shipments' => [
                'active' => $activeShipments,
                'on_time_deliveries' => $onTimeDeliveries,
                'on_time_rate' => round($onTimeRate, 2).'%',
            ],
        ];
    }

    /**
     * Get fleet performance metrics for a company.
     *
     * @param  Company  $company  Company to analyze
     * @return array<string, mixed> Fleet performance
     */
    public function getFleetPerformance(Company $company): array
    {
        $last30Days = now()->subDays(30);

        // Total vehicles
        $totalVehicles = DB::table('vehicles')
            ->join('branches', 'vehicles.branch_id', '=', 'branches.id')
            ->where('branches.company_id', $company->id)
            ->where('vehicles.status', 1)
            ->count();

        // Active vehicles (with shipments)
        $activeVehicles = DB::table('vehicles')
            ->join('branches', 'vehicles.branch_id', '=', 'branches.id')
            ->join('shipments', 'vehicles.id', '=', 'shipments.vehicle_id')
            ->where('branches.company_id', $company->id)
            ->whereIn('shipments.status', ['assigned', 'loaded', 'in_transit'])
            ->distinct('vehicles.id')
            ->count();

        $utilizationRate = $totalVehicles > 0 ? ($activeVehicles / $totalVehicles) * 100 : 0;

        // Vehicle performance by type
        $performanceByType = DB::table('vehicles')
            ->selectRaw('vehicle_type, COUNT(*) as count')
            ->join('branches', 'vehicles.branch_id', '=', 'branches.id')
            ->where('branches.company_id', $company->id)
            ->where('vehicles.status', 1)
            ->groupBy('vehicle_type')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->vehicle_type => $item->count])
            ->toArray();

        // Shipments per vehicle
        $shipmentsPerVehicle = DB::table('shipments')
            ->join('vehicles', 'shipments.vehicle_id', '=', 'vehicles.id')
            ->join('branches', 'vehicles.branch_id', '=', 'branches.id')
            ->where('branches.company_id', $company->id)
            ->whereBetween('shipments.created_at', [$last30Days, now()])
            ->count();

        $avgShipmentsPerVehicle = $totalVehicles > 0 ? $shipmentsPerVehicle / $totalVehicles : 0;

        return [
            'period' => 'Last 30 days',
            'fleet_size' => $totalVehicles,
            'active_vehicles' => $activeVehicles,
            'utilization_rate' => round($utilizationRate, 2).'%',
            'avg_shipments_per_vehicle' => round($avgShipmentsPerVehicle, 2),
            'vehicle_types' => $performanceByType,
        ];
    }
}
