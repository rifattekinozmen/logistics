<?php

use App\Enums\CustomerPriority;
use App\Enums\CustomerType;
use App\Models\Customer;
use App\Models\TaxOffice;

it('admin can access customer index', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.customers.index'));

    $response->assertSuccessful();
    $response->assertViewHas('customers');
});

it('admin can access customer create form', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.customers.create'));

    $response->assertSuccessful();
});

it('admin can create customer with new fields', function () {
    [$user, $company] = createAdminUser();
    $taxOffice = TaxOffice::firstOrCreate(
        ['name' => 'Sarıyer VD'],
        ['city_id' => null, 'is_active' => true]
    );

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.customers.store'), [
            'name' => 'Test Müşteri A.Ş.',
            'customer_code' => 'MST-001',
            'customer_type' => CustomerType::Bireysel->value,
            'priority_level' => CustomerPriority::VIP->value,
            'contact_person' => 'Ahmet Yılmaz',
            'phone' => '532 123 45 67',
            'email' => 'test@example.com',
            'tax_number' => '1234567890',
            'tax_office_id' => $taxOffice->id,
            'address' => 'Test Adresi',
            'status' => 1,
        ]);

    $response->assertRedirect();
    expect(Customer::count())->toBe(1);

    $customer = Customer::with('taxOffice')->first();
    expect($customer->name)->toBe('Test Müşteri A.Ş.')
        ->and($customer->customer_code)->toBe('MST-001')
        ->and($customer->customer_type)->toBe('Bireysel')
        ->and($customer->priority_level)->toBe('VIP')
        ->and($customer->contact_person)->toBe('Ahmet Yılmaz')
        ->and($customer->taxOffice?->name)->toBe('Sarıyer VD');
});

it('admin can access customer show page', function () {
    [$user, $company] = createAdminUser();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.customers.show', $customer));

    $response->assertSuccessful();
    $response->assertViewHas('customer');
});

it('admin can access customer edit form', function () {
    [$user, $company] = createAdminUser();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.customers.edit', $customer));

    $response->assertSuccessful();
    $response->assertViewHas('customer');
});

it('admin can update customer with new fields', function () {
    [$user, $company] = createAdminUser();
    $customer = Customer::factory()->create([
        'name' => 'Eski Ad',
        'customer_type' => CustomerType::Bireysel->value,
    ]);
    $taxOffice = TaxOffice::firstOrCreate(
        ['name' => 'Kadıköy VD'],
        ['city_id' => null, 'is_active' => true]
    );

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->put(route('admin.customers.update', $customer), [
            'name' => 'Güncel Müşteri Ltd.',
            'customer_code' => 'MST-002',
            'customer_type' => CustomerType::Kurumsal->value,
            'priority_level' => CustomerPriority::Oncelikli->value,
            'contact_person' => 'Mehmet Demir',
            'phone' => '533 999 88 77',
            'email' => 'guncel@example.com',
            'tax_number' => '9876543210',
            'tax_office_id' => $taxOffice->id,
            'address' => 'Güncel Adres',
            'status' => 1,
        ]);

    $response->assertRedirect();
    $customer->refresh()->load('taxOffice');

    expect($customer->name)->toBe('Güncel Müşteri Ltd.')
        ->and($customer->customer_code)->toBe('MST-002')
        ->and($customer->customer_type)->toBe('Kurumsal')
        ->and($customer->priority_level)->toBe('Öncelikli')
        ->and($customer->contact_person)->toBe('Mehmet Demir')
        ->and($customer->taxOffice?->name)->toBe('Kadıköy VD');
});

it('validates required fields on store', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.customers.store'), [
            'name' => '',
            'status' => '',
        ]);

    $response->assertSessionHasErrors(['name', 'status']);
});
