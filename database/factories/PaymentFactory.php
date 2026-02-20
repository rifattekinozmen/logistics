<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $isPaid = $this->faker->boolean(60);

        return [
            'related_type' => Customer::class,
            'related_id' => Customer::factory(),
            'payment_type' => $this->faker->randomElement(['incoming', 'outgoing']),
            'amount' => $this->faker->randomFloat(2, 100, 50000),
            'due_date' => $this->faker->dateTimeBetween('-30 days', '+30 days'),
            'paid_date' => $isPaid ? $this->faker->dateTimeBetween('-10 days', 'now') : null,
            'status' => $isPaid ? 1 : 0,
            'payment_method' => $isPaid ? $this->faker->randomElement(['cash', 'bank_transfer', 'credit_card', 'check']) : null,
            'reference_number' => $isPaid ? $this->faker->numerify('REF-########') : null,
            'notes' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
            'paid_date' => null,
            'payment_method' => null,
            'reference_number' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
            'paid_date' => $this->faker->dateTimeBetween('-10 days', 'now'),
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'credit_card', 'check']),
            'reference_number' => $this->faker->numerify('REF-########'),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
            'due_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
            'paid_date' => null,
        ]);
    }
}
