<?php

namespace Database\Factories;

use App\Enums\AskerlikDurumu;
use App\Enums\Cinsiyet;
use App\Enums\KanGrubu;
use App\Enums\MaasOdemeTuru;
use App\Enums\MedeniDurum;
use App\Enums\TahsilDurumu;
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
            'personel_kodu' => null,
            'ad_soyad' => $this->faker->name(),
            'tckn' => $this->faker->numerify('###########'),
            'kimlik_seri_no' => 'A01'.$this->faker->numerify('######'),
            'cilt_no' => $this->faker->numerify('###'),
            'aile_sira_no' => $this->faker->numerify('####'),
            'sira_no' => $this->faker->numerify('######'),
            'cuzdan_kayit_no' => $this->faker->numerify('#####'),
            'verilis_tarihi' => $this->faker->dateTimeBetween('-10 years', '-1 year'),
            'email' => $this->faker->unique()->safeEmail(),
            'telefon' => $this->faker->numerify('5##########'),
            'mobil_telefon' => $this->faker->numerify('5##########'),
            'acil_iletisim' => $this->faker->numerify('5##########'),
            'anne_adi' => $this->faker->firstName('female'),
            'baba_adi' => $this->faker->firstName('male'),
            'dogum_tarihi' => $this->faker->dateTimeBetween('-50 years', '-18 years'),
            'dogum_yeri' => $this->faker->city(),
            'medeni_durum' => $this->faker->randomElement(array_column(MedeniDurum::cases(), 'value')),
            'kan_grubu' => $this->faker->randomElement(array_column(KanGrubu::cases(), 'value')),
            'cinsiyet' => $this->faker->randomElement(array_column(Cinsiyet::cases(), 'value')),
            'cocuk_sayisi' => $this->faker->numberBetween(0, 4),
            'adres_satir_1' => $this->faker->streetAddress(),
            'bulvar' => $this->faker->optional()->streetName(),
            'sokak' => $this->faker->optional()->streetName(),
            'dis_kapi' => (string) $this->faker->numberBetween(1, 99),
            'ic_kapi' => (string) $this->faker->optional()->numberBetween(1, 20),
            'posta_kodu' => $this->faker->postcode(),
            'departman' => $this->faker->randomElement(['Lojistik', 'İnsan Kaynakları', 'Muhasebe', 'Satış', 'IT', 'Operasyon']),
            'pozisyon' => $this->faker->jobTitle(),
            'ise_baslama_tarihi' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'sgk_baslangic_tarihi' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'maas' => $this->faker->randomFloat(2, 5000, 50000),
            'tahsil_durumu' => $this->faker->randomElement(array_column(TahsilDurumu::cases(), 'value')),
            'mezun_okul' => $this->faker->optional()->company().' Lisesi',
            'mezun_bolum' => $this->faker->randomElement(['Lise', 'Lisans', 'Ön Lisans']),
            'mezuniyet_tarihi' => $this->faker->optional()->dateTimeBetween('-15 years', '-2 years'),
            'bildigi_dil' => $this->faker->optional()->randomElement(['İngilizce', 'Almanca', 'Fransızca']),
            'askerlik_durumu' => $this->faker->randomElement(array_column(AskerlikDurumu::cases(), 'value')),
            'sgk_yaslilik_ayligi' => $this->faker->boolean(20),
            'sgk_30_gunden_az' => $this->faker->boolean(10),
            'banka_adi' => $this->faker->optional()->randomElement(array_keys(config('personnel.banks', []))),
            'sube_kodu' => $this->faker->optional()->numerify('####'),
            'hesap_no' => $this->faker->optional()->numerify('########'),
            'maas_odeme_turu' => $this->faker->randomElement(array_column(MaasOdemeTuru::cases(), 'value')),
            'iban' => 'TR'.$this->faker->numerify('##############################'),
            'aktif' => true,
            'notlar' => $this->faker->optional(0.3)->sentence(),
        ];
    }
}
