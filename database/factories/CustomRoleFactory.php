<?php

namespace Database\Factories;

use App\Models\CustomRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomRole>
 */
class CustomRoleFactory extends Factory
{
    protected $model = CustomRole::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['admin', 'operation', 'accounting', 'driver', 'customer']),
            'description' => $this->faker->sentence(),
        ];
    }
}
