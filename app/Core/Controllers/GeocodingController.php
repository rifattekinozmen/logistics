<?php

namespace App\Core\Controllers;

use App\Core\Services\GeocodingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeocodingController extends Controller
{
    /**
     * Enlem/boylamdan açık adres getirir (reverse geocoding). Giriş yapmış kullanıcılar kullanabilir.
     */
    public function reverse(Request $request, GeocodingService $geocodingService): JsonResponse
    {
        $lat = $request->input('lat') ?? $request->input('latitude');
        $lon = $request->input('lon') ?? $request->input('longitude');

        if ($lat === null || $lon === null || ! is_numeric($lat) || ! is_numeric($lon)) {
            return response()->json(['message' => 'Enlem ve boylam gerekli.'], 422);
        }

        $lat = (float) $lat;
        $lon = (float) $lon;

        $address = $geocodingService->reverseGeocode($lat, $lon);

        if ($address === null) {
            return response()->json(['message' => 'Bu koordinat için adres bulunamadı.'], 404);
        }

        return response()->json(['address' => $address]);
    }
}
