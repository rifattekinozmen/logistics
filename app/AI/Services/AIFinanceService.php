<?php

namespace App\AI\Services;

use App\Models\Payment;

/**
 * Finansal analiz AI servisi.
 *
 * Ödeme riskleri, nakit akışı, geciken ödemeler gibi konularda analiz yapar.
 */
class AIFinanceService extends AIService
{
    /**
     * Finansal analiz çalıştır.
     */
    public function analyze(): array
    {
        $reports = [];

        // Geciken ödemeler
        $overduePayments = $this->analyzeOverduePayments();
        if ($overduePayments['total_amount'] > 0) {
            $reports[] = $this->createReport(
                'finance',
                'Geciken ödemeler: '.number_format($overduePayments['total_amount'], 2).' TL',
                $overduePayments['total_amount'] > 100000 ? 'high' : ($overduePayments['total_amount'] > 50000 ? 'medium' : 'low'),
                $overduePayments
            );
        }

        // Yaklaşan ödemeler
        $upcomingPayments = $this->analyzeUpcomingPayments();
        if ($upcomingPayments['total_amount'] > 0) {
            $reports[] = $this->createReport(
                'finance',
                '7 gün içinde '.number_format($upcomingPayments['total_amount'], 2).' TL ödeme yapılacak.',
                'low',
                $upcomingPayments
            );
        }

        // Anomali: geciken ödemeler ortalamanın çok üzerinde mi? (risk_score, volatility ile)
        $overdueAnomaly = $this->detectOverdueAnomaly();
        if ($overdueAnomaly !== null) {
            $reports[] = $overdueAnomaly;
        }

        return $reports;
    }

    /**
     * Son 6 aylık aylık ödeme toplamlarına göre volatilite: low / medium / high.
     */
    protected function computeVolatility(): string
    {
        $months = [];
        for ($i = 0; $i < 6; $i++) {
            $start = now()->subMonths($i + 1)->startOfMonth();
            $end = now()->subMonths($i + 1)->endOfMonth();
            $months[] = (float) Payment::where('status', Payment::STATUS_PAID)
                ->whereBetween('paid_date', [$start, $end])
                ->sum('amount');
        }
        $mean = array_sum($months) / 6;
        if ($mean <= 0) {
            return 'low';
        }
        $variance = array_sum(array_map(fn ($x) => ($x - $mean) ** 2, $months)) / 6;
        $std = sqrt($variance);
        $coefficientOfVariation = $std / $mean;
        if ($coefficientOfVariation < 0.5) {
            return 'low';
        }
        if ($coefficientOfVariation <= 1.0) {
            return 'medium';
        }

        return 'high';
    }

    /**
     * Geciken ödemeleri analiz et.
     */
    protected function analyzeOverduePayments(): array
    {
        $overdue = Payment::where('status', 0) // Bekliyor
            ->where('due_date', '<', now())
            ->get();

        return [
            'count' => $overdue->count(),
            'total_amount' => $overdue->sum('amount'),
            'payments' => $overdue->map(fn ($p) => [
                'id' => $p->id,
                'amount' => $p->amount,
                'due_date' => $p->due_date->format('Y-m-d'),
            ])->toArray(),
        ];
    }

    /**
     * Yaklaşan ödemeleri analiz et.
     */
    protected function analyzeUpcomingPayments(): array
    {
        $upcoming = Payment::where('status', 0) // Bekliyor
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->get();

        return [
            'count' => $upcoming->count(),
            'total_amount' => $upcoming->sum('amount'),
        ];
    }

    /**
     * Geciken ödemelerde anomali tespit et (ortalamaya göre aşırı yüksek gecikme).
     * risk_score, overdue_ratio, volatility ile severity (ADVANCED_SCORING.md).
     */
    public function detectOverdueAnomaly(): ?array
    {
        $overdue = $this->analyzeOverduePayments();
        $overdueTotal = (float) $overdue['total_amount'];
        if ($overdueTotal <= 0) {
            return null;
        }

        $avgMonthlyPaid = (float) Payment::where('status', Payment::STATUS_PAID)
            ->where('paid_date', '>=', now()->subMonths(3))
            ->selectRaw('SUM(amount) as total')
            ->value('total');
        $avgMonthlyPaid = $avgMonthlyPaid / 3;

        $avgMonthlyPaid = max($avgMonthlyPaid, 1);
        $overdueRatio = $overdueTotal / $avgMonthlyPaid;
        $riskScore = (float) min(100, $overdueRatio * 25);
        $volatility = $this->computeVolatility();

        $severity = $riskScore >= 80 || $volatility === 'high'
            ? 'high'
            : ($riskScore >= 50 || $volatility === 'medium' ? 'medium' : 'low');

        if ($overdueRatio < 1.5) {
            return null;
        }

        return $this->createReport(
            'finance',
            sprintf(
                'Anomali: Geciken ödemeler (%.2f TL) son 3 ay ortalamasının %.1f katı.',
                $overdueTotal,
                round($overdueRatio, 1)
            ),
            $severity,
            [
                'overdue_total' => round($overdueTotal, 2),
                'overdue_count' => $overdue['count'],
                'avg_monthly_paid_3m' => round($avgMonthlyPaid, 2),
                'overdue_ratio' => round($overdueRatio, 2),
                'risk_score' => round($riskScore, 1),
                'volatility' => $volatility,
            ]
        );
    }
}
