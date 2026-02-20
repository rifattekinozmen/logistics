<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Sap\Models\SapDocument;
use App\Sap\Models\SapSyncLog;
use App\Sap\Services\SapDocumentService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Config::set('sap.sync.enabled', false);
});

it('can register order as SAP document', function () {
    $service = app(SapDocumentService::class);
    $order = Order::factory()->create();

    $sapDoc = $service->registerOrder($order);

    expect($sapDoc)->toBeInstanceOf(SapDocument::class);
    expect($sapDoc->local_model_type)->toBe(Order::class);
    expect($sapDoc->local_model_id)->toBe($order->id);
    expect($sapDoc->sap_doc_type)->toBe('TA');
    expect($sapDoc->sync_status)->toBe('pending');
});

it('can register shipment as SAP document', function () {
    $service = app(SapDocumentService::class);
    $shipment = Shipment::factory()->create();

    $sapDoc = $service->registerShipment($shipment);

    expect($sapDoc)->toBeInstanceOf(SapDocument::class);
    expect($sapDoc->sap_doc_type)->toBe('LF');
});

it('marks SAP document as synced', function () {
    $service = app(SapDocumentService::class);
    $order = Order::factory()->create();
    $sapDoc = $service->registerOrder($order);

    $synced = $service->markSynced($sapDoc, 'SAP-123456', '2026');

    expect($synced->sync_status)->toBe('synced');
    expect($synced->sap_doc_number)->toBe('SAP-123456');
    expect($synced->last_synced_at)->not->toBeNull();
});

it('marks SAP document as failed', function () {
    $service = app(SapDocumentService::class);
    $order = Order::factory()->create();
    $sapDoc = $service->registerOrder($order);

    $failed = $service->markFailed($sapDoc, 'Connection timeout', 500);

    expect($failed->sync_status)->toBe('error');
    expect($failed->sync_error)->toContain('Connection timeout');
});

it('logs sync operation', function () {
    $service = app(SapDocumentService::class);
    $order = Order::factory()->create();
    $sapDoc = $service->registerOrder($order);

    $log = $service->logSync($sapDoc, 'test_operation', 'outbound', 200, 'success', null, 150);

    expect($log)->toBeInstanceOf(SapSyncLog::class);
    expect($log->operation)->toBe('test_operation');
    expect($log->result)->toBe('success');
    expect($log->duration_ms)->toBe(150);
});

it('creates sales order in SAP when sync enabled', function () {
    Config::set('sap.sync.enabled', true);
    Config::set('sap.odata_url', 'https://mock-sap.test');
    Config::set('sap.service_paths.sales_order', '/sales-orders');

    Http::fake([
        'https://mock-sap.test/sales-orders' => Http::response([
            'd' => [
                'SalesOrder' => 'SAP-001',
                'SalesOrderType' => 'OR',
            ],
        ], 201),
    ]);

    $service = app(SapDocumentService::class);
    $order = Order::factory()->create();

    $sapDoc = $service->createSalesOrder($order);

    expect($sapDoc->sync_status)->toBe('synced');
    expect($sapDoc->sap_doc_number)->toBe('SAP-001');
    Http::assertSent(fn ($request) => $request->url() === 'https://mock-sap.test/sales-orders');
});

it('skips sync when SAP sync is disabled', function () {
    Config::set('sap.sync.enabled', false);

    $service = app(SapDocumentService::class);
    $order = Order::factory()->create();

    $sapDoc = $service->createSalesOrder($order);

    expect($sapDoc->sync_status)->toBe('skipped');
});

it('handles SAP API errors gracefully', function () {
    Config::set('sap.sync.enabled', true);
    Config::set('sap.odata_url', 'https://mock-sap.test');
    Config::set('sap.service_paths.sales_order', '/sales-orders');

    Http::fake([
        'https://mock-sap.test/sales-orders' => Http::response([], 500),
    ]);

    $service = app(SapDocumentService::class);
    $order = Order::factory()->create();

    expect(fn () => $service->createSalesOrder($order))
        ->toThrow(Exception::class);

    $sapDoc = SapDocument::where('local_model_type', Order::class)
        ->where('local_model_id', $order->id)
        ->first();

    expect($sapDoc->sync_status)->toBe('error');
});

it('can sync delivery to SAP', function () {
    Config::set('sap.sync.enabled', true);
    Config::set('sap.odata_url', 'https://mock-sap.test');
    Config::set('sap.service_paths.delivery', '/deliveries');

    Http::fake([
        'https://mock-sap.test/deliveries' => Http::response([
            'd' => [
                'DeliveryDocument' => 'DEL-001',
            ],
        ], 201),
    ]);

    $service = app(SapDocumentService::class);
    $shipment = Shipment::factory()->create();

    $sapDoc = $service->syncDelivery($shipment);

    expect($sapDoc->sync_status)->toBe('synced');
    expect($sapDoc->sap_doc_number)->toBe('DEL-001');
});

it('can sync invoice to SAP', function () {
    Config::set('sap.sync.enabled', true);
    Config::set('sap.odata_url', 'https://mock-sap.test');
    Config::set('sap.service_paths.invoice', '/invoices');

    Http::fake([
        'https://mock-sap.test/invoices' => Http::response([
            'd' => [
                'BillingDocument' => 'INV-001',
            ],
        ], 201),
    ]);

    $service = app(SapDocumentService::class);
    $payment = Payment::factory()->create();

    $sapDoc = $service->syncInvoice($payment);

    expect($sapDoc->sync_status)->toBe('synced');
    expect($sapDoc->sap_doc_number)->toBe('INV-001');
});

it('can retrieve document flow by SAP doc number', function () {
    $service = app(SapDocumentService::class);
    $order = Order::factory()->create();
    $sapDoc = $service->registerOrder($order);
    $sapDoc->update(['sap_doc_number' => 'SAP-999', 'sap_status' => 'created']);

    $flow = $service->getDocumentFlow('SAP-999');

    expect($flow)->toBeInstanceOf(\App\DocumentFlow\Models\DocumentFlow::class);
    expect($flow->document_number)->toBe('SAP-999');
});

it('returns null for non-existent SAP doc number', function () {
    $service = app(SapDocumentService::class);

    $flow = $service->getDocumentFlow('NON-EXISTENT');

    expect($flow)->toBeNull();
});

it('gets pending documents', function () {
    $order1 = Order::factory()->create();
    $order2 = Order::factory()->create();

    $service = app(SapDocumentService::class);
    $service->registerOrder($order1);
    $sapDoc2 = $service->registerOrder($order2);
    $service->markSynced($sapDoc2, 'SAP-123');

    $pending = $service->getPending();

    expect($pending->total())->toBe(1);
});

it('filters documents by company', function () {
    [$user, $company1] = createAdminUser();
    $company2 = \App\Models\Company::factory()->create();

    $order1 = Order::factory()->create(['company_id' => $company1->id]);
    $order2 = Order::factory()->create(['company_id' => $company2->id]);

    $service = app(SapDocumentService::class);
    $service->registerOrder($order1);
    $service->registerOrder($order2);

    $results = $service->getPaginated(['company_id' => $company1->id]);

    expect($results->total())->toBe(1);
});
