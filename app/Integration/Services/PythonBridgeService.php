<?php

namespace App\Integration\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

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

            if (!$response->successful()) {
                throw new \Exception("Python bridge hatası: {$response->body()}");
            }

            return [
                'success' => true,
                'response' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error("Python bridge hatası: {$e->getMessage()}", [
                'action' => $action,
                'data' => $data,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Queue üzerinden Python'a gönder.
     */
    public function sendToPythonAsync(array $data, string $action = 'process'): void
    {
        Queue::push(\App\Integration\Jobs\SendToPythonJob::class, [
            'data' => $data,
            'action' => $action,
        ]);
    }
}
