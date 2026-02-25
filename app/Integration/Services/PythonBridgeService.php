<?php

namespace App\Integration\Services;

use App\Integration\Jobs\SendToPythonJob;
use App\Models\FuelPrice;
use App\Models\Shipment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PythonBridgeService
{
    /**
     * Python ara katmana veri gönder.
     *
     * Python SDK kısıtları için ara katman kullanılıyorsa bu servis kullanılır.
     */
    public function sendToPython(array $data, string $action = 'process'): array
    {
        try {
            $pythonEndpoint = config('services.python.endpoint', 'http://localhost:8001/api/process');

            $response = Http::timeout(30)->post($pythonEndpoint, [
                'action' => $action,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ]);

            if (! $response->successful()) {
                throw new Exception("Python bridge hatası: {$response->body()}");
            }

            return [
                'success' => true,
                'response' => $response->json(),
            ];
        } catch (Exception $e) {
            Log::error("Python bridge hatası: {$e->getMessage()}", [
                'action' => $action,
                'data' => $data,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Queue üzerinden Python'a gönder (Queue-first mimari).
     */
    public function sendToPythonAsync(array $data, string $action = 'process'): void
    {
        dispatch(new SendToPythonJob($data, $action));
    }

    /**
     * Teslimat/analitik verilerini Python pipeline'a gönderir.
     *
     * @param  array<string, mixed>  $payload  { batch_id, rows_count, summary }
     */
    public function pushDeliveryDataToPipeline(array $payload): void
    {
        $this->sendToPythonAsync([
            'source' => 'delivery_import',
            'payload' => $payload,
        ], 'analytics');
    }

    /**
     * Sipariş verilerini Python pipeline'a gönderir (ML/optimizasyon için).
     *
     * @param  array<string, mixed>  $ordersData  Order verileri
     */
    public function pushOrderDataToPipeline(array $ordersData): void
    {
        $this->sendToPythonAsync([
            'source' => 'orders',
            'payload' => $ordersData,
        ], 'optimization');
    }

    /**
     * Haftalık yakıt fiyatı özeti + sevkiyat sayıları payload'ı oluşturur (POC genişletme).
     *
     * @return array{source: string, period_days: int, period: array{start: string, end: string}, fuel: array{avg_price: float, min_price: float|null, max_price: float|null, record_count: int}, shipments: array{total: int, by_status: array<string, int>}}
     */
    public function buildFuelAndShipmentsPayload(int $days = 7): array
    {
        $start = now()->subDays($days);
        $end = now();

        $fuelPrices = FuelPrice::query()
            ->whereBetween('price_date', [$start, $end])
            ->get();

        $avgPrice = $fuelPrices->isEmpty() ? 0.0 : (float) $fuelPrices->avg('price');
        $minPrice = $fuelPrices->isEmpty() ? null : (float) $fuelPrices->min('price');
        $maxPrice = $fuelPrices->isEmpty() ? null : (float) $fuelPrices->max('price');

        $shipments = Shipment::query()
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $byStatus = $shipments->groupBy('status')->map(fn ($g) => $g->count())->all();

        return [
            'source' => 'fuel_shipments',
            'period_days' => $days,
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            'fuel' => [
                'avg_price' => round($avgPrice, 4),
                'min_price' => $minPrice !== null ? round($minPrice, 4) : null,
                'max_price' => $maxPrice !== null ? round($maxPrice, 4) : null,
                'record_count' => $fuelPrices->count(),
            ],
            'shipments' => [
                'total' => $shipments->count(),
                'by_status' => $byStatus,
            ],
        ];
    }

    /**
     * Yakıt + sevkiyat özetini Python'a kuyruk üzerinden gönderir.
     */
    public function pushFuelAndShipmentsToPython(int $days = 7): void
    {
        $payload = $this->buildFuelAndShipmentsPayload($days);
        $this->sendToPythonAsync([
            'source' => $payload['source'],
            'payload' => $payload,
        ], 'fuel_shipments');
    }
}
