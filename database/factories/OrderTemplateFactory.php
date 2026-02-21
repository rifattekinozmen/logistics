<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\OrderTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderTemplate>
 */
class OrderTemplateFactory extends Factory
{
    protected $model = OrderTemplate::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'name' => $this->faker->words(3, true),
            'pickup_address' => $this->faker->address(),
            'delivery_address' => $this->faker->address(),
            'total_weight' => $this->faker->randomFloat(2, 100, 5000),
            'total_volume' => $this->faker->randomFloat(2, 1, 50),
            'is_dangerous' => $this->faker->boolean(20),
            'notes' => $this->faker->optional()->sentence(),
            'sort_order' => 0,
        ];
    }
}
