<?php

namespace App\Sap\Services;

use App\Jobs\ProcessSapEventJob;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SapEventMeshService
{
    /**
     * Subscribe to SAP Event Mesh topic.
     */
    public function subscribe(string $topic): bool
    {
        if (! config('sap.sync.enabled')) {
            Log::info('SAP sync disabled');

            return false;
        }

        $url = config('sap.event_mesh_url', 'https://event-mesh.cfapps.sap.hana.ondemand.com').'/messagingrest/v1/subscriptions';

        try {
            $response = Http::timeout(config('sap.timeout'))
                ->withToken(config('sap.event_mesh_token'))
                ->post($url, [
                    'name' => "logistics-{$topic}",
                    'topicPattern' => $topic,
                    'qos' => 1,
                    'webhookUrl' => route('api.sap.webhook'),
                ]);

            if ($response->successful()) {
                Log::info("Subscribed to SAP Event Mesh topic: {$topic}");

                return true;
            }

            Log::warning("Failed to subscribe to SAP Event Mesh topic: {$topic}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error("SAP Event Mesh subscription error: {$topic}", [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handle incoming SAP event webhook.
     */
    public function handleWebhook(Request $request): array
    {
        $eventType = $request->header('X-SAP-EventType');
        $eventId = $request->header('X-SAP-EventId');
        $payload = $request->all();

        Log::info('SAP Event received', [
            'event_type' => $eventType,
            'event_id' => $eventId,
        ]);

        if (! $eventType || ! $eventId) {
            Log::warning('Invalid SAP event: missing headers');

            return [
                'success' => false,
                'message' => 'Invalid event headers',
            ];
        }

        ProcessSapEventJob::dispatch($eventType, $eventId, $payload);

        return [
            'success' => true,
            'message' => 'Event queued for processing',
            'event_id' => $eventId,
        ];
    }

    /**
     * Publish event to SAP Event Mesh.
     */
    public function publishEvent(string $topic, array $payload): bool
    {
        if (! config('sap.sync.enabled')) {
            Log::info('SAP sync disabled');

            return false;
        }

        $url = config('sap.event_mesh_url', 'https://event-mesh.cfapps.sap.hana.ondemand.com').'/messagingrest/v1/topics/'.$topic.'/messages';

        try {
            $response = Http::timeout(config('sap.timeout'))
                ->withToken(config('sap.event_mesh_token'))
                ->post($url, [
                    'data' => $payload,
                ]);

            if ($response->successful()) {
                Log::info("Published event to SAP Event Mesh: {$topic}");

                return true;
            }

            Log::warning("Failed to publish event to SAP Event Mesh: {$topic}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error("SAP Event Mesh publish error: {$topic}", [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
