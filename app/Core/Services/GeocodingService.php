<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Http;

/**
 * Adres metnini enlem/boylam (latitude/longitude) koordinatlarına çevirir.
 * Nominatim (OpenStreetMap) veya Google Geocoding API kullanılabilir.
 *
 * @see https://nominatim.org/release-docs/develop/api/Search/
 * @see https://developers.google.com/maps/documentation/geocoding
 */
class GeocodingService
{
    protected function provider(): string
    {
        return config('services.geocoding.provider', 'nominatim');
    }

    protected function googleApiKey(): ?string
    {
        return config('services.geocoding.google_api_key');
    }

    protected function userAgent(): string
    {
        return config('services.geocoding.user_agent', config('app.name').'/1.0');
    }

    /**
     * Adres metninden enlem ve boylam döndürür. Sonuç bulunamazsa null.
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public function geocode(string $address): ?array
    {
        $address = trim($address);
        if ($address === '') {
            return null;
        }

        return match ($this->provider()) {
            'google' => $this->geocodeGoogle($address),
            'nominatim' => $this->geocodeNominatim($address),
            default => $this->geocodeNominatim($address),
        };
    }

    /**
     * OpenStreetMap Nominatim API (ücretsiz, rate limit: 1 req/s).
     */
    protected function geocodeNominatim(string $address): ?array
    {
        $url = config('services.geocoding.nominatim_url', 'https://nominatim.openstreetmap.org/search');
        $email = config('services.geocoding.nominatim_email', '');

        $response = Http::withHeaders([
            'User-Agent' => $this->userAgent(),
        ])
            ->timeout(5)
            ->get($url, [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 0,
                'email' => $email,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $body = $response->json();
        if (! is_array($body) || empty($body)) {
            return null;
        }

        $first = $body[0];
        $lat = isset($first['lat']) ? (float) $first['lat'] : null;
        $lon = isset($first['lon']) ? (float) $first['lon'] : null;

        if ($lat === null || $lon === null || $lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return null;
        }

        return [
            'latitude' => $lat,
            'longitude' => $lon,
        ];
    }

    /**
     * Google Geocoding API (API key gerekir, ücretli kotaya tabi).
     */
    protected function geocodeGoogle(string $address): ?array
    {
        if (! $this->googleApiKey()) {
            return null;
        }

        $response = Http::timeout(5)
            ->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'key' => $this->googleApiKey(),
            ]);

        if (! $response->successful()) {
            return null;
        }

        $body = $response->json();
        $status = $body['status'] ?? '';
        if ($status !== 'OK' || empty($body['results'])) {
            return null;
        }

        $location = $body['results'][0]['geometry']['location'] ?? null;
        if (! $location) {
            return null;
        }

        $lat = (float) ($location['lat'] ?? 0);
        $lon = (float) ($location['lng'] ?? 0);
        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return null;
        }

        return [
            'latitude' => $lat,
            'longitude' => $lon,
        ];
    }

    /**
     * Enlem/boylamdan adres metni döndürür (reverse geocoding). Bulunamazsa null.
     */
    public function reverseGeocode(float $latitude, float $longitude): ?string
    {
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return null;
        }

        return match ($this->provider()) {
            'google' => $this->reverseGeocodeGoogle($latitude, $longitude),
            'nominatim' => $this->reverseGeocodeNominatim($latitude, $longitude),
            default => $this->reverseGeocodeNominatim($latitude, $longitude),
        };
    }

    protected function reverseGeocodeNominatim(float $latitude, float $longitude): ?string
    {
        $url = config('services.geocoding.nominatim_reverse_url', 'https://nominatim.openstreetmap.org/reverse');
        $email = config('services.geocoding.nominatim_email', '');

        $response = Http::withHeaders([
            'User-Agent' => $this->userAgent(),
        ])
            ->timeout(5)
            ->get($url, [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'json',
                'addressdetails' => 0,
                'email' => $email,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $body = $response->json();
        if (! is_array($body) || empty($body['display_name'])) {
            return null;
        }

        return (string) $body['display_name'];
    }

    protected function reverseGeocodeGoogle(float $latitude, float $longitude): ?string
    {
        if (! $this->googleApiKey()) {
            return null;
        }

        $response = Http::timeout(5)
            ->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => "{$latitude},{$longitude}",
                'key' => $this->googleApiKey(),
            ]);

        if (! $response->successful()) {
            return null;
        }

        $body = $response->json();
        $status = $body['status'] ?? '';
        if ($status !== 'OK' || empty($body['results'][0]['formatted_address'])) {
            return null;
        }

        return (string) $body['results'][0]['formatted_address'];
    }
}
