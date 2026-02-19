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
            'tckn' => $this->faker->numerify('###########'),
            'kimlik_seri_no' => 'A01-'.$this->faker->numerify('######'),
            'email' => $this->faker->unique()->safeEmail(),
            'telefon' => $this->faker->numerify('5##########'),
            'mobil_telefon' => $this->faker->numerify('5##########'),
            'acil_iletisim' => $this->faker->numerify('5##########'),
            'anne_adi' => $this->faker->firstName('female'),
            'baba_adi' => $this->faker->firstName('male'),
            'dogum_tarihi' => $this->faker->dateTimeBetween('-50 years', '-18 years'),
            'dogum_yeri' => $this->faker->city(),
            'medeni_durum' => $this->faker->randomElement(['Bekar', 'Evli', 'Dul', 'Boşanmış']),
            'departman' => $this->faker->randomElement(['Lojistik', 'İnsan Kaynakları', 'Muhasebe', 'Satış', 'IT', 'Operasyon']),
            'pozisyon' => $this->faker->jobTitle(),
            'ise_baslama_tarihi' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'maas' => $this->faker->randomFloat(2, 5000, 50000),
            'aktif' => true,
        ];
    }
}
