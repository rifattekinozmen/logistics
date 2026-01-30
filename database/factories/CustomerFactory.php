<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'tax_number' => $this->faker->unique()->numerify('##########'),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'address' => $this->faker->address(),
            'status' => 1,
        ];
    }
}
