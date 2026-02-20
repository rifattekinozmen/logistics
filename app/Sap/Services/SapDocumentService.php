<?php

namespace App\Sap\Services;

use App\DocumentFlow\Models\DocumentFlow;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Sap\Models\SapDocument;
use App\Sap\Models\SapSyncLog;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SapDocumentService
{
    /**
     * Bir sipariş için SAP satış siparişi kaydı oluştur (TA).
     */
    public function registerOrder(Order $order): SapDocument
    {
        return SapDocument::firstOrCreate(
            [
                'local_model_type' => Order::class,
                'local_model_id' => $order->id,
                'sap_doc_type' => 'TA',
            ],
            [
                'company_id' => $order->company_id,
                'sync_status' => 'pending',
            ]
        );
    }

    /**
     * Bir sevkiyat için SAP teslimat kaydı oluştur (LF).
     */
    public function registerShipment(Shipment $shipment): SapDocument
    {
        return SapDocument::firstOrCreate(
            [
                'local_model_type' => Shipment::class,
                'local_model_id' => $shipment->id,
                'sap_doc_type' => 'LF',
            ],
            [
                'company_id' => $shipment->order->company_id ?? 1,
                'sync_status' => 'pending',
            ]
        );
    }

    /**
     * SAP dokümanını senkronize edildi olarak işaretle.
     */
    public function markSynced(SapDocument $document, string $sapDocNumber, ?string $sapDocYear = null): SapDocument
    {
        $document->update([
            'sap_doc_number' => $sapDocNumber,
            'sap_doc_year' => $sapDocYear ?? date('Y'),
            'sync_status' => 'synced',
            'last_synced_at' => Carbon::now(),
            'sync_error' => null,
        ]);

        $this->logSync($document, 'sync', 'outbound', 200, 'success');

        return $document->fresh();
    }

    /**
     * SAP dokümanını hatalı olarak işaretle.
     */
    public function markFailed(SapDocument $document, string $errorMessage, int $httpStatus = 0): SapDocument
    {
        $document->update([
            'sync_status' => 'error',
            'sync_error' => $errorMessage,
        ]);

        $this->logSync($document, 'sync', 'outbound', $httpStatus, 'error', $errorMessage);

        return $document->fresh();
    }

    /**
     * Sync log kaydı oluştur.
     */
    public function logSync(
        SapDocument $document,
        string $operation,
        string $direction = 'outbound',
        int $httpStatus = 200,
        string $result = 'success',
        ?string $errorMessage = null,
        ?int $durationMs = null,
    ): SapSyncLog {
        return SapSyncLog::create([
            'company_id' => $document->company_id,
            'sap_document_id' => $document->id,
            'operation' => $operation,
            'direction' => $direction,
            'http_status' => $httpStatus,
            'result' => $result,
            'error_message' => $errorMessage,
            'duration_ms' => $durationMs,
        ]);
    }

    /**
     * Bekleyen SAP dokümanlarını getir.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPending(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = SapDocument::query()
            ->with(['company'])
            ->where('sync_status', 'pending');

        if (isset($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        if (isset($filters['sap_doc_type'])) {
            $query->where('sap_doc_type', $filters['sap_doc_type']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Tüm SAP dokümanlarını getir.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginated(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = SapDocument::query()->with(['company']);

        if (isset($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        if (isset($filters['sync_status'])) {
            $query->where('sync_status', $filters['sync_status']);
        }

        if (isset($filters['sap_doc_type'])) {
            $query->where('sap_doc_type', $filters['sap_doc_type']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a sales order in SAP from a local order.
     *
     * @param  Order  $order  Local order model
     * @return SapDocument Created SAP document record
     *
     * @throws Exception On sync failure
     */
    public function createSalesOrder(Order $order): SapDocument
    {
        $sapDoc = $this->registerOrder($order);

        if ($sapDoc->isSynced()) {
            return $sapDoc;
        }

        $payload = $this->buildSalesOrderPayload($order);
        $sapDoc->update(['sap_payload' => $payload]);

        if (! config('sap.sync.enabled')) {
            $sapDoc->update(['sync_status' => 'skipped']);

            return $sapDoc;
        }

        $startTime = microtime(true);

        try {
            $response = $this->getHttpClient()
                ->post(
                    config('sap.odata_url').config('sap.service_paths.sales_order'),
                    $payload
                );

            $duration = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $responseData = $response->json();
                $sapDocNumber = $responseData['d']['SalesOrder'] ?? null;

                $sapDoc->update([
                    'sap_doc_number' => $sapDocNumber,
                    'sap_status' => 'created',
                    'sync_status' => 'synced',
                    'last_synced_at' => now(),
                    'sap_response' => $responseData,
                    'sync_error' => null,
                ]);

                $this->logSync($sapDoc, 'create_sales_order', 'outbound', $response->status(), 'success', null, $duration);

                return $sapDoc;
            }

            throw new Exception('SAP API returned non-successful status: '.$response->status());
        } catch (Exception $e) {
            $duration = (int) ((microtime(true) - $startTime) * 1000);
            $this->markFailed($sapDoc, $e->getMessage(), 0);

            throw $e;
        }
    }

    /**
     * Sync a shipment (delivery) to SAP.
     *
     * @param  Shipment  $shipment  Local shipment model
     * @return SapDocument Created/Updated SAP document record
     *
     * @throws Exception On sync failure
     */
    public function syncDelivery(Shipment $shipment): SapDocument
    {
        $sapDoc = $this->registerShipment($shipment);

        if ($sapDoc->isSynced()) {
            return $sapDoc;
        }

        $payload = $this->buildDeliveryPayload($shipment);
        $sapDoc->update(['sap_payload' => $payload]);

        if (! config('sap.sync.enabled')) {
            $sapDoc->update(['sync_status' => 'skipped']);

            return $sapDoc;
        }

        $startTime = microtime(true);

        try {
            $response = $this->getHttpClient()
                ->post(
                    config('sap.odata_url').config('sap.service_paths.delivery'),
                    $payload
                );

            $duration = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $responseData = $response->json();
                $sapDocNumber = $responseData['d']['DeliveryDocument'] ?? null;

                $sapDoc->update([
                    'sap_doc_number' => $sapDocNumber,
                    'sap_status' => 'created',
                    'sync_status' => 'synced',
                    'last_synced_at' => now(),
                    'sap_response' => $responseData,
                    'sync_error' => null,
                ]);

                $this->logSync($sapDoc, 'sync_delivery', 'outbound', $response->status(), 'success', null, $duration);

                return $sapDoc;
            }

            throw new Exception('SAP API returned non-successful status: '.$response->status());
        } catch (Exception $e) {
            $duration = (int) ((microtime(true) - $startTime) * 1000);
            $this->markFailed($sapDoc, $e->getMessage(), 0);

            throw $e;
        }
    }

    /**
     * Sync an invoice (billing document) to SAP.
     *
     * @param  Payment  $payment  Local payment/invoice model
     * @return SapDocument Created/Updated SAP document record
     *
     * @throws Exception On sync failure
     */
    public function syncInvoice(Payment $payment): SapDocument
    {
        $sapDoc = SapDocument::firstOrCreate(
            [
                'local_model_type' => Payment::class,
                'local_model_id' => $payment->id,
                'sap_doc_type' => 'FV',
            ],
            [
                'company_id' => 1,
                'sync_status' => 'pending',
            ]
        );

        if ($sapDoc->isSynced()) {
            return $sapDoc;
        }

        $payload = $this->buildInvoicePayload($payment);
        $sapDoc->update(['sap_payload' => $payload]);

        if (! config('sap.sync.enabled')) {
            $sapDoc->update(['sync_status' => 'skipped']);

            return $sapDoc;
        }

        $startTime = microtime(true);

        try {
            $response = $this->getHttpClient()
                ->post(
                    config('sap.odata_url').config('sap.service_paths.invoice'),
                    $payload
                );

            $duration = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $responseData = $response->json();
                $sapDocNumber = $responseData['d']['BillingDocument'] ?? null;

                $sapDoc->update([
                    'sap_doc_number' => $sapDocNumber,
                    'sap_status' => 'created',
                    'sync_status' => 'synced',
                    'last_synced_at' => now(),
                    'sap_response' => $responseData,
                    'sync_error' => null,
                ]);

                $this->logSync($sapDoc, 'sync_invoice', 'outbound', $response->status(), 'success', null, $duration);

                return $sapDoc;
            }

            throw new Exception('SAP API returned non-successful status: '.$response->status());
        } catch (Exception $e) {
            $duration = (int) ((microtime(true) - $startTime) * 1000);
            $this->markFailed($sapDoc, $e->getMessage(), 0);

            throw $e;
        }
    }

    /**
     * Get document flow for a SAP document number.
     * Retrieves the chain: Order → Delivery → Invoice.
     *
     * @param  string  $sapDocNumber  SAP document number
     * @return DocumentFlow|null Document flow record or null if not found
     */
    public function getDocumentFlow(string $sapDocNumber): ?DocumentFlow
    {
        $sapDoc = SapDocument::where('sap_doc_number', $sapDocNumber)->first();

        if (! $sapDoc) {
            return null;
        }

        return DocumentFlow::firstOrCreate(
            [
                'source_type' => $sapDoc->local_model_type,
                'source_id' => $sapDoc->local_model_id,
            ],
            [
                'document_type' => $this->mapSapDocTypeToLocal($sapDoc->sap_doc_type),
                'document_number' => $sapDoc->sap_doc_number,
                'status' => $sapDoc->sap_status,
            ]
        );
    }

    /**
     * Build sales order payload for SAP OData API.
     *
     * @param  Order  $order  Local order
     * @return array<string, mixed> SAP API payload
     */
    protected function buildSalesOrderPayload(Order $order): array
    {
        $customer = $order->customer;

        return [
            'SalesOrderType' => 'OR',
            'SalesOrganization' => '1000',
            'DistributionChannel' => '10',
            'Division' => '00',
            'SoldToParty' => $customer->sap_customer_id ?? $customer->id,
            'SalesOrderDate' => $order->created_at->format('Y-m-d'),
            'RequestedDeliveryDate' => $order->planned_delivery_date?->format('Y-m-d') ?? now()->addDays(7)->format('Y-m-d'),
            'PurchaseOrderByCustomer' => $order->order_number,
            'to_Item' => [
                'results' => [
                    [
                        'SalesOrderItem' => '10',
                        'Material' => 'FREIGHT',
                        'RequestedQuantity' => (string) ($order->total_weight ?? 1),
                        'RequestedQuantityUnit' => 'KG',
                        'NetAmount' => (string) ($order->freight_price ?? 0),
                        'TransactionCurrency' => 'TRY',
                    ],
                ],
            ],
        ];
    }

    /**
     * Build delivery payload for SAP OData API.
     *
     * @param  Shipment  $shipment  Local shipment
     * @return array<string, mixed> SAP API payload
     */
    protected function buildDeliveryPayload(Shipment $shipment): array
    {
        $order = $shipment->order;
        $orderSapDoc = SapDocument::where('local_model_type', Order::class)
            ->where('local_model_id', $order->id)
            ->where('sync_status', 'synced')
            ->first();

        return [
            'ReferenceSDDocument' => $orderSapDoc?->sap_doc_number,
            'ActualGoodsMovementDate' => $shipment->pickup_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'DeliveryDocumentType' => 'LF',
            'ShippingPoint' => '1000',
        ];
    }

    /**
     * Build invoice payload for SAP OData API.
     *
     * @param  Payment  $payment  Local payment
     * @return array<string, mixed> SAP API payload
     */
    protected function buildInvoicePayload(Payment $payment): array
    {
        return [
            'BillingDocumentType' => 'F2',
            'BillingDocumentDate' => $payment->due_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'TotalNetAmount' => (string) $payment->amount,
            'TransactionCurrency' => 'TRY',
        ];
    }

    /**
     * Get configured HTTP client for SAP OData.
     *
     * @return PendingRequest HTTP client instance
     */
    protected function getHttpClient(): PendingRequest
    {
        return Http::timeout(config('sap.timeout', 30))
            ->withBasicAuth(config('sap.username'), config('sap.password'))
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->when(! config('sap.verify_ssl'), fn (PendingRequest $http) => $http->withoutVerifying());
    }

    /**
     * Map SAP document type to local document type.
     *
     * @param  string  $sapDocType  SAP document type code
     * @return string Local document type
     */
    protected function mapSapDocTypeToLocal(string $sapDocType): string
    {
        return match ($sapDocType) {
            'TA' => 'order',
            'J', 'LF' => 'delivery',
            'F2', 'FV' => 'invoice',
            default => 'unknown',
        };
    }
}
