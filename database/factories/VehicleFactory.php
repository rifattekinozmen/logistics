<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        $brands = ['Mercedes', 'Volvo', 'Scania', 'MAN', 'Iveco', 'Ford', 'Renault'];
        $models = ['Actros', 'FH', 'R Series', 'TGX', 'Stralis', 'Transit', 'Master'];
        $types = ['truck', 'van', 'car', 'trailer'];

        return [
            'plate' => $this->faker->unique()->regexify('[0-9]{2}[A-Z]{1,3}[0-9]{2,4}'),
            'brand' => $this->faker->randomElement($brands),
            'model' => $this->faker->randomElement($models),
            'year' => $this->faker->numberBetween(2010, 2025),
            'vehicle_type' => $this->faker->randomElement($types),
            'capacity_kg' => $this->faker->randomFloat(2, 1000, 50000),
            'capacity_m3' => $this->faker->randomFloat(2, 10, 100),
            'status' => 1,
            'branch_id' => Branch::factory(),
        ];
    }
}
