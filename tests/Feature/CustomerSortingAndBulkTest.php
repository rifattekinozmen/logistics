<?php

use App\Models\Customer;

it('sorts customers by name ascending and descending', function () {
    [$user, $company] = createAdminUser();

    Customer::factory()->create(['name' => 'Z Müşteri']);
    Customer::factory()->create(['name' => 'A Müşteri']);

    $responseAsc = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.customers.index', ['sort' => 'name', 'direction' => 'asc']));

    $responseAsc->assertSuccessful()
        ->assertViewHas('customers', function ($customers) {
            $names = $customers->getCollection()->pluck('name')->values();

            return $names->first() === 'A Müşteri' && $names->last() === 'Z Müşteri';
        });

    $responseDesc = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.customers.index', ['sort' => 'name', 'direction' => 'desc']));

    $responseDesc->assertSuccessful()
        ->assertViewHas('customers', function ($customers) {
            $names = $customers->getCollection()->pluck('name')->values();

            return $names->first() === 'Z Müşteri' && $names->last() === 'A Müşteri';
        });
});

it('performs bulk activate and deactivate on customers', function () {
    [$user, $company] = createAdminUser();

    $active = Customer::factory()->create(['status' => 1]);
    $inactive = Customer::factory()->create(['status' => 0]);

    // deactivate both
    $responseDeactivate = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.customers.bulk'), [
            'action' => 'deactivate',
            'selected' => [$active->id, $inactive->id],
        ]);

    $responseDeactivate->assertRedirect(route('admin.customers.index'));

    expect($active->fresh()->status)->toBe(0)
        ->and($inactive->fresh()->status)->toBe(0);

    // activate both
    $responseActivate = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.customers.bulk'), [
            'action' => 'activate',
            'selected' => [$active->id, $inactive->id],
        ]);

    $responseActivate->assertRedirect(route('admin.customers.index'));

    expect($active->fresh()->status)->toBe(1)
        ->and($inactive->fresh()->status)->toBe(1);
});

it('validates bulk customer request', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.customers.bulk'), [
            'action' => 'activate',
            // eksik selected
        ]);

    $response->assertSessionHasErrors(['selected']);
});

