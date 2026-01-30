<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => $this->faker->randomElement(['Lojistik', 'İnsan Kaynakları', 'Muhasebe', 'Satış', 'IT', 'Operasyon', 'Depo', 'Bakım']),
            'description' => $this->faker->sentence(),
        ];
    }
}
