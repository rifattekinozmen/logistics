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

        // Anomali: geciken ödemeler ortalamanın çok üzerinde mi?
        $overdueAnomaly = $this->detectOverdueAnomaly();
        if ($overdueAnomaly !== null) {
            $reports[] = $overdueAnomaly;
        }

        return $reports;
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
     * Son 3 aydaki aylık ortalama ödenen tutarla karşılaştırır.
     */
    public function detectOverdueAnomaly(): ?array
    {
        $overdue = $this->analyzeOverduePayments();
        if ($overdue['total_amount'] <= 0) {
            return null;
        }

        $avgMonthlyPaid = (float) Payment::where('status', Payment::STATUS_PAID)
            ->where('paid_date', '>=', now()->subMonths(3))
            ->selectRaw('SUM(amount) as total')
            ->value('total');
        $avgMonthlyPaid = $avgMonthlyPaid / 3;

        if ($avgMonthlyPaid <= 0) {
            return null;
        }

        $ratio = $overdue['total_amount'] / $avgMonthlyPaid;
        if ($ratio < 1.5) {
            return null;
        }

        $severity = $ratio >= 3 ? 'high' : ($ratio >= 2 ? 'medium' : 'low');

        return $this->createReport(
            'finance',
            sprintf(
                'Anomali: Geciken ödemeler (%.2f TL) son 3 ay ortalamasının %.1f katı.',
                $overdue['total_amount'],
                round($ratio, 1)
            ),
            $severity,
            [
                'overdue_total' => $overdue['total_amount'],
                'overdue_count' => $overdue['count'],
                'avg_monthly_paid_3m' => round($avgMonthlyPaid, 2),
                'ratio' => round($ratio, 2),
            ]
        );
    }
}
