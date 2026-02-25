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
    Http::fake([
        '*' => Http::response(['success' => true], 200),
    ]);

    $service = app(PythonBridgeService::class);
    $result = $service->sendToPython(['key' => 'value'], 'process');

    expect($result['success'])->toBeTrue();
    expect($result)->toHaveKey('response');
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
