<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    protected $model = InventoryItem::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'sku' => 'SKU-'.$this->faker->unique()->numerify('########'),
            'barcode' => $this->faker->optional(0.6)->ean13(),
            'name' => $this->faker->words(3, true),
            'category' => $this->faker->optional(0.7)->randomElement(['hammadde', 'yarı mamul', 'mamul', 'yardımcı malzeme']),
            'unit' => $this->faker->randomElement(['piece', 'kg', 'liter', 'm2', 'm3']),
            'min_stock_level' => 0,
            'max_stock_level' => $this->faker->optional(0.5)->randomFloat(2, 100, 10000),
            'critical_stock_level' => $this->faker->optional(0.5)->randomFloat(2, 1, 100),
            'track_serial' => false,
            'track_lot' => false,
            'status' => 1,
        ];
    }
}
