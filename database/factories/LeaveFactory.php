<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Leave>
 */
class LeaveFactory extends Factory
{
    protected $model = Leave::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 month');
        $endDate = (clone $startDate)->modify('+'.$this->faker->numberBetween(1, 14).' days');
        $totalDays = (int) $startDate->diff($endDate)->days + 1;

        return [
            'employee_id' => Employee::factory(),
            'leave_type' => $this->faker->randomElement(['annual', 'sick', 'unpaid', 'personal']),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_days' => $totalDays,
            'reason' => $this->faker->optional()->sentence(),
            'status' => 'pending',
        ];
    }
}
