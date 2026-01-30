<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'order_number' => 'ORD-'.$this->faker->unique()->numerify('########'),
            'status' => $this->faker->randomElement(['pending', 'assigned', 'in_transit', 'delivered', 'cancelled']),
            'pickup_address' => $this->faker->address(),
            'delivery_address' => $this->faker->address(),
            'planned_pickup_date' => $this->faker->dateTimeBetween('now', '+7 days'),
            'planned_delivery_date' => $this->faker->dateTimeBetween('+7 days', '+14 days'),
            'actual_pickup_date' => null,
            'delivered_at' => null,
            'total_weight' => $this->faker->randomFloat(2, 100, 10000),
            'total_volume' => $this->faker->randomFloat(2, 1, 100),
            'is_dangerous' => $this->faker->boolean(20),
            'notes' => $this->faker->optional()->sentence(),
            'created_by' => null,
        ];
    }
}
