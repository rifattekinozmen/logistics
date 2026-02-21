<?php

namespace App\Integration\Services;

use App\Integration\Jobs\SendToPythonJob;
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
}
