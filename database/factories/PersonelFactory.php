<?php

namespace Database\Factories;

use App\Models\Personel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Personel>
 */
class PersonelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Personel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ad_soyad' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'telefon' => $this->faker->phoneNumber(),
            'departman' => $this->faker->randomElement(['Lojistik', 'İnsan Kaynakları', 'Muhasebe', 'Satış', 'IT', 'Operasyon']),
            'pozisyon' => $this->faker->jobTitle(),
            'ise_baslama_tarihi' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'maas' => $this->faker->randomFloat(2, 5000, 50000),
            'aktif' => true,
        ];
    }
}
