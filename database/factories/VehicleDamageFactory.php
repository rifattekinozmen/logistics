<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDamage;
use App\Models\VehicleInspection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VehicleDamage>
 */
class VehicleDamageFactory extends Factory
{
    protected $model = VehicleDamage::class;

    public function definition(): array
    {
        return [
            'inspection_id' => null,
            'vehicle_id' => Vehicle::factory(),
            'damage_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'damage_location' => $this->faker->randomElement(['front', 'rear', 'right', 'left', 'top', 'bottom']),
            'damage_type' => $this->faker->randomElement(['scratch', 'dent', 'crack', 'paint_damage']),
            'damage_size' => $this->faker->optional()->randomElement(['small', 'medium', 'large']),
            'severity' => $this->faker->randomElement(['minor', 'moderate', 'severe']),
            'description' => $this->faker->optional()->sentence(),
            'digital_drawing_data' => null,
            'status' => $this->faker->randomElement(['detected', 'approved', 'repaired', 'cancelled']),
            'photos' => null,
            'created_by' => User::factory(),
            'approved_by' => null,
            'approved_at' => null,
            'repaired_at' => null,
        ];
    }
}
