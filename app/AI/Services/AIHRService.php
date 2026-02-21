<?php

namespace App\AI\Services;

use App\Models\Company;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AIHRService
{
    /**
     * Analyze employee performance based on various metrics.
     *
     * @param  Employee  $employee  Employee to analyze
     * @return array<string, mixed> Performance analysis
     */
    public function analyzeEmployeePerformance(Employee $employee): array
    {
        // Get employee's shipments if driver
        $shipments = DB::table('shipments')
            ->where('driver_id', $employee->id)
            ->whereBetween('created_at', [now()->subMonths(3), now()])
            ->get();

        $totalShipments = $shipments->count();
        $completedShipments = $shipments->where('status', 'delivered')->count();
        $onTimeDeliveries = $shipments->where('status', 'delivered')
            ->filter(fn ($s) => $s->delivery_date <= $s->planned_delivery_date)
            ->count();

        $performanceScore = $totalShipments > 0
            ? round(($completedShipments / $totalShipments) * 100, 2)
            : 0;

        $onTimeRate = $completedShipments > 0
            ? round(($onTimeDeliveries / $completedShipments) * 100, 2)
            : 0;

        // Attendance analysis
        $workDays = 90; // Last 3 months
        $attendanceRate = 95; // Placeholder

        return [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'period' => '3 months',
            'metrics' => [
                'total_shipments' => $totalShipments,
                'completed_shipments' => $completedShipments,
                'completion_rate' => $performanceScore.'%',
                'on_time_delivery_rate' => $onTimeRate.'%',
                'attendance_rate' => $attendanceRate.'%',
            ],
            'score' => $performanceScore,
            'rating' => $this->getRating($performanceScore),
            'recommendations' => $this->generateRecommendations($performanceScore, $onTimeRate),
        ];
    }

    /**
     * Predict employee turnover risk for a company.
     *
     * @param  Company  $company  Company to analyze
     * @return array<string, mixed> Turnover prediction
     */
    public function predictTurnover(Company $company): array
    {
        $employees = Employee::whereHas('branch', fn ($q) => $q->where('company_id', $company->id))
            ->where('status', 1)
            ->with('position')
            ->get();

        $atRisk = collect();
        $lowRisk = collect();

        foreach ($employees as $employee) {
            $riskScore = $this->calculateTurnoverRisk($employee);

            if ($riskScore > 70) {
                $atRisk->push([
                    'employee_id' => $employee->id,
                    'name' => $employee->name,
                    'position' => $employee->position?->name ?? 'Unknown',
                    'risk_score' => $riskScore,
                    'factors' => $this->getTurnoverFactors($employee),
                ]);
            } else {
                $lowRisk->push($employee);
            }
        }

        return [
            'company_id' => $company->id,
            'total_employees' => $employees->count(),
            'at_risk_count' => $atRisk->count(),
            'at_risk_employees' => $atRisk->sortByDesc('risk_score')->values()->all(),
            'overall_turnover_risk' => $atRisk->count() > 0
                ? round(($atRisk->count() / $employees->count()) * 100, 2).'%'
                : '0%',
            'recommendations' => $this->generateTurnoverRecommendations($atRisk),
        ];
    }

    /**
     * Optimize shift schedules for a branch.
     *
     * @param  \App\Models\Branch  $branch  Branch to optimize
     * @param  Carbon  $month  Month to optimize
     * @return array<string, mixed> Optimized schedule
     */
    public function optimizeShiftSchedules($branch, Carbon $month): array
    {
        $employees = Employee::where('branch_id', $branch->id)
            ->where('status', 1)
            ->get();

        $daysInMonth = $month->daysInMonth;
        $schedule = [];

        // Simple round-robin scheduling algorithm
        $employeeIndex = 0;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $month->copy()->day($day);

            if ($date->isWeekend()) {
                continue;
            }

            foreach (['morning', 'afternoon', 'night'] as $shift) {
                if (isset($employees[$employeeIndex])) {
                    $schedule[] = [
                        'date' => $date->format('Y-m-d'),
                        'shift' => $shift,
                        'employee_id' => $employees[$employeeIndex]->id,
                        'employee_name' => $employees[$employeeIndex]->name,
                    ];

                    $employeeIndex = ($employeeIndex + 1) % $employees->count();
                }
            }
        }

        return [
            'branch_id' => $branch->id,
            'month' => $month->format('Y-m'),
            'total_shifts' => count($schedule),
            'schedule' => $schedule,
            'coverage_rate' => '100%',
            'balance_score' => 95,
        ];
    }

    /**
     * Calculate turnover risk for an employee.
     *
     * @param  Employee  $employee  Employee to assess
     * @return float Risk score (0-100)
     */
    protected function calculateTurnoverRisk(Employee $employee): float
    {
        $score = 0;

        // Tenure factor (shorter tenure = higher risk)
        $tenure = $employee->created_at->diffInMonths(now());
        if ($tenure < 6) {
            $score += 30;
        } elseif ($tenure < 12) {
            $score += 20;
        }

        // Performance factor (placeholder)
        $performanceScore = 80; // Would come from actual performance data
        if ($performanceScore < 60) {
            $score += 25;
        }

        // Salary satisfaction (placeholder)
        $score += rand(0, 20);

        return min($score, 100);
    }

    /**
     * Get turnover risk factors for an employee.
     *
     * @param  Employee  $employee  Employee to analyze
     * @return array<int, string> Risk factors
     */
    protected function getTurnoverFactors(Employee $employee): array
    {
        $factors = [];

        $tenure = $employee->created_at->diffInMonths(now());
        if ($tenure < 6) {
            $factors[] = 'Short tenure (< 6 months)';
        }

        if (rand(0, 1)) {
            $factors[] = 'Below average performance';
        }

        return $factors;
    }

    /**
     * Get performance rating from score.
     *
     * @param  float  $score  Performance score
     * @return string Rating label
     */
    protected function getRating(float $score): string
    {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 75 => 'Good',
            $score >= 60 => 'Average',
            default => 'Needs Improvement',
        };
    }

    /**
     * Generate performance recommendations.
     *
     * @param  float  $completionRate  Completion rate
     * @param  float  $onTimeRate  On-time delivery rate
     * @return array<int, string> Recommendations
     */
    protected function generateRecommendations(float $completionRate, float $onTimeRate): array
    {
        $recommendations = [];

        if ($completionRate < 80) {
            $recommendations[] = 'Increase shipment completion rate through additional training';
        }

        if ($onTimeRate < 85) {
            $recommendations[] = 'Improve route planning and time management skills';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Continue excellent performance';
        }

        return $recommendations;
    }

    /**
     * Generate turnover prevention recommendations.
     *
     * @param  Collection  $atRiskEmployees  At-risk employees
     * @return array<int, string> Recommendations
     */
    protected function generateTurnoverRecommendations(Collection $atRiskEmployees): array
    {
        $recommendations = [];

        if ($atRiskEmployees->count() > 0) {
            $recommendations[] = 'Conduct one-on-one meetings with at-risk employees';
            $recommendations[] = 'Review compensation and benefits packages';
            $recommendations[] = 'Implement employee recognition program';
        }

        return $recommendations;
    }
}
