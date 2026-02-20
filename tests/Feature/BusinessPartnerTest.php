<?php

use App\BusinessPartner\Models\BusinessPartner;
use App\BusinessPartner\Services\BusinessPartnerService;

it('admin can view business partner index', function () {
    [$user, $company] = createAdminUser();

    $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.business-partners.index'))
        ->assertOk();
});

it('creates vendor business partner with auto-generated partner_number', function () {
    [$user, $company] = createAdminUser();
    $service = app(BusinessPartnerService::class);

    $partner = $service->create([
        'company_id' => $company->id,
        'partner_type' => 'vendor',
        'name' => 'Test Tedarikçi A.Ş.',
        'currency' => 'TRY',
        'status' => 1,
    ]);

    expect($partner)->toBeInstanceOf(BusinessPartner::class)
        ->and($partner->partner_number)->toStartWith('BP-')
        ->and($partner->partner_type)->toBe('vendor');
});

it('creates customer business partner', function () {
    [$user, $company] = createAdminUser();
    $service = app(BusinessPartnerService::class);

    $partner = $service->create([
        'company_id' => $company->id,
        'partner_type' => 'customer',
        'name' => 'Test Müşteri Ltd.',
        'currency' => 'USD',
        'status' => 1,
    ]);

    expect($partner->isCustomer())->toBeTrue()
        ->and($partner->isVendor())->toBeFalse();
});

it('partner_number is unique per creation', function () {
    [$user, $company] = createAdminUser();
    $service = app(BusinessPartnerService::class);

    $p1 = $service->create(['company_id' => $company->id, 'partner_type' => 'vendor', 'name' => 'BP1', 'currency' => 'TRY', 'status' => 1]);
    $p2 = $service->create(['company_id' => $company->id, 'partner_type' => 'vendor', 'name' => 'BP2', 'currency' => 'TRY', 'status' => 1]);

    expect($p1->partner_number)->not->toBe($p2->partner_number);
});

it('business partner can be linked to existing customer', function () {
    [$user, $company] = createAdminUser();
    $service = app(BusinessPartnerService::class);
    $customer = \App\Models\Customer::factory()->create();

    $partner = $service->create([
        'company_id' => $company->id,
        'partner_type' => 'customer',
        'name' => $customer->name,
        'currency' => 'TRY',
        'status' => 1,
    ]);

    $service->linkToCustomer($partner, $customer);

    expect($customer->fresh()->business_partner_id)->toBe($partner->id);
});

it('admin can create business partner via HTTP', function () {
    [$user, $company] = createAdminUser();

    $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.business-partners.store'), [
            'company_id' => $company->id,
            'partner_type' => 'carrier',
            'name' => 'HTTP Test Taşıyıcı',
            'currency' => 'TRY',
            'status' => 1,
        ])
        ->assertRedirect();

    expect(BusinessPartner::where('name', 'HTTP Test Taşıyıcı')->exists())->toBeTrue();
});
