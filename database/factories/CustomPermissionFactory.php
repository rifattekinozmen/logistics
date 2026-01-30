<?php

namespace Database\Factories;

use App\Models\CustomPermission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomPermission>
 */
class CustomPermissionFactory extends Factory
{
    protected $model = CustomPermission::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->word().'.'.$this->faker->randomElement(['create', 'view', 'update', 'delete']),
            'description' => $this->faker->sentence(),
        ];
    }
}
