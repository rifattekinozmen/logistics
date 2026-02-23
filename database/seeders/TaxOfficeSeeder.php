<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\TaxOffice;
use Illuminate\Database\Seeder;

class TaxOfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $turkey = Country::where('code', 'TR')->first();
        if (! $turkey) {
            return;
        }

        $cities = [
            'İstanbul' => ['Kadıköy VD', 'Beşiktaş VD', 'Şişli VD', 'Sarıyer VD', 'Kartal VD', 'Bakırköy VD', 'Üsküdar VD', 'Beyoğlu VD', 'Fatih VD', 'Ataşehir VD'],
            'Ankara' => ['Çankaya VD', 'Kızılay VD', 'Ulus VD', 'Yenimahalle VD', 'Mamak VD', 'Keçiören VD'],
            'İzmir' => ['Konak VD', 'Karşıyaka VD', 'Bornova VD', 'Buca VD', 'Alsancak VD'],
            'Adana' => ['Seyhan VD', 'Çukurova VD', 'Yüreğir VD'],
            'Antalya' => ['Muratpaşa VD', 'Konyaaltı VD', 'Kepez VD'],
            'Bursa' => ['Osmangazi VD', 'Nilüfer VD', 'Yıldırım VD'],
            'Kocaeli' => ['İzmit VD', 'Gebze VD'],
            'Gaziantep' => ['Şahinbey VD', 'Şehitkamil VD'],
            'Konya' => ['Selçuklu VD', 'Meram VD'],
            'Mersin' => ['Mezitli VD', 'Yenişehir VD'],
        ];

        foreach ($cities as $cityName => $taxOffices) {
            $city = City::firstOrCreate(
                [
                    'country_id' => $turkey->id,
                    'name_tr' => $cityName,
                ],
                [
                    'plate_code' => $this->getPlateCode($cityName),
                    'is_active' => true,
                ]
            );

            foreach ($taxOffices as $name) {
                TaxOffice::firstOrCreate(
                    [
                        'name' => $name,
                        'city_id' => $city->id,
                    ],
                    [
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function getPlateCode(string $cityName): string
    {
        return match ($cityName) {
            'İstanbul' => '34',
            'Ankara' => '06',
            'İzmir' => '35',
            'Adana' => '01',
            'Antalya' => '07',
            'Bursa' => '16',
            'Kocaeli' => '41',
            'Gaziantep' => '27',
            'Konya' => '42',
            'Mersin' => '33',
            default => '00',
        };
    }
}
