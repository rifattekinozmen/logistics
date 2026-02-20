<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Warehouse>
 */
class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'branch_id' => Branch::factory(),
            'code' => 'WH-'.$this->faker->unique()->numerify('####'),
            'name' => $this->faker->company().' Deposu',
            'address' => $this->faker->address(),
            'warehouse_type' => $this->faker->randomElement(['main', 'distribution', 'storage', 'cold']),
            'status' => 1,
            'manager_id' => User::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }
}
