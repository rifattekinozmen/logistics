<?php

use App\Analytics\Services\AnalyticsDashboardService;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('calculates financial metrics for given period', function (): void {
    /** @var Company $company */
    $company = Company::factory()->create();

    $start = now()->subDays(30);
    $end = now();

    // Orders (only invoiced and in range should be counted)
    DB::table('orders')->insert([
        [
            'company_id' => $company->id,
            'status' => 'invoiced',
            'freight_price' => 1000,
            'created_at' => now()->subDays(5),
        ],
        [
            'company_id' => $company->id,
            'status' => 'invoiced',
            'freight_price' => 500,
            'created_at' => now()->subDays(10),
        ],
        [
            'company_id' => $company->id,
            'status' => 'pending',
            'freight_price' => 9999,
            'created_at' => now()->subDays(3),
        ],
        [
            'company_id' => $company->id,
            'status' => 'invoiced',
            'freight_price' => 300,
            'created_at' => now()->subDays(40), // out of range
        ],
    ]);

    // Payments (only outgoing + status=1 within range)
    DB::table('payments')->insert([
        [
            'payment_type' => 'outgoing',
            'status' => 1,
            'amount' => 800,
            'paid_date' => now()->subDays(7),
        ],
        [
            'payment_type' => 'outgoing',
            'status' => 0,
            'amount' => 200,
            'paid_date' => now()->subDays(8),
        ],
        [
            'payment_type' => 'incoming',
            'status' => 1,
            'amount' => 999,
            'paid_date' => now()->subDays(9),
        ],
    ]);

    /** @var AnalyticsDashboardService $service */
    $service = app(AnalyticsDashboardService::class);

    $metrics = $service->getFinancialMetrics($company, $start, $end);

    // Revenue: only invoiced + in range = 1000 + 500
    expect($metrics['revenue'])->toBe(1500.0);
    // Expenses: only outgoing + status=1 + in range = 800
    expect($metrics['expenses'])->toBe(800.0);
    expect($metrics['net_profit'])->toBe(700.0);
    expect($metrics['profit_margin'])->toBeFloat();
    expect($metrics['monthly_trend'])->toBeArray()->not()->toBeEmpty();
});

it('calculates operational KPIs for last 30 days', function (): void {
    /** @var Company $company */
    $company = Company::factory()->create();

    $now = now();
    $past = $now->copy()->subDays(10);

    // Orders
    DB::table('orders')->insert([
        [
            'company_id' => $company->id,
            'status' => 'delivered',
            'freight_price' => 100,
            'created_at' => $past,
            'delivered_at' => $past->copy()->addDays(1),
        ],
        [
            'company_id' => $company->id,
            'status' => 'delivered',
            'freight_price' => 200,
            'created_at' => $past->copy()->addDay(),
            'delivered_at' => $past->copy()->addDays(3),
        ],
        [
            'company_id' => $company->id,
            'status' => 'pending',
            'freight_price' => 50,
            'created_at' => $past,
        ],
    ]);

    // Shipments for on-time and total deliveries
    DB::table('shipments')->insert([
        [
            'order_id' => 1,
            'vehicle_id' => null,
            'status' => 'delivered',
            'pickup_date' => $past,
            'delivery_date' => $past->copy()->addDay(), // on-time (<=)
            'created_at' => $past,
        ],
        [
            'order_id' => 2,
            'vehicle_id' => null,
            'status' => 'delivered',
            'pickup_date' => $past,
            'delivery_date' => $past->copy()->addDays(5), // late
            'created_at' => $past,
        ],
    ]);

    /** @var AnalyticsDashboardService $service */
    $service = app(AnalyticsDashboardService::class);
    $kpis = $service->getOperationalKpis($company);

    expect($kpis['total_orders'])->toBe(3);
    expect($kpis['completed_orders'])->toBe(2);
    expect($kpis['completion_rate'])->toBeFloat();
    expect($kpis['total_deliveries'])->toBe(2);
    expect($kpis['on_time_deliveries'])->toBe(1);
    expect($kpis['on_time_delivery_rate'])->toBeFloat();
    expect($kpis['avg_processing_time'])->toBeFloat();
    expect($kpis['status_breakdown'])->toBeArray()->not()->toBeEmpty();
});

it('calculates fleet performance metrics', function (): void {
    /** @var Company $company */
    $company = Company::factory()->create();

    // Create a branch for the company (using raw insert to avoid needing factories)
    $branchId = DB::table('branches')->insertGetId([
        'company_id' => $company->id,
        'name' => 'Merkez',
        'status' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Vehicles: 3 active
    $vehicle1 = DB::table('vehicles')->insertGetId([
        'plate' => '34AAA001',
        'brand' => 'Ford',
        'model' => 'F-Max',
        'status' => 1,
        'branch_id' => $branchId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $vehicle2 = DB::table('vehicles')->insertGetId([
        'plate' => '34BBB002',
        'brand' => 'Mercedes',
        'model' => 'Actros',
        'status' => 1,
        'branch_id' => $branchId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $vehicle3 = DB::table('vehicles')->insertGetId([
        'plate' => '34CCC003',
        'brand' => 'Volvo',
        'model' => 'FH',
        'status' => 1,
        'branch_id' => $branchId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Shipments in last 30 days for two vehicles
    DB::table('shipments')->insert([
        [
            'order_id' => null,
            'vehicle_id' => $vehicle1,
            'status' => 'in_transit',
            'pickup_date' => now()->subDays(5),
            'delivery_date' => null,
            'created_at' => now()->subDays(5),
        ],
        [
            'order_id' => null,
            'vehicle_id' => $vehicle2,
            'status' => 'assigned',
            'pickup_date' => now()->subDays(2),
            'delivery_date' => null,
            'created_at' => now()->subDays(2),
        ],
    ]);

    /** @var AnalyticsDashboardService $service */
    $service = app(AnalyticsDashboardService::class);
    $performance = $service->getFleetPerformance($company);

    expect($performance['total_vehicles'])->toBe(3);
    expect($performance['active_vehicles'])->toBe(2);
    expect($performance['idle_vehicles'])->toBe(1);
    expect($performance['utilization_rate'])->toBeFloat();
    expect($performance['vehicle_utilization'])->toBeArray()->not()->toBeEmpty();
    expect($performance['maintenance_alerts'])->toBeArray();
});

