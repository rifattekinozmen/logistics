<?php

namespace App\Integration\Jobs;

use App\Integration\Services\PythonBridgeService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Python ara katmana veri gönderme job'u (Faz 3 production: config, retry, backoff).
 */
class SendToPythonJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $data,
        protected string $action = 'process'
    ) {
        $this->onQueue('default');
        $this->tries = config('python_bridge.max_retries', 3);
        $this->backoff = config('python_bridge.backoff', 60);
    }

    /**
     * Execute the job.
     */
    public function handle(PythonBridgeService $pythonService): void
    {
        try {
            $result = $pythonService->sendToPython($this->data, $this->action);

            Log::info("Python'a veri gönderildi", [
                'action' => $this->action,
                'success' => $result['success'] ?? false,
            ]);
        } catch (Exception $e) {
            Log::error("Python'a veri gönderim hatası: {$e->getMessage()}", [
                'action' => $this->action,
                'data' => $this->data,
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
