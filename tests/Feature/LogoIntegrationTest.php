<?php

use App\Integration\Services\LogoIntegrationService;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Config::set('logo.enabled', false);
});

it('can export invoice to LOGO when enabled', function () {
    Config::set('logo.enabled', true);
    Config::set('logo.api_url', 'https://mock-logo.test/api');

    Http::fake([
        'https://mock-logo.test/api/invoices' => Http::response([
            'invoice_id' => 'LOGO-INV-001',
            'status' => 'created',
        ], 201),
    ]);

    $service = app(LogoIntegrationService::class);
    $payment = Payment::factory()->create();

    $result = $service->exportInvoice($payment);

    expect($result)->toBeTrue();
    Http::assertSent(fn ($request) => $request->url() === 'https://mock-logo.test/api/invoices');
});

it('skips invoice export when LOGO integration is disabled', function () {
    Config::set('logo.enabled', false);

    $service = app(LogoIntegrationService::class);
    $payment = Payment::factory()->create();

    $result = $service->exportInvoice($payment);

    expect($result)->toBeFalse();
});

it('can sync customer to LOGO', function () {
    Config::set('logo.enabled', true);
    Config::set('logo.api_url', 'https://mock-logo.test/api');

    Http::fake([
        'https://mock-logo.test/api/customers' => Http::response([
            'customer_id' => 'LOGO-CUST-001',
            'status' => 'synced',
        ], 200),
    ]);

    $service = app(LogoIntegrationService::class);
    $customer = Customer::factory()->create();

    $result = $service->syncCustomer($customer);

    expect($result)->toBeTrue();
    Http::assertSent(fn ($request) => $request->url() === 'https://mock-logo.test/api/customers');
});

it('skips customer sync when LOGO integration is disabled', function () {
    Config::set('logo.enabled', false);

    $service = app(LogoIntegrationService::class);
    $customer = Customer::factory()->create();

    $result = $service->syncCustomer($customer);

    expect($result)->toBeFalse();
});

it('can get accounting data from LOGO', function () {
    Config::set('logo.enabled', true);
    Config::set('logo.api_url', 'https://mock-logo.test/api');

    Http::fake([
        'https://mock-logo.test/api/accounting*' => Http::response([
            'date' => '2026-02-01',
            'total_revenue' => 150000.00,
            'total_expenses' => 85000.00,
            'net_profit' => 65000.00,
        ], 200),
    ]);

    $service = app(LogoIntegrationService::class);
    $company = \App\Models\Company::factory()->create();

    $data = $service->getAccountingData($company, now());

    expect($data)->toBeArray();
    expect($data)->toHaveKey('total_revenue');
    expect($data['total_revenue'])->toBe(150000.00);
});

it('returns empty array when LOGO integration is disabled for accounting data', function () {
    Config::set('logo.enabled', false);

    $service = app(LogoIntegrationService::class);
    $company = \App\Models\Company::factory()->create();

    $data = $service->getAccountingData($company, now());

    expect($data)->toBe([]);
});

it('handles LOGO API errors gracefully', function () {
    Config::set('logo.enabled', true);
    Config::set('logo.api_url', 'https://mock-logo.test/api');

    Http::fake([
        'https://mock-logo.test/api/invoices' => Http::response([], 500),
    ]);

    $service = app(LogoIntegrationService::class);
    $payment = Payment::factory()->create();

    expect(fn () => $service->exportInvoice($payment))
        ->toThrow(Exception::class);
});

it('includes authentication token in requests', function () {
    Config::set('logo.enabled', true);
    Config::set('logo.api_url', 'https://mock-logo.test/api');
    Config::set('logo.api_token', 'test-token-123');

    Http::fake([
        'https://mock-logo.test/api/customers' => Http::response(['status' => 'ok'], 200),
    ]);

    $service = app(LogoIntegrationService::class);
    $customer = Customer::factory()->create();

    $service->syncCustomer($customer);

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization') &&
            str_contains($request->header('Authorization')[0], 'test-token-123');
    });
});
