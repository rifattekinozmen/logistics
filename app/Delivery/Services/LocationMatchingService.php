<?php

namespace App\Delivery\Services;

use App\Models\Location;
use App\Models\City;
use App\Models\District;
use App\Models\Neighborhood;
use Illuminate\Support\Str;

class LocationMatchingService
{
    /**
     * Teslimat adresinden lokasyon eşleştirmesi yap.
     * 
     * @return Location|null Eşleşen lokasyon veya null
     */
    public function matchLocation(string $deliveryAddress): ?Location
    {
        // Adresi normalize et
        $normalizedAddress = $this->normalizeAddress($deliveryAddress);
        
        // Şehir, ilçe, mahalle bilgilerini çıkar
        $extracted = $this->extractLocationParts($normalizedAddress);
        
        if (empty($extracted['city'])) {
            return null;
        }

        // Şehir eşleştirmesi
        $city = $this->findCity($extracted['city']);
        if (!$city) {
            return null;
        }

        // İlçe eşleştirmesi (varsa)
        $district = null;
        if (!empty($extracted['district'])) {
            $district = $this->findDistrict($city->id, $extracted['district']);
        }

        // Mahalle eşleştirmesi (varsa)
        $neighborhood = null;
        if ($district && !empty($extracted['neighborhood'])) {
            $neighborhood = $this->findNeighborhood($district->id, $extracted['neighborhood']);
        }

        // Lokasyon oluştur veya mevcut olanı bul
        $location = Location::firstOrCreate(
            [
                'city_id' => $city->id,
                'district_id' => $district?->id,
                'neighborhood_id' => $neighborhood?->id,
                'address_line' => $normalizedAddress,
            ],
            [
                'country_id' => $city->country_id ?? 1, // Türkiye varsayılan
                'postal_code' => $extracted['postal_code'] ?? null,
            ]
        );

        return $location;
    }

    /**
     * Adresi normalize et.
     */
    protected function normalizeAddress(string $address): string
    {
        // Fazla boşlukları temizle
        $address = preg_replace('/\s+/', ' ', trim($address));
        
        // Türkçe karakterleri normalize et
        $address = Str::ascii($address);
        
        return $address;
    }

    /**
     * Adresten şehir, ilçe, mahalle bilgilerini çıkar.
     */
    protected function extractLocationParts(string $address): array
    {
        $parts = [
            'city' => null,
            'district' => null,
            'neighborhood' => null,
            'postal_code' => null,
        ];

        // Posta kodu çıkar (5 haneli sayı)
        if (preg_match('/\b(\d{5})\b/', $address, $matches)) {
            $parts['postal_code'] = $matches[1];
        }

        // Şehir isimlerini ara (Türkiye şehirleri)
        $cities = City::where('is_active', true)->pluck('name_tr', 'id')->toArray();
        
        foreach ($cities as $cityName) {
            if (stripos($address, $cityName) !== false) {
                $parts['city'] = $cityName;
                break;
            }
        }

        // İlçe isimlerini ara (şehir bulunduysa)
        if ($parts['city']) {
            $city = City::where('name_tr', $parts['city'])->first();
            if ($city) {
                $districts = District::where('city_id', $city->id)
                    ->where('is_active', true)
                    ->pluck('name_tr')
                    ->toArray();
                
                foreach ($districts as $districtName) {
                    if (stripos($address, $districtName) !== false) {
                        $parts['district'] = $districtName;
                        break;
                    }
                }
            }
        }

        return $parts;
    }

    /**
     * Şehir bul.
     */
    protected function findCity(string $cityName): ?City
    {
        return City::where('is_active', true)
            ->where(function($query) use ($cityName) {
                $query->where('name_tr', 'like', "%{$cityName}%")
                    ->orWhere('name_en', 'like', "%{$cityName}%");
            })
            ->first();
    }

    /**
     * İlçe bul.
     */
    protected function findDistrict(int $cityId, string $districtName): ?District
    {
        return District::where('city_id', $cityId)
            ->where('is_active', true)
            ->where(function($query) use ($districtName) {
                $query->where('name_tr', 'like', "%{$districtName}%")
                    ->orWhere('name_en', 'like', "%{$districtName}%");
            })
            ->first();
    }

    /**
     * Mahalle bul.
     */
    protected function findNeighborhood(int $districtId, string $neighborhoodName): ?Neighborhood
    {
        return Neighborhood::where('district_id', $districtId)
            ->where('is_active', true)
            ->where(function($query) use ($neighborhoodName) {
                $query->where('name_tr', 'like', "%{$neighborhoodName}%")
                    ->orWhere('name_en', 'like', "%{$neighborhoodName}%");
            })
            ->first();
    }
}
