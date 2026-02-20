<?php

namespace Database\Factories;

use App\Pricing\Models\PricingCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Pricing\Models\PricingCondition>
 */
class PricingConditionFactory extends Factory
{
    protected $model = PricingCondition::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['weight_based', 'distance_based', 'flat', 'zone_based']);

        return [
            'company_id' => 1,
            'condition_type' => $type,
            'name' => fake()->words(3, true).' koşulu',
            'route_origin' => fake()->optional()->city(),
            'route_destination' => fake()->optional()->city(),
            'weight_from' => $type === 'weight_based' ? fake()->randomFloat(2, 0, 500) : null,
            'weight_to' => $type === 'weight_based' ? fake()->randomFloat(2, 500, 5000) : null,
            'distance_from' => $type === 'distance_based' ? fake()->randomFloat(2, 0, 100) : null,
            'distance_to' => $type === 'distance_based' ? fake()->randomFloat(2, 100, 1000) : null,
            'price_per_kg' => $type === 'weight_based' ? fake()->randomFloat(4, 0.5, 10) : null,
            'price_per_km' => $type === 'distance_based' ? fake()->randomFloat(4, 1, 20) : null,
            'flat_rate' => in_array($type, ['flat', 'zone_based'], true) ? fake()->randomFloat(2, 100, 5000) : null,
            'min_charge' => fake()->randomFloat(2, 50, 200),
            'currency' => fake()->randomElement(['TRY', 'USD', 'EUR']),
            'vehicle_type' => fake()->optional()->randomElement(['truck', 'van', 'trailer']),
            'valid_from' => null,
            'valid_to' => null,
            'status' => 1,
            'notes' => null,
        ];
    }
}
