<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user, ['*']);
});

it('reporting finance-summary requires authentication', function () {
    Auth::logout();
    $company = Company::factory()->create();

    $response = $this->getJson("/api/v1/reporting/finance-summary?company_id={$company->id}");

    $response->assertUnauthorized();
});

it('reporting finance-summary returns expected structure when authenticated', function () {
    $company = Company::factory()->create();

    $response = $this->getJson("/api/v1/reporting/finance-summary?company_id={$company->id}");

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'revenue',
        'expenses',
        'net_profit',
        'profit_margin',
        'monthly_trend',
    ]);
});

it('reporting fleet-utilization returns expected structure when authenticated', function () {
    $company = Company::factory()->create();

    $response = $this->getJson("/api/v1/reporting/fleet-utilization?company_id={$company->id}");

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'total_vehicles',
        'active_vehicles',
        'idle_vehicles',
        'utilization_rate',
        'vehicle_utilization',
    ]);
});

it('reporting operations-kpi returns expected structure when authenticated', function () {
    $company = Company::factory()->create();

    $response = $this->getJson("/api/v1/reporting/operations-kpi?company_id={$company->id}");

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'total_orders',
        'completed_orders',
        'completion_rate',
        'on_time_delivery_rate',
        'status_breakdown',
    ]);
});

it('reporting finance-summary validates company_id', function () {
    $response = $this->getJson('/api/v1/reporting/finance-summary');

    $response->assertUnprocessable();
});

it('reporting validates company exists and returns unprocessable for invalid id', function () {
    $response = $this->getJson('/api/v1/reporting/finance-summary?company_id=999999');

    $response->assertUnprocessable();
});
