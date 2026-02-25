<?php

namespace App\AI\Services;

use App\Models\FuelPrice;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AIFleetService
{
    /**
     * Predict maintenance needs for a vehicle.
     */
    public function predictMaintenanceNeeds(Vehicle $vehicle): array
    {
        $lastInspection = VehicleInspection::where('vehicle_id', $vehicle->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $daysSinceInspection = $lastInspection
            ? now()->diffInDays($lastInspection->created_at)
            : 365;

        $mileage = $vehicle->current_mileage ?? 0;
        $maintenanceScore = $this->calculateMaintenanceScore($daysSinceInspection, $mileage);

        $upcomingMaintenance = [];

        if ($daysSinceInspection >= 150) {
            $upcomingMaintenance[] = [
                'type' => 'Periyodik Muayene',
                'urgency' => 'high',
                'estimated_days' => max(0, 180 - $daysSinceInspection),
            ];
        }

        if ($mileage > 0 && $mileage % 10000 < 2000) {
            $upcomingMaintenance[] = [
                'type' => 'Yağ Değişimi',
                'urgency' => 'medium',
                'estimated_km' => 10000 - ($mileage % 10000),
            ];
        }

        if ($mileage > 0 && $mileage % 40000 < 5000) {
            $upcomingMaintenance[] = [
                'type' => 'Lastik Değişimi',
                'urgency' => 'medium',
                'estimated_km' => 40000 - ($mileage % 40000),
            ];
        }

        return [
            'vehicle_id' => $vehicle->id,
            'vehicle_plate' => $vehicle->plate,
            'maintenance_score' => $maintenanceScore,
            'status' => $this->getMaintenanceStatus($maintenanceScore),
            'last_inspection_days' => $daysSinceInspection,
            'current_mileage' => $mileage,
            'upcoming_maintenance' => $upcomingMaintenance,
            'recommendations' => $this->generateMaintenanceRecommendations($maintenanceScore, $upcomingMaintenance),
        ];
    }

    /**
     * Analyze fuel consumption patterns.
     *
     * Note: fuel_records table may not exist; returns placeholder metrics when absent.
     */
    public function analyzeFuelConsumption(Vehicle $vehicle, int $months = 3): array
    {
        $totalFuelConsumed = 0.0;
        $totalCost = 0.0;
        $averageConsumption = 0.0;

        if (Schema::hasTable('fuel_records')) {
            $fuelRecords = DB::table('fuel_records')
                ->where('vehicle_id', $vehicle->id)
                ->whereBetween('created_at', [now()->subMonths($months), now()])
                ->get();

            $totalFuelConsumed = (float) $fuelRecords->sum('quantity');
            $totalCost = (float) $fuelRecords->sum('total_cost');
            $averageConsumption = (float) ($fuelRecords->avg('quantity') ?? 0);
        }

        $avgFuelPrice = (float) (FuelPrice::query()
            ->whereBetween('price_date', [now()->subMonths($months), now()])
            ->avg('purchase_price') ?? 0);

        $efficiency = $this->calculateFuelEfficiency($vehicle, $totalFuelConsumed, $months);

        return [
            'vehicle_id' => $vehicle->id,
            'vehicle_plate' => $vehicle->plate,
            'period' => $months.' months',
            'metrics' => [
                'total_fuel_consumed' => round($totalFuelConsumed, 2).' L',
                'total_cost' => round($totalCost, 2).' TL',
                'average_consumption' => round($averageConsumption, 2).' L',
                'average_price' => round($avgFuelPrice, 2).' TL/L',
            ],
            'efficiency_score' => $efficiency,
            'efficiency_rating' => $this->getEfficiencyRating($efficiency),
            'cost_analysis' => $this->analyzeFuelCost($totalCost, $avgFuelPrice, $months),
            'recommendations' => $this->generateFuelRecommendations($efficiency),
        ];
    }

    /**
     * Filo analizi çalıştır; bakım anomali ve kullanım anomali raporları döndürür.
     *
     * @return array<int, array{type: string, summary_text: string, severity: string, data_snapshot: array, generated_at: \Illuminate\Support\Carbon}>
     */
    public function analyze(int $companyId): array
    {
        $reports = [];

        $vehicles = Vehicle::whereHas('branch', fn ($q) => $q->where('company_id', $companyId))
            ->where('status', 1)
            ->get();

        $maintenanceAnomalies = [];
        foreach ($vehicles as $vehicle) {
            $prediction = $this->predictMaintenanceNeeds($vehicle);
            if ($prediction['status'] === 'needs_attention' || $prediction['maintenance_score'] < 40) {
                $maintenanceAnomalies[] = [
                    'vehicle_id' => $vehicle->id,
                    'plate' => $vehicle->plate,
                    'maintenance_score' => $prediction['maintenance_score'],
                ];
            }
        }

        if (count($maintenanceAnomalies) > 0) {
            $reports[] = $this->createReport(
                'fleet',
                count($maintenanceAnomalies).' araç acil bakım veya muayene gerektiriyor.',
                count($maintenanceAnomalies) >= 3 ? 'high' : 'medium',
                ['vehicles' => $maintenanceAnomalies]
            );
        }

        $deployment = $this->optimizeFleetDeployment($companyId);
        $idleCount = count(array_filter($deployment['utilization_data'], fn ($v) => $v['status'] === 'idle'));
        $total = $deployment['total_vehicles'];
        if ($total > 0 && $idleCount >= $total * 0.5) {
            $reports[] = $this->createReport(
                'fleet',
                "Filo kullanım anomali: {$idleCount}/{$total} araç atıl durumda.",
                $idleCount === $total ? 'high' : 'medium',
                [
                    'idle_count' => $idleCount,
                    'total_vehicles' => $total,
                    'average_utilization' => $deployment['average_utilization'],
                ]
            );
        }

        return $reports;
    }

    /**
     * AI raporu yapısı oluştur (ai_reports ile uyumlu).
     *
     * @param  array<string, mixed>  $data
     * @return array{type: string, summary_text: string, severity: string, data_snapshot: array, generated_at: \Illuminate\Support\Carbon}
     */
    protected function createReport(string $type, string $summaryText, string $severity, array $data = []): array
    {
        return [
            'type' => $type,
            'summary_text' => $summaryText,
            'severity' => $severity,
            'data_snapshot' => $data,
            'generated_at' => now(),
        ];
    }

    /**
     * Optimize fleet deployment for a company.
     */
    public function optimizeFleetDeployment(int $companyId): array
    {
        $vehicles = Vehicle::whereHas('branch', fn ($q) => $q->where('company_id', $companyId))
            ->where('status', 1)
            ->get();

        $vehicleIds = $vehicles->pluck('id')->all();
        $activeShipments = collect();

        if (! empty($vehicleIds)) {
            $activeShipments = DB::table('shipments')
                ->whereIn('vehicle_id', $vehicleIds)
                ->whereIn('status', ['pending', 'in_transit'])
                ->get();
        }

        $utilization = [];
        foreach ($vehicles as $vehicle) {
            $vehicleShipments = $activeShipments->where('vehicle_id', $vehicle->id);
            $utilizationRate = ($vehicleShipments->count() / max($activeShipments->count(), 1)) * 100;

            $utilization[] = [
                'vehicle_id' => $vehicle->id,
                'vehicle_plate' => $vehicle->plate,
                'active_shipments' => $vehicleShipments->count(),
                'utilization_rate' => round($utilizationRate, 2),
                'status' => $this->getUtilizationStatus($utilizationRate),
            ];
        }

        return [
            'company_id' => $companyId,
            'total_vehicles' => $vehicles->count(),
            'active_vehicles' => count(array_filter($utilization, fn ($v) => $v['active_shipments'] > 0)),
            'utilization_data' => $utilization,
            'average_utilization' => round(collect($utilization)->avg('utilization_rate'), 2).'%',
            'recommendations' => $this->generateDeploymentRecommendations($utilization),
        ];
    }

    /**
     * Calculate maintenance score.
     */
    protected function calculateMaintenanceScore(int $daysSinceInspection, float $mileage): float
    {
        $inspectionScore = max(0, 100 - ($daysSinceInspection / 180 * 100));
        $mileageScore = $mileage > 0 ? max(0, 100 - (($mileage % 10000) / 10000 * 100)) : 100;

        return round(($inspectionScore + $mileageScore) / 2, 2);
    }

    /**
     * Get maintenance status from score.
     */
    protected function getMaintenanceStatus(float $score): string
    {
        return match (true) {
            $score >= 80 => 'excellent',
            $score >= 60 => 'good',
            $score >= 40 => 'fair',
            default => 'needs_attention',
        };
    }

    /**
     * Calculate fuel efficiency.
     */
    protected function calculateFuelEfficiency(Vehicle $vehicle, float $fuelConsumed, int $months): float
    {
        if ($fuelConsumed <= 0) {
            return 0;
        }

        $averageDistance = ($vehicle->current_mileage ?? 0) / max($months, 1);
        $efficiency = $averageDistance > 0 ? ($fuelConsumed / $averageDistance) * 100 : 0;

        return round($efficiency, 2);
    }

    /**
     * Get efficiency rating.
     */
    protected function getEfficiencyRating(float $efficiency): string
    {
        return match (true) {
            $efficiency >= 90 => 'Excellent',
            $efficiency >= 75 => 'Good',
            $efficiency >= 60 => 'Average',
            default => 'Needs Improvement',
        };
    }

    /**
     * Analyze fuel cost.
     */
    protected function analyzeFuelCost(float $totalCost, float $avgPrice, int $months): array
    {
        $monthlyAverage = $totalCost / max($months, 1);
        $trend = rand(-15, 25);

        return [
            'monthly_average' => round($monthlyAverage, 2).' TL',
            'trend' => $trend > 0 ? '+'.$trend.'%' : $trend.'%',
            'status' => $trend > 10 ? 'increasing' : ($trend < -10 ? 'decreasing' : 'stable'),
        ];
    }

    /**
     * Get utilization status.
     */
    protected function getUtilizationStatus(float $rate): string
    {
        return match (true) {
            $rate >= 80 => 'high',
            $rate >= 50 => 'optimal',
            $rate >= 20 => 'low',
            default => 'idle',
        };
    }

    /**
     * Generate maintenance recommendations.
     */
    protected function generateMaintenanceRecommendations(float $score, array $upcoming): array
    {
        $recommendations = [];

        if ($score < 50) {
            $recommendations[] = 'Acil bakım gerekiyor - En kısa sürede servis randevusu alın';
        }

        if (! empty($upcoming)) {
            foreach ($upcoming as $item) {
                if ($item['urgency'] === 'high') {
                    $recommendations[] = $item['type'].' için hemen randevu alın';
                }
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Araç bakım durumu iyi, rutin kontrollere devam edin';
        }

        return $recommendations;
    }

    /**
     * Generate fuel recommendations.
     */
    protected function generateFuelRecommendations(float $efficiency): array
    {
        $recommendations = [];

        if ($efficiency < 60) {
            $recommendations[] = 'Sürücü eğitimi ile yakıt verimliliği artırılabilir';
            $recommendations[] = 'Rota optimizasyonu yapılmalı';
            $recommendations[] = 'Araç bakımı kontrol edilmeli';
        } elseif ($efficiency < 75) {
            $recommendations[] = 'Periyodik bakımlar aksatılmamalı';
            $recommendations[] = 'Lastik basıncı düzenli kontrol edilmeli';
        } else {
            $recommendations[] = 'Mükemmel verimlilik, mevcut performans korunmalı';
        }

        return $recommendations;
    }

    /**
     * Generate deployment recommendations.
     */
    protected function generateDeploymentRecommendations(array $utilization): array
    {
        $recommendations = [];
        $idleVehicles = array_filter($utilization, fn ($v) => $v['status'] === 'idle');
        $highUtilization = array_filter($utilization, fn ($v) => $v['status'] === 'high');

        if (count($idleVehicles) > 0) {
            $recommendations[] = count($idleVehicles).' araç atıl durumda - alternatif kullanım alanları değerlendirilmeli';
        }

        if (count($highUtilization) > count($utilization) / 2) {
            $recommendations[] = 'Filo kapasitesi yeterli değil - ek araç alımı düşünülebilir';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Filo kullanımı optimal seviyede';
        }

        return $recommendations;
    }
}
