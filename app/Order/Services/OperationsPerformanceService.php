<?php

namespace App\Order\Services;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\Vehicle;
use Carbon\Carbon;

class OperationsPerformanceService
{
    /**
     * Operasyon performans özeti.
     */
    public function getPerformanceSummary(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Teslimat performans puanı
        $deliveryScore = $this->calculateDeliveryPerformanceScore();

        // Geciken sipariş oranı
        $delayedOrderRate = $this->calculateDelayedOrderRate();

        // Araç doluluk oranı
        $vehicleUtilization = $this->calculateVehicleUtilization();

        // Ortalama teslimat süresi
        $avgDeliveryTime = $this->calculateAverageDeliveryTime();

        return [
            'delivery_performance_score' => $deliveryScore,
            'delayed_order_rate' => $delayedOrderRate,
            'vehicle_utilization' => $vehicleUtilization,
            'average_delivery_time' => $avgDeliveryTime,
            'summary' => [
                'total_orders' => Order::where('status', '!=', 'cancelled')->count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'in_transit_orders' => Order::where('status', 'in_transit')->count(),
                'delivered_orders' => Order::where('status', 'delivered')->count(),
                'active_shipments' => Shipment::whereIn('status', ['assigned', 'loaded', 'in_transit'])->count(),
            ],
        ];
    }

    /**
     * Teslimat performans puanı hesapla (0-100).
     */
    protected function calculateDeliveryPerformanceScore(): float
    {
        $total = Order::where('status', 'delivered')
            ->whereNotNull('planned_delivery_date')
            ->whereNotNull('delivered_at')
            ->count();

        if ($total === 0) {
            return 100.0;
        }

        $onTimeCount = Order::where('status', 'delivered')
            ->whereNotNull('planned_delivery_date')
            ->whereNotNull('delivered_at')
            ->whereColumn('delivered_at', '<=', 'planned_delivery_date')
            ->count();

        return round(($onTimeCount / $total) * 100, 2);
    }

    /**
     * Geciken sipariş oranı hesapla.
     */
    protected function calculateDelayedOrderRate(): array
    {
        $total = Order::where('status', '!=', 'cancelled')
            ->where('status', '!=', 'delivered')
            ->whereNotNull('planned_delivery_date')
            ->count();

        $delayed = Order::where('status', '!=', 'cancelled')
            ->where('status', '!=', 'delivered')
            ->where('planned_delivery_date', '<', now())
            ->count();

        $rate = $total > 0 ? round(($delayed / $total) * 100, 2) : 0;

        return [
            'count' => $delayed,
            'total' => $total,
            'rate' => $rate,
        ];
    }

    /**
     * Araç doluluk oranı hesapla.
     */
    protected function calculateVehicleUtilization(): array
    {
        $totalVehicles = Vehicle::where('status', 1)->count();

        if ($totalVehicles === 0) {
            return [
                'utilization_rate' => 0,
                'active_vehicles' => 0,
                'total_vehicles' => 0,
            ];
        }

        $activeVehicles = Shipment::whereIn('status', ['assigned', 'loaded', 'in_transit'])
            ->whereNotNull('vehicle_id')
            ->distinct('vehicle_id')
            ->count('vehicle_id');

        $rate = round(($activeVehicles / $totalVehicles) * 100, 2);

        return [
            'utilization_rate' => $rate,
            'active_vehicles' => $activeVehicles,
            'total_vehicles' => $totalVehicles,
            'available_vehicles' => $totalVehicles - $activeVehicles,
        ];
    }

    /**
     * Ortalama teslimat süresi hesapla (saat cinsinden).
     */
    protected function calculateAverageDeliveryTime(): ?float
    {
        $driver = Order::query()->getConnection()->getDriverName();
        $baseQuery = Order::where('status', 'delivered')
            ->whereNotNull('actual_pickup_date')
            ->whereNotNull('delivered_at');

        if ($driver === 'sqlsrv') {
            $result = (clone $baseQuery)->selectRaw('COUNT(*) as cnt, SUM(DATEDIFF(SECOND, actual_pickup_date, delivered_at) / 3600.0) as total_hours')->first();
        } elseif ($driver === 'sqlite') {
            $result = (clone $baseQuery)->selectRaw('COUNT(*) as cnt, SUM((julianday(delivered_at) - julianday(actual_pickup_date)) * 24) as total_hours')->first();
        } else {
            $result = (clone $baseQuery)->selectRaw('COUNT(*) as cnt, SUM(TIMESTAMPDIFF(SECOND, actual_pickup_date, delivered_at) / 3600.0) as total_hours')->first();
        }

        $cnt = (int) ($result->cnt ?? 0);
        if ($cnt === 0) {
            return null;
        }

        $totalHours = (float) ($result->total_hours ?? 0);

        return round($totalHours / $cnt, 2);
    }
}
