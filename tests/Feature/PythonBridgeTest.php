<?php

use App\Analytics\Services\AnalyticsDashboardService;
use App\Integration\Jobs\SendToPythonJob;
use App\Integration\Services\PythonBridgeService;
use App\Models\Company;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

it('dispatches job when sending to python async', function () {
    Queue::fake();

    $service = app(PythonBridgeService::class);
    $service->sendToPythonAsync(['test' => 'data'], 'process');

    Queue::assertPushed(SendToPythonJob::class);
});

it('dispatches job when pushing delivery data to pipeline', function () {
    Queue::fake();

    $service = app(PythonBridgeService::class);
    $service->pushDeliveryDataToPipeline([
        'batch_id' => 1,
        'rows_count' => 100,
        'summary' => ['total_weight' => 5000],
    ]);

    Queue::assertPushed(SendToPythonJob::class);
});

it('dispatches job when pushing order data to pipeline', function () {
    Queue::fake();

    $service = app(PythonBridgeService::class);
    $service->pushOrderDataToPipeline([
        'orders' => [['id' => 1, 'status' => 'pending']],
    ]);

    Queue::assertPushed(SendToPythonJob::class);
});

it('sends data to python endpoint when called synchronously', function () {
    config(['python_bridge.enabled' => true]);
    Http::fake([
        '*' => Http::response(['success' => true], 200),
    ]);

    $service = app(PythonBridgeService::class);
    $result = $service->sendToPython(['key' => 'value'], 'process');

    expect($result['success'])->toBeTrue();
    expect($result)->toHaveKey('response');
});

it('returns success without HTTP when python bridge is disabled', function () {
    config(['python_bridge.enabled' => false]);

    $service = app(PythonBridgeService::class);
    $result = $service->sendToPython(['key' => 'value'], 'process');

    expect($result['success'])->toBeTrue();
    expect($result['response'])->toHaveKey('skipped');
    expect($result['response']['skipped'])->toBeTrue();
});

it('pushes analytics snapshot via artisan command', function (): void {
    Queue::fake();

    /** @var Company $company */
    $company = Company::factory()->create([
        'is_active' => 1,
    ]);

    // Analytics service will run queries, but verinin boş olması bu test için sorun değil.
    app(AnalyticsDashboardService::class);

    $this->artisan('analytics:push-python', ['companyId' => $company->id, '--days' => 7])
        ->assertExitCode(0);

    Queue::assertPushed(SendToPythonJob::class);
});

it('buildFuelAndShipmentsPayload returns expected structure', function () {
    $service = app(PythonBridgeService::class);
    $payload = $service->buildFuelAndShipmentsPayload(7);

    expect($payload)->toHaveKeys(['source', 'period_days', 'period', 'fuel', 'shipments']);
    expect($payload['source'])->toBe('fuel_shipments');
    expect($payload['period_days'])->toBe(7);
    expect($payload['period'])->toHaveKeys(['start', 'end']);
    expect($payload['fuel'])->toHaveKeys(['avg_price', 'min_price', 'max_price', 'record_count']);
    expect($payload['shipments'])->toHaveKeys(['total', 'by_status']);
    expect($payload['shipments']['by_status'])->toBeArray();
});

it('pushFuelAndShipmentsToPython dispatches SendToPythonJob', function () {
    Queue::fake();

    $service = app(PythonBridgeService::class);
    $service->pushFuelAndShipmentsToPython(7);

    Queue::assertPushed(SendToPythonJob::class);
});

it('python push-fuel-shipments command queues job', function () {
    Queue::fake();

    $this->artisan('python:push-fuel-shipments', ['--days' => 7])
        ->assertExitCode(0);

    Queue::assertPushed(SendToPythonJob::class);
});

it('buildFinanceRiskPayload returns expected structure', function () {
    $company = Company::factory()->create();
    $service = app(PythonBridgeService::class);
    $payload = $service->buildFinanceRiskPayload($company->id, 30);

    expect($payload)->toHaveKeys(['source', 'company_id', 'period', 'overdue_payments', 'total_outstanding', 'collection_rate']);
    expect($payload['source'])->toBe('finance_risk');
    expect($payload['company_id'])->toBe($company->id);
    expect($payload['period'])->toHaveKeys(['start', 'end']);
    expect($payload['overdue_payments'])->toBeArray();
    expect($payload['total_outstanding'])->toBeFloat();
    expect($payload['collection_rate'])->toBeFloat();
});

it('pushFinanceRiskToPython dispatches SendToPythonJob', function () {
    Queue::fake();
    $company = Company::factory()->create();

    app(PythonBridgeService::class)->pushFinanceRiskToPython($company->id, 30);

    Queue::assertPushed(SendToPythonJob::class);
});

it('buildFleetMaintenancePayload returns expected structure', function () {
    $company = Company::factory()->create();
    $service = app(PythonBridgeService::class);
    $payload = $service->buildFleetMaintenancePayload($company->id);

    expect($payload)->toHaveKeys(['source', 'company_id', 'vehicles']);
    expect($payload['source'])->toBe('fleet_maintenance');
    expect($payload['company_id'])->toBe($company->id);
    expect($payload['vehicles'])->toBeArray();
});

it('pushFleetMaintenanceToPython dispatches SendToPythonJob', function () {
    Queue::fake();
    $company = Company::factory()->create();

    app(PythonBridgeService::class)->pushFleetMaintenanceToPython($company->id);

    Queue::assertPushed(SendToPythonJob::class);
});
