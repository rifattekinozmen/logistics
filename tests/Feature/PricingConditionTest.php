<?php

use App\Pricing\Models\PricingCondition;
use App\Pricing\Services\PricingService;

it('admin can view pricing conditions index', function () {
    [$user, $company] = createAdminUser();

    $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.pricing-conditions.index'))
        ->assertOk();
});

it('creates weight-based pricing condition', function () {
    [$user, $company] = createAdminUser();

    $condition = PricingCondition::create([
        'company_id' => $company->id,
        'condition_type' => 'weight_based',
        'name' => 'Ağırlık Koşulu Test',
        'price_per_kg' => 2.5,
        'min_charge' => 100,
        'currency' => 'TRY',
        'status' => 1,
    ]);

    expect($condition)->toBeInstanceOf(PricingCondition::class)
        ->and($condition->condition_type)->toBe('weight_based')
        ->and($condition->isActive())->toBeTrue();
});

it('calculates weight-based price correctly', function () {
    [$user, $company] = createAdminUser();
    $service = app(PricingService::class);

    $condition = PricingCondition::create([
        'company_id' => $company->id,
        'condition_type' => 'weight_based',
        'name' => 'Ağırlık Test',
        'price_per_kg' => 3.0,
        'min_charge' => 50,
        'currency' => 'TRY',
        'status' => 1,
    ]);

    $price = $service->calculatePrice($condition, weightKg: 100, distanceKm: 0);

    expect($price)->toBe(300.0);
});

it('applies minimum charge when calculated price is lower', function () {
    [$user, $company] = createAdminUser();
    $service = app(PricingService::class);

    $condition = PricingCondition::create([
        'company_id' => $company->id,
        'condition_type' => 'weight_based',
        'name' => 'Min Ücret Test',
        'price_per_kg' => 1.0,
        'min_charge' => 200,
        'currency' => 'TRY',
        'status' => 1,
    ]);

    $price = $service->calculatePrice($condition, weightKg: 10, distanceKm: 0);

    expect($price)->toBe(200.0);
});

it('calculates flat rate pricing correctly', function () {
    [$user, $company] = createAdminUser();
    $service = app(PricingService::class);

    $condition = PricingCondition::create([
        'company_id' => $company->id,
        'condition_type' => 'flat',
        'name' => 'Sabit Ücret Test',
        'flat_rate' => 1500,
        'min_charge' => 0,
        'currency' => 'TRY',
        'status' => 1,
    ]);

    $price = $service->calculatePrice($condition, weightKg: 0, distanceKm: 0);

    expect($price)->toBe(1500.0);
});

it('finds applicable condition for given route and weight', function () {
    [$user, $company] = createAdminUser();
    $service = app(PricingService::class);

    PricingCondition::create([
        'company_id' => $company->id,
        'condition_type' => 'weight_based',
        'name' => 'İstanbul-Ankara',
        'route_origin' => 'İstanbul',
        'route_destination' => 'Ankara',
        'weight_from' => 0,
        'weight_to' => 10000,
        'price_per_kg' => 2.0,
        'min_charge' => 100,
        'currency' => 'TRY',
        'status' => 1,
    ]);

    $found = $service->findApplicableCondition(
        origin: 'İstanbul',
        destination: 'Ankara',
        weightKg: 500,
        companyId: $company->id,
    );

    expect($found)->not->toBeNull()
        ->and($found->name)->toBe('İstanbul-Ankara');
});

it('admin can create pricing condition via HTTP', function () {
    [$user, $company] = createAdminUser();

    $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.pricing-conditions.store'), [
            'company_id' => $company->id,
            'condition_type' => 'flat',
            'name' => 'HTTP Test Sabit Koşul',
            'flat_rate' => 750,
            'min_charge' => 0,
            'currency' => 'TRY',
            'status' => 1,
        ])
        ->assertRedirect();

    expect(PricingCondition::where('name', 'HTTP Test Sabit Koşul')->exists())->toBeTrue();
});
