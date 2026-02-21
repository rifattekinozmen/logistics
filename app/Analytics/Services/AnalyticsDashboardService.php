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

        // Monthly trend (driver-specific: SQL Server FORMAT, MySQL DATE_FORMAT, SQLite strftime)
        // SQL Server requires all SELECT columns to be in GROUP BY
        $driver = DB::connection()->getDriverName();
        $monthSelect = match ($driver) {
            'sqlsrv' => "FORMAT(created_at, 'yyyy-MM') as sort_key, FORMAT(created_at, 'MMM yyyy') as month, SUM(freight_price) as total",
            'sqlite' => "strftime('%b %Y', created_at) as month, SUM(freight_price) as total",
            default => 'DATE_FORMAT(created_at, "%b %Y") as month, SUM(freight_price) as total',
        };
        $monthGroup = match ($driver) {
            'sqlsrv' => "FORMAT(created_at, 'yyyy-MM'), FORMAT(created_at, 'MMM yyyy')",
            'sqlite' => "strftime('%Y-%m', created_at)",
            default => 'DATE_FORMAT(created_at, "%Y-%m")',
        };
        $monthOrder = match ($driver) {
            'sqlsrv' => 'sort_key',
            'sqlite' => "strftime('%Y-%m', created_at)",
            default => 'DATE_FORMAT(created_at, "%Y-%m")',
        };

        $monthlyRevenue = DB::table('orders')
            ->selectRaw($monthSelect)
            ->where('company_id', $company->id)
            ->where('status', 'invoiced')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw($monthGroup))
            ->orderBy($monthOrder)
            ->get();

        return [
            'revenue' => round($revenue, 2),
            'expenses' => round($expenses, 2),
            'net_profit' => round($netProfit, 2),
            'profit_margin' => round($profitMargin, 2),
            'monthly_trend' => $monthlyRevenue->map(fn ($item) => [
                'month' => $item->month,
                'total' => round($item->total, 2),
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

        // Shipments (scoped by company via orders)
        $onTimeDeliveries = DB::table('shipments')
            ->join('orders', 'shipments.order_id', '=', 'orders.id')
            ->where('orders.company_id', $company->id)
            ->where('shipments.status', 'delivered')
            ->whereRaw('shipments.delivery_date <= shipments.pickup_date')
            ->whereBetween('shipments.created_at', [$last30Days, now()])
            ->count();

        $totalDeliveries = DB::table('shipments')
            ->join('orders', 'shipments.order_id', '=', 'orders.id')
            ->where('orders.company_id', $company->id)
            ->where('shipments.status', 'delivered')
            ->whereBetween('shipments.created_at', [$last30Days, now()])
            ->count();

        $onTimeRate = $totalDeliveries > 0 ? ($onTimeDeliveries / $totalDeliveries) * 100 : 0;

        // Average processing time in hours (driver-specific: SQL Server DATEDIFF, MySQL TIMESTAMPDIFF, SQLite julianday)
        $driver = DB::connection()->getDriverName();
        $diffExpr = match ($driver) {
            'sqlsrv' => 'AVG(DATEDIFF(HOUR, created_at, delivered_at)) as avg_hours',
            'sqlite' => 'AVG((julianday(delivered_at) - julianday(created_at)) * 24) as avg_hours',
            default => 'AVG(TIMESTAMPDIFF(HOUR, created_at, delivered_at)) as avg_hours',
        };

        $avgProcessingTime = DB::table('orders')
            ->where('company_id', $company->id)
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$last30Days, now()])
            ->whereNotNull('delivered_at')
            ->selectRaw($diffExpr)
            ->value('avg_hours') ?? 0;

        // Order status distribution (Order model statuses)
        $statusLabels = [
            'pending' => ['label' => 'Beklemede', 'color' => 'warning', 'chartColor' => 'rgb(245, 158, 11)'],
            'planned' => ['label' => 'Planlandı', 'color' => 'info', 'chartColor' => 'rgb(55, 117, 168)'],
            'assigned' => ['label' => 'Atandı', 'color' => 'info', 'chartColor' => 'rgb(55, 117, 168)'],
            'loaded' => ['label' => 'Yüklendi', 'color' => 'primary', 'chartColor' => 'rgb(61, 105, 206)'],
            'in_transit' => ['label' => 'Yolda', 'color' => 'primary', 'chartColor' => 'rgb(61, 105, 206)'],
            'delivered' => ['label' => 'Teslim Edildi', 'color' => 'success', 'chartColor' => 'rgb(45, 139, 111)'],
            'invoiced' => ['label' => 'Faturalandı', 'color' => 'success', 'chartColor' => 'rgb(45, 139, 111)'],
            'cancelled' => ['label' => 'İptal', 'color' => 'danger', 'chartColor' => 'rgb(196, 30, 90)'],
        ];

        $statusDistribution = DB::table('orders')
            ->selectRaw('status, COUNT(*) as count')
            ->where('company_id', $company->id)
            ->whereBetween('created_at', [$last30Days, now()])
            ->groupBy('status')
            ->get();

        $statusBreakdown = $statusDistribution->map(function ($item) use ($statusLabels, $totalOrders) {
            $statusKey = strtolower((string) $item->status);
            $label = $statusLabels[$statusKey] ?? ['label' => ucfirst($item->status), 'color' => 'secondary', 'chartColor' => 'rgb(107, 114, 128)'];

            return [
                'status' => $item->status,
                'label' => $label['label'],
                'color' => $label['color'],
                'chartColor' => $label['chartColor'],
                'count' => $item->count,
                'percentage' => $totalOrders > 0 ? ($item->count / $totalOrders) * 100 : 0,
            ];
        })->toArray();

        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'completion_rate' => round($completionRate, 2),
            'on_time_deliveries' => $onTimeDeliveries,
            'total_deliveries' => $totalDeliveries,
            'on_time_delivery_rate' => round($onTimeRate, 2),
            'avg_processing_time' => round($avgProcessingTime, 1),
            'status_breakdown' => $statusBreakdown,
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

        // Active vehicles (with active shipments) — COUNT(DISTINCT vehicles.id)
        $activeVehicles = DB::table('vehicles')
            ->join('branches', 'vehicles.branch_id', '=', 'branches.id')
            ->join('shipments', 'vehicles.id', '=', 'shipments.vehicle_id')
            ->where('branches.company_id', $company->id)
            ->whereIn('shipments.status', ['assigned', 'loaded', 'in_transit'])
            ->distinct()
            ->count('vehicles.id');

        // Idle vehicles
        $idleVehicles = $totalVehicles - $activeVehicles;

        $utilizationRate = $totalVehicles > 0 ? ($activeVehicles / $totalVehicles) * 100 : 0;

        // Vehicles due for maintenance (simulated data for demo)
        $maintenanceDue = (int) ($totalVehicles * 0.2); // %20'si bakım bekliyor

        // Average fuel efficiency (random for demo)
        $avgFuelEfficiency = 28.5;

        // Vehicle utilization by vehicle
        $vehicleUtilization = DB::table('vehicles')
            ->selectRaw('vehicles.id, vehicles.plate as name, 
                COUNT(shipments.id) as shipment_count,
                COALESCE(COUNT(shipments.id) * 10, 0) as utilization')
            ->join('branches', 'vehicles.branch_id', '=', 'branches.id')
            ->leftJoin('shipments', function ($join) use ($last30Days) {
                $join->on('vehicles.id', '=', 'shipments.vehicle_id')
                    ->where('shipments.created_at', '>=', $last30Days);
            })
            ->where('branches.company_id', $company->id)
            ->where('vehicles.status', 1)
            ->groupBy('vehicles.id', 'vehicles.plate')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'utilization' => min($item->utilization, 100),
            ])
            ->toArray();

        // Maintenance alerts (simulated for demo)
        $maintenanceAlerts = DB::table('vehicles')
            ->select('vehicles.plate', 'vehicles.brand', 'vehicles.model')
            ->join('branches', 'vehicles.branch_id', '=', 'branches.id')
            ->where('branches.company_id', $company->id)
            ->where('vehicles.status', 1)
            ->limit(min($maintenanceDue, 10))
            ->get()
            ->map(function ($vehicle, $index) {
                $urgencyColors = ['warning', 'danger', 'info'];
                $urgencyLabels = ['Yakında', 'Acil', 'Planla'];
                $randomIndex = $index % 3;

                return [
                    'name' => $vehicle->brand.' '.$vehicle->model,
                    'plate_number' => $vehicle->plate,
                    'last_maintenance' => Carbon::now()->subDays(rand(30, 180))->format('d.m.Y'),
                    'current_km' => rand(50000, 200000),
                    'urgency_color' => $urgencyColors[$randomIndex],
                    'urgency_label' => $urgencyLabels[$randomIndex],
                ];
            })
            ->toArray();

        return [
            'total_vehicles' => $totalVehicles,
            'active_vehicles' => $activeVehicles,
            'idle_vehicles' => $idleVehicles,
            'maintenance_due' => $maintenanceDue,
            'utilization_rate' => round($utilizationRate, 2),
            'avg_fuel_efficiency' => $avgFuelEfficiency,
            'vehicle_utilization' => $vehicleUtilization,
            'maintenance_alerts' => $maintenanceAlerts,
        ];
    }
}
