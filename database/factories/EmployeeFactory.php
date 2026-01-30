<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'branch_id' => Branch::factory(),
            'position_id' => Position::factory(),
            'employee_number' => 'EMP-'.$this->faker->unique()->numerify('######'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'salary' => $this->faker->randomFloat(2, 5000, 50000),
            'hire_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'status' => 1,
        ];
    }
}
