<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'documentable_type' => Vehicle::class,
            'documentable_id' => Vehicle::factory(),
            'category' => $this->faker->randomElement(['license', 'insurance', 'inspection', 'identity']),
            'name' => $this->faker->words(3, true).'.pdf',
            'file_path' => 'documents/'.$this->faker->uuid().'.pdf',
            'file_size' => $this->faker->numberBetween(1000, 500000),
            'mime_type' => 'application/pdf',
            'valid_from' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'valid_until' => $this->faker->dateTimeBetween('now', '+1 year'),
            'version' => 1,
            'tags' => null,
            'uploaded_by' => User::factory(),
        ];
    }
}
