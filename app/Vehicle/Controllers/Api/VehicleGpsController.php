<?php

namespace App\Vehicle\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleGpsPosition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Faz 3: Real-time GPS için placeholder API.
 * Gerçek entegrasyon (cihaz/app) sonrası doldurulacak.
 */
class VehicleGpsController extends Controller
{
    /**
     * Son GPS konum kayıtlarını döndür (placeholder: tablo boşsa boş liste).
     */
    public function index(Request $request): JsonResponse
    {
        $positions = VehicleGpsPosition::query()
            ->with('vehicle:id,plate,branch_id')
            ->latest('recorded_at')
            ->limit($request->integer('limit', 50))
            ->get();

        return response()->json([
            'data' => $positions->map(fn ($p) => [
                'vehicle_id' => $p->vehicle_id,
                'plate' => $p->vehicle?->plate,
                'latitude' => (float) $p->latitude,
                'longitude' => (float) $p->longitude,
                'recorded_at' => $p->recorded_at->toIso8601String(),
                'source' => $p->source,
            ]),
        ]);
    }

    /**
     * Belirli bir aracın son GPS konumunu döndür (placeholder).
     */
    public function latest(Vehicle $vehicle): JsonResponse
    {
        $position = VehicleGpsPosition::where('vehicle_id', $vehicle->id)
            ->latest('recorded_at')
            ->first();

        if (! $position) {
            return response()->json([
                'data' => null,
                'message' => 'Bu araç için henüz GPS konumu kaydı yok.',
            ]);
        }

        return response()->json([
            'data' => [
                'vehicle_id' => $vehicle->id,
                'plate' => $vehicle->plate,
                'latitude' => (float) $position->latitude,
                'longitude' => (float) $position->longitude,
                'recorded_at' => $position->recorded_at->toIso8601String(),
                'source' => $position->source,
            ],
        ]);
    }

    /**
     * Araç konumu kaydet (cihaz veya sürücü uygulamasından gelecek - placeholder).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'recorded_at' => 'nullable|date',
            'source' => 'nullable|string|in:device,driver_app,manual',
        ]);

        $position = VehicleGpsPosition::create([
            'vehicle_id' => $validated['vehicle_id'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'recorded_at' => isset($validated['recorded_at']) ? $validated['recorded_at'] : now(),
            'source' => $validated['source'] ?? 'manual',
        ]);

        return response()->json(['data' => $position], 201);
    }
}
