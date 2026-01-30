<?php

namespace App\AI\Services;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

/**
 * Operasyon analizi AI servisi.
 * 
 * Sipariş gecikmeleri, teslimat performansı, SLA takibi gibi konularda analiz yapar.
 */
class AIOperationsService extends AIService
{
    /**
     * Operasyon analizi çalıştır.
     */
    public function analyze(): array
    {
        $reports = [];

        // Geciken teslimatlar
        $delayedDeliveries = $this->analyzeDelayedDeliveries();
        if (!empty($delayedDeliveries)) {
            $reports[] = $this->createReport(
                'operations',
                "{$delayedDeliveries['count']} adet teslimat gecikme riski taşıyor.",
                $delayedDeliveries['count'] > 10 ? 'high' : ($delayedDeliveries['count'] > 5 ? 'medium' : 'low'),
                $delayedDeliveries
            );
        }

        // Teslimat performansı
        $performance = $this->analyzeDeliveryPerformance();
        if ($performance['score'] < 70) {
            $reports[] = $this->createReport(
                'operations',
                "Teslimat performans puanı düşük: {$performance['score']}/100",
                $performance['score'] < 50 ? 'high' : 'medium',
                $performance
            );
        }

        return $reports;
    }

    /**
     * Geciken teslimatları analiz et.
     */
    protected function analyzeDelayedDeliveries(): array
    {
        $delayed = Order::where('status', '!=', 'delivered')
            ->where('status', '!=', 'cancelled')
            ->where('planned_delivery_date', '<', now())
            ->count();

        return [
            'count' => $delayed,
            'message' => $delayed > 0 ? "{$delayed} adet sipariş planlanan teslimat tarihini geçti." : null,
        ];
    }

    /**
     * Teslimat performansını analiz et.
     */
    protected function analyzeDeliveryPerformance(): array
    {
        $total = Order::where('status', 'delivered')->count();
        $onTime = Order::where('status', 'delivered')
            ->whereColumn('delivered_at', '<=', 'planned_delivery_date')
            ->count();

        $score = $total > 0 ? round(($onTime / $total) * 100, 2) : 100;

        return [
            'score' => $score,
            'total_delivered' => $total,
            'on_time' => $onTime,
            'late' => $total - $onTime,
        ];
    }
}
