<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shipment>
 */
class ShipmentFactory extends Factory
{
    protected $model = Shipment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'vehicle_id' => Vehicle::factory(),
            'driver_id' => Employee::factory(),
            'status' => $this->faker->randomElement(['pending', 'in_transit', 'delivered', 'cancelled']),
            'pickup_date' => $this->faker->dateTimeBetween('now', '+3 days'),
            'delivery_date' => $this->faker->dateTimeBetween('+3 days', '+7 days'),
            'qr_code' => $this->faker->unique()->uuid(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'pickup_date' => null,
            'delivery_date' => null,
        ]);
    }

    public function inTransit(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_transit',
            'pickup_date' => $this->faker->dateTimeBetween('-2 days', 'now'),
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'pickup_date' => $this->faker->dateTimeBetween('-5 days', '-3 days'),
            'delivery_date' => $this->faker->dateTimeBetween('-2 days', 'now'),
        ]);
    }
}
