<?php

namespace App\AI\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

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
                "Geciken ödemeler: " . number_format($overduePayments['total_amount'], 2) . " TL",
                $overduePayments['total_amount'] > 100000 ? 'high' : ($overduePayments['total_amount'] > 50000 ? 'medium' : 'low'),
                $overduePayments
            );
        }

        // Yaklaşan ödemeler
        $upcomingPayments = $this->analyzeUpcomingPayments();
        if ($upcomingPayments['total_amount'] > 0) {
            $reports[] = $this->createReport(
                'finance',
                "7 gün içinde " . number_format($upcomingPayments['total_amount'], 2) . " TL ödeme yapılacak.",
                'low',
                $upcomingPayments
            );
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
            'payments' => $overdue->map(fn($p) => [
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
}
