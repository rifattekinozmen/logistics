<?php

namespace Database\Factories;

use App\Enums\CustomerPriority;
use App\Enums\CustomerType;
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
            'customer_code' => $this->faker->optional()->numerify('MST-#####'),
            'customer_type' => $this->faker->optional()->randomElement(array_column(CustomerType::cases(), 'value')),
            'priority_level' => $this->faker->optional()->randomElement(array_column(CustomerPriority::cases(), 'value')),
            'contact_person' => $this->faker->optional()->name(),
            'name' => $this->faker->company(),
            'tax_number' => $this->faker->unique()->numerify('##########'),
            'tax_office' => $this->faker->optional()->city().' VD',
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'address' => $this->faker->address(),
            'status' => 1,
        ];
    }
}
