<?php

namespace Database\Factories;

use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WarehouseLocation>
 */
class WarehouseLocationFactory extends Factory
{
    protected $model = WarehouseLocation::class;

    public function definition(): array
    {
        $code = strtoupper($this->faker->bothify('??-##'));
        $name = $this->faker->words(2, true);

        return [
            'warehouse_id' => Warehouse::factory(),
            'parent_id' => null,
            'location_type' => $this->faker->randomElement(['zone', 'aisle', 'rack', 'shelf', 'position']),
            'code' => $code,
            'name' => $name,
            'full_path' => $code,
            'capacity' => $this->faker->optional(0.7)->randomFloat(2, 10, 1000),
            'status' => 1,
        ];
    }
}
