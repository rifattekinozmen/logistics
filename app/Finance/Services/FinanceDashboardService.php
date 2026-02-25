<?php

namespace App\Finance\Services;

use App\Models\Company;
use App\Models\Payment;
use Carbon\Carbon;

class FinanceDashboardService
{
    /**
     * Finans dashboard verilerini hazırla.
     */
    public function getDashboardData(?Company $company = null): array
    {
        $query = Payment::query();

        if ($company) {
            // Payment modelinde company_id yoksa, related_type/related_id üzerinden filtrele
            // Şimdilik tüm ödemeleri alıyoruz, ileride company scope eklenebilir
        }

        $today = Carbon::today();

        $overdueQuery = $query->clone()
            ->where('status', 0)
            ->where('due_date', '<', $today);
        $overdueCount = $overdueQuery->count();
        $overdueTotal = $overdueQuery->sum('amount');
        $overduePaymentsList = $query->clone()
            ->where('status', 0)
            ->where('due_date', '<', $today)
            ->orderBy('due_date')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'amount' => $p->amount,
                'due_date' => $p->due_date->format('d.m.Y'),
                'type' => $p->payment_type,
            ])->toArray();

        $dueTodayCount = $query->clone()->where('status', 0)->whereDate('due_date', $today)->count();
        $dueTodayTotal = $query->clone()->where('status', 0)->whereDate('due_date', $today)->sum('amount');

        $dueIn7Count = $query->clone()
            ->where('status', 0)
            ->whereBetween('due_date', [$today->copy()->addDay(), $today->copy()->addDays(7)])
            ->count();
        $dueIn7Total = $query->clone()
            ->where('status', 0)
            ->whereBetween('due_date', [$today->copy()->addDay(), $today->copy()->addDays(7)])
            ->sum('amount');

        $paidThisMonthCount = $query->clone()
            ->where('status', 1)
            ->whereMonth('paid_date', $today->month)
            ->whereYear('paid_date', $today->year)
            ->count();
        $paidThisMonthTotal = $query->clone()
            ->where('status', 1)
            ->whereMonth('paid_date', $today->month)
            ->whereYear('paid_date', $today->year)
            ->sum('amount');

        $pendingThisMonthCount = $query->clone()
            ->where('status', 0)
            ->whereMonth('due_date', $today->month)
            ->whereYear('due_date', $today->year)
            ->count();
        $pendingThisMonthTotal = $query->clone()
            ->where('status', 0)
            ->whereMonth('due_date', $today->month)
            ->whereYear('due_date', $today->year)
            ->sum('amount');

        return [
            'overdue' => [
                'count' => $overdueCount,
                'total_amount' => $overdueTotal,
                'payments' => $overduePaymentsList,
            ],
            'due_today' => [
                'count' => $dueTodayCount,
                'total_amount' => $dueTodayTotal,
            ],
            'due_in_7_days' => [
                'count' => $dueIn7Count,
                'total_amount' => $dueIn7Total,
            ],
            'paid_this_month' => [
                'count' => $paidThisMonthCount,
                'total_amount' => $paidThisMonthTotal,
            ],
            'pending_this_month' => [
                'count' => $pendingThisMonthCount,
                'total_amount' => $pendingThisMonthTotal,
            ],
        ];
    }

    /**
     * Nakit akış özeti (son 6 ay).
     */
    public function getCashFlowSummary(?Company $company = null, int $months = 6): array
    {
        $query = Payment::query();

        $startDate = Carbon::now()->subMonths($months)->startOfMonth();

        $payments = $query->where('due_date', '>=', $startDate)
            ->get()
            ->groupBy(function ($payment) {
                return $payment->due_date->format('Y-m');
            })
            ->map(function ($group) {
                return [
                    'income' => $group->where('payment_type', 'income')->where('status', 1)->sum('amount'),
                    'expense' => $group->where('payment_type', 'expense')->where('status', 1)->sum('amount'),
                    'pending_income' => $group->where('payment_type', 'income')->where('status', 0)->sum('amount'),
                    'pending_expense' => $group->where('payment_type', 'expense')->where('status', 0)->sum('amount'),
                ];
            });

        return $payments->toArray();
    }
}
