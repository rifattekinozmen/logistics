<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\InventoryStock;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryStock>
 */
class InventoryStockFactory extends Factory
{
    protected $model = InventoryStock::class;

    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'location_id' => null,
            'item_id' => InventoryItem::factory(),
            'quantity' => $this->faker->randomFloat(2, 1, 1000),
            'serial_number' => $this->faker->optional(0.3)->uuid(),
            'lot_number' => $this->faker->optional(0.4)->bothify('LOT-####-??'),
            'expiry_date' => $this->faker->optional(0.3)->dateTimeBetween('now', '+2 years'),
        ];
    }
}
