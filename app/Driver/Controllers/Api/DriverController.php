<?php

namespace App\Driver\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Log;

class DriverController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/driver/shipments",
     *     summary="Şoföre atanmış sevkiyatları listele",
     *     description="Giriş yapmış şoförün atanmış olduğu sevkiyatları getirir",
     *     operationId="getDriverShipments",
     *     tags={"Driver"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Sevkiyat durumu filtresi",
     *         required=false,
     *
     *         @OA\Schema(
     *             type="string",
     *             enum={"pending", "assigned", "loaded", "in_transit", "delivered", "cancelled"}
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *
     *                 @OA\Items(
     *
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="order_number", type="string", example="ORD-12345678"),
     *                     @OA\Property(property="customer_name", type="string", example="ABC Lojistik A.Ş."),
     *                     @OA\Property(property="pickup_address", type="string", example="İstanbul, Türkiye"),
     *                     @OA\Property(property="delivery_address", type="string", example="Ankara, Türkiye"),
     *                     @OA\Property(property="status", type="string", example="in_transit"),
     *                     @OA\Property(property="vehicle_plate", type="string", example="34 ABC 123"),
     *                     @OA\Property(property="pickup_date", type="string", format="date-time", example="2026-02-20T10:30:00+03:00"),
     *                     @OA\Property(property="delivery_date", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Personel kaydı bulunamadı",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Personel kaydı bulunamadı.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim"
     *     )
     * )
     */
    public function shipments(Request $request): JsonResponse
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Personel kaydı bulunamadı.',
            ], 404);
        }

        $query = Shipment::where('driver_id', $employee->id)
            ->with(['order.customer', 'vehicle']);

        // Durum filtresi
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $shipments = $query->latest()->get()->map(function ($shipment) {
            return [
                'id' => $shipment->id,
                'order_number' => $shipment->order->order_number ?? null,
                'customer_name' => $shipment->order->customer->name ?? null,
                'pickup_address' => $shipment->order->pickup_address ?? null,
                'delivery_address' => $shipment->order->delivery_address ?? null,
                'status' => $shipment->status,
                'vehicle_plate' => $shipment->vehicle->plate ?? null,
                'pickup_date' => $shipment->pickup_date?->toIso8601String(),
                'delivery_date' => $shipment->delivery_date?->toIso8601String(),
                'created_at' => $shipment->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $shipments,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/driver/shipments/{shipment}/status",
     *     summary="Sevkiyat durumunu güncelle",
     *     description="Şoförün atandığı sevkiyatın durumunu günceller",
     *     operationId="updateDriverShipmentStatus",
     *     tags={"Driver"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="shipment",
     *         in="path",
     *         description="Sevkiyat ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"status"},
     *
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"assigned", "loaded", "in_transit", "delivered"},
     *                 example="in_transit"
     *             ),
     *             @OA\Property(
     *                 property="notes",
     *                 type="string",
     *                 maxLength=1000,
     *                 example="Yükleme tamamlandı, yola çıkıldı"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sevkiyat durumu güncellendi."),
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="in_transit")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Yetkisiz erişim",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bu sevkiyata erişim yetkiniz yok.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Doğrulama hatası"
     *     )
     * )
     */
    public function updateShipmentStatus(Request $request, Shipment $shipment): JsonResponse
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (! $employee || $shipment->driver_id !== $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu sevkiyata erişim yetkiniz yok.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:assigned,loaded,in_transit,delivered',
            'notes' => 'nullable|string|max:1000',
        ]);

        $shipment->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $shipment->notes,
        ]);

        // Duruma göre tarih güncelle
        if ($validated['status'] === 'loaded' && ! $shipment->pickup_date) {
            $shipment->update(['pickup_date' => now()]);
        }

        if ($validated['status'] === 'delivered') {
            $shipment->update(['delivery_date' => now()]);
            $shipment->order->update(['delivered_at' => now(), 'status' => 'delivered']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sevkiyat durumu güncellendi.',
            'data' => [
                'id' => $shipment->id,
                'status' => $shipment->status,
            ],
        ]);
    }

    /**
     * POD (Teslimat kanıtı) yükle.
     */
    public function uploadPod(Request $request, Shipment $shipment): JsonResponse
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (! $employee || $shipment->driver_id !== $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu sevkiyata erişim yetkiniz yok.',
            ], 403);
        }

        $validated = $request->validate([
            'pod_file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120', // 5MB
            'notes' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('pod_file');
        $path = $file->store('pods', 'public');

        // Document modeline kaydet
        $document = $shipment->order->documents()->create([
            'category' => 'pod',
            'name' => 'POD - '.$shipment->order->order_number,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => $user->id,
        ]);

        // Sevkiyat notlarını güncelle
        if ($validated['notes'] ?? null) {
            $shipment->update(['notes' => $validated['notes']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'POD başarıyla yüklendi.',
            'data' => [
                'document_id' => $document->id,
                'file_url' => Storage::disk('public')->url($path),
            ],
        ]);
    }

    /**
     * GPS konum kaydet.
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'shipment_id' => 'nullable|exists:shipments,id',
        ]);

        $user = Auth::user();
        $employee = $user->employee;

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Personel kaydı bulunamadı.',
            ], 404);
        }

        // Şimdilik sadece log olarak kaydediyoruz
        // İleride ayrı bir driver_locations tablosu oluşturulabilir
        Log::info('Driver location update', [
            'driver_id' => $employee->id,
            'shipment_id' => $validated['shipment_id'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'timestamp' => now()->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Konum başarıyla kaydedildi.',
        ]);
    }
}
