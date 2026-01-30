<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\District;
use App\Models\Neighborhood;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Türkiye
        $turkey = Country::firstOrCreate(
            ['code' => 'TR'],
            [
                'name_tr' => 'Türkiye',
                'name_en' => 'Turkey',
                'phone_code' => '+90',
                'currency_code' => 'TRY',
                'is_active' => true,
            ]
        );

        // İstanbul
        $istanbul = City::firstOrCreate(
            ['country_id' => $turkey->id, 'name_tr' => 'İstanbul'],
            [
                'plate_code' => '34',
                'is_active' => true,
            ]
        );

        // Kadıköy
        $kadikoy = District::firstOrCreate(
            ['city_id' => $istanbul->id, 'name_tr' => 'Kadıköy'],
            [
                'is_active' => true,
            ]
        );

        // Acıbadem
        Neighborhood::firstOrCreate(
            ['district_id' => $kadikoy->id, 'name_tr' => 'Acıbadem'],
            [
                'is_active' => true,
            ]
        );
    }
}
