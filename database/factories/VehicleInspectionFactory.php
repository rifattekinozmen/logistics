<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VehicleInspection>
 */
class VehicleInspectionFactory extends Factory
{
    protected $model = VehicleInspection::class;

    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'inspection_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'inspector_name' => $this->faker->name(),
            'status' => $this->faker->randomElement(['pending', 'passed', 'failed', 'conditional']),
            'notes' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
