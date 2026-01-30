<?php

namespace App\Finance\Services;

use App\Models\Payment;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        
        // Geciken ödemeler
        $overduePayments = $query->clone()
            ->where('status', 0) // Bekliyor
            ->where('due_date', '<', $today)
            ->get();

        // Bugün vadesi gelenler
        $dueToday = $query->clone()
            ->where('status', 0)
            ->whereDate('due_date', $today)
            ->get();

        // 7 gün içinde vadesi gelecekler
        $dueIn7Days = $query->clone()
            ->where('status', 0)
            ->whereBetween('due_date', [$today->copy()->addDay(), $today->copy()->addDays(7)])
            ->get();

        // Bu ay ödenenler
        $paidThisMonth = $query->clone()
            ->where('status', 1) // Ödendi
            ->whereMonth('paid_date', $today->month)
            ->whereYear('paid_date', $today->year)
            ->get();

        // Bu ay bekleyenler
        $pendingThisMonth = $query->clone()
            ->where('status', 0)
            ->whereMonth('due_date', $today->month)
            ->whereYear('due_date', $today->year)
            ->get();

        return [
            'overdue' => [
                'count' => $overduePayments->count(),
                'total_amount' => $overduePayments->sum('amount'),
                'payments' => $overduePayments->take(5)->map(fn($p) => [
                    'id' => $p->id,
                    'amount' => $p->amount,
                    'due_date' => $p->due_date->format('d.m.Y'),
                    'type' => $p->payment_type,
                ])->toArray(),
            ],
            'due_today' => [
                'count' => $dueToday->count(),
                'total_amount' => $dueToday->sum('amount'),
            ],
            'due_in_7_days' => [
                'count' => $dueIn7Days->count(),
                'total_amount' => $dueIn7Days->sum('amount'),
            ],
            'paid_this_month' => [
                'count' => $paidThisMonth->count(),
                'total_amount' => $paidThisMonth->sum('amount'),
            ],
            'pending_this_month' => [
                'count' => $pendingThisMonth->count(),
                'total_amount' => $pendingThisMonth->sum('amount'),
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
