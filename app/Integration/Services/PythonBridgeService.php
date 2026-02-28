<?php

namespace App\Integration\Services;

use App\AI\Services\AIFleetService;
use App\Integration\Jobs\SendToPythonJob;
use App\Models\Customer;
use App\Models\FuelPrice;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\Vehicle;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PythonBridgeService
{
    /**
     * Python ara katmana veri gönder.
     * config('python_bridge.enabled') false ise HTTP atılmaz (fail-safe).
     */
    public function sendToPython(array $data, string $action = 'process'): array
    {
        if (! config('python_bridge.enabled', false)) {
            Log::debug('Python bridge disabled, skipping send.', ['action' => $action]);

            return ['success' => true, 'response' => ['skipped' => true]];
        }

        try {
            $endpoint = config('python_bridge.endpoint', config('services.python.endpoint', 'http://localhost:8001/api/process'));
            $timeout = config('python_bridge.timeout', 30);

            $response = Http::timeout($timeout)->post($endpoint, [
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

    /**
     * Finans risk özeti payload'ı oluşturur (geciken ödemeler, toplam bakiye).
     *
     * @return array{source: string, company_id: int, period: array{start: string, end: string}, overdue_payments: array<int, array{id: int, customer_id: int, days_overdue: int, amount: float}>, total_outstanding: float, collection_rate: float}
     */
    public function buildFinanceRiskPayload(int $companyId, int $days = 30): array
    {
        $start = now()->subDays($days);
        $end = now();

        $customerIds = Customer::where('company_id', $companyId)->pluck('id')->all();

        $overdueQuery = Payment::query()
            ->where('related_type', Customer::class)
            ->whereIn('related_id', $customerIds)
            ->where('status', Payment::STATUS_PENDING)
            ->where('due_date', '<', now());

        $overduePayments = $overdueQuery->get();

        $overdueList = $overduePayments->map(fn (Payment $p) => [
            'id' => $p->id,
            'customer_id' => $p->related_id,
            'days_overdue' => (int) now()->diffInDays($p->due_date, false),
            'amount' => round((float) $p->amount, 2),
        ])->values()->all();

        $totalOutstanding = round((float) $overduePayments->sum('amount'), 2);

        $paidInPeriod = (float) Payment::query()
            ->where('related_type', Customer::class)
            ->whereIn('related_id', $customerIds)
            ->where('status', Payment::STATUS_PAID)
            ->whereBetween('paid_date', [$start, $end])
            ->sum('amount');

        $invoicedInPeriod = 0.0;
        if (Schema::hasTable('orders')) {
            $invoicedInPeriod = (float) DB::table('orders')
                ->where('company_id', $companyId)
                ->where('status', 'invoiced')
                ->whereBetween('created_at', [$start, $end])
                ->sum('freight_price');
        }

        $collectionRate = $invoicedInPeriod > 0 ? round(($paidInPeriod / $invoicedInPeriod) * 100, 2) : 0.0;

        return [
            'source' => 'finance_risk',
            'company_id' => $companyId,
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            'overdue_payments' => $overdueList,
            'total_outstanding' => $totalOutstanding,
            'collection_rate' => $collectionRate,
        ];
    }

    /**
     * Filo bakım özeti payload'ı oluşturur (AIFleetService ile).
     *
     * @return array{source: string, company_id: int, vehicles: array<int, array{id: int, plate: string, maintenance_score: float, last_inspection_days: int, status: string}>}
     */
    public function buildFleetMaintenancePayload(int $companyId): array
    {
        $fleetService = app(AIFleetService::class);

        $vehicles = Vehicle::query()
            ->whereHas('branch', fn ($q) => $q->where('company_id', $companyId))
            ->where('status', 1)
            ->get();

        $vehiclesData = [];
        foreach ($vehicles as $vehicle) {
            $prediction = $fleetService->predictMaintenanceNeeds($vehicle);
            $vehiclesData[] = [
                'id' => $vehicle->id,
                'plate' => $vehicle->plate,
                'maintenance_score' => $prediction['maintenance_score'],
                'last_inspection_days' => $prediction['last_inspection_days'],
                'status' => $prediction['status'],
            ];
        }

        return [
            'source' => 'fleet_maintenance',
            'company_id' => $companyId,
            'vehicles' => $vehiclesData,
        ];
    }

    /**
     * Finans risk özetini Python'a kuyruk üzerinden gönderir.
     */
    public function pushFinanceRiskToPython(int $companyId, int $days = 30): void
    {
        $payload = $this->buildFinanceRiskPayload($companyId, $days);
        $this->sendToPythonAsync([
            'source' => $payload['source'],
            'payload' => $payload,
        ], 'finance_risk');
    }

    /**
     * Filo bakım özetini Python'a kuyruk üzerinden gönderir.
     */
    public function pushFleetMaintenanceToPython(int $companyId): void
    {
        $payload = $this->buildFleetMaintenancePayload($companyId);
        $this->sendToPythonAsync([
            'source' => $payload['source'],
            'payload' => $payload,
        ], 'fleet_maintenance');
    }
}
