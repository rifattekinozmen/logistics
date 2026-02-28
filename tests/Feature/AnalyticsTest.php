<?php

use App\Models\Branch;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

it('yetkili kullanıcı finans analitiği sayfasına erişebilir', function () {
    [$user, $company] = createAdminUser();
    session(['active_company_id' => $company->id]);

    $this->actingAs($user);

    $response = $this->get(route('admin.analytics.finance'));

    $response->assertSuccessful();
    $response->assertViewHas('metrics');
});

it('yetkili kullanıcı operasyon analitiği sayfasına erişebilir', function () {
    [$user, $company] = createAdminUser();
    session(['active_company_id' => $company->id]);

    $this->actingAs($user);

    $response = $this->get(route('admin.analytics.operations'));

    $response->assertSuccessful();
    $response->assertViewHas('kpis');
});

it('yetkili kullanıcı filo analitiği sayfasına erişebilir', function () {
    [$user, $company] = createAdminUser();
    session(['active_company_id' => $company->id]);

    $this->actingAs($user);

    $response = $this->get(route('admin.analytics.fleet'));

    $response->assertSuccessful();
    $response->assertViewHas('performance');
});

it('finans sayfası sipariş ve ödeme verilerini doğru yansıtır', function () {
    [$user, $company] = createAdminUser();

    $order = Order::factory()->create([
        'status' => 'invoiced',
        'freight_price' => 15000,
        'created_at' => now(),
    ]);
    DB::table('orders')->where('id', $order->id)->update(['company_id' => $company->id]);

    Payment::factory()->paid()->create([
        'payment_type' => 'outgoing',
        'amount' => 3000,
        'paid_date' => now(),
        'status' => 1,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.analytics.finance'));

    $response->assertSuccessful();
    $metrics = $response->viewData('metrics');
    expect($metrics['revenue'])->toBe(15000.0)
        ->and($metrics)->toHaveKeys(['expenses', 'net_profit', 'profit_margin', 'monthly_trend']);
});

it('operasyon sayfası sipariş ve sevkiyat KPI verilerini yansıtır', function () {
    [$user, $company] = createAdminUser();

    $order = Order::factory()->create([
        'status' => 'delivered',
        'delivered_at' => now(),
        'created_at' => now()->subDays(5),
    ]);
    DB::table('orders')->where('id', $order->id)->update(['company_id' => $company->id]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.analytics.operations'));

    $response->assertSuccessful();
    $kpis = $response->viewData('kpis');
    expect($kpis['total_orders'])->toBeGreaterThanOrEqual(1)
        ->and($kpis)->toHaveKeys(['completed_orders', 'completion_rate', 'status_breakdown']);
});

it('filo sayfası araç metriklerini doğru yansıtır', function () {
    [$user, $company] = createAdminUser();
    $branch = Branch::factory()->create(['company_id' => $company->id]);
    Vehicle::factory()->count(3)->create(['branch_id' => $branch->id]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.analytics.fleet'));

    $response->assertSuccessful();
    $performance = $response->viewData('performance');
    expect($performance['total_vehicles'])->toBe(3)
        ->and($performance)->toHaveKeys(['active_vehicles', 'vehicle_utilization', 'maintenance_alerts']);
});

it('analytics sayfaları boş veri ile 500 vermeden render olur', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.analytics.finance'));

    $response->assertSuccessful();
    $metrics = $response->viewData('metrics');
    expect($metrics['revenue'])->toBe(0.0)
        ->and($metrics['monthly_trend'])->toBeArray();
});

it('analytics operasyon sayfası boş veri ile render olur', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.analytics.operations'));

    $response->assertSuccessful();
    $kpis = $response->viewData('kpis');
    expect($kpis['total_orders'])->toBe(0)
        ->and($kpis['status_breakdown'])->toBeArray();
});

it('analytics filo sayfası boş veri ile render olur', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.analytics.fleet'));

    $response->assertSuccessful();
    $performance = $response->viewData('performance');
    expect($performance['total_vehicles'])->toBe(0)
        ->and($performance['vehicle_utilization'])->toBeArray();
});

it('analytics route\'larına giriş yapmadan erişilemez', function () {
    $this->get(route('admin.analytics.finance'))->assertRedirect('/login');
    $this->get(route('admin.analytics.operations'))->assertRedirect('/login');
    $this->get(route('admin.analytics.fleet'))->assertRedirect('/login');
    $this->get(route('admin.analytics.fleet-map'))->assertRedirect('/login');
});

it('yetkili kullanıcı filo harita sayfasına erişebilir ve konum endpoint\'i JSON döner', function () {
    [$user, $company] = createAdminUser();
    session(['active_company_id' => $company->id]);

    $this->actingAs($user);

    $response = $this->get(route('admin.analytics.fleet-map'));
    $response->assertSuccessful();
    $response->assertViewHas('company');

    $jsonResponse = $this->getJson(route('admin.analytics.fleet-map.positions'));
    $jsonResponse->assertSuccessful();
    $jsonResponse->assertJsonStructure(['data']);
    expect($jsonResponse->json('data'))->toBeArray();
});
