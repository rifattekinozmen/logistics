<?php

it('kullanıcı motorin fiyat listesine erişebilir', function () {
    [$user, $company] = createAdminUser();
    session(['active_company_id' => $company->id]);

    $this->actingAs($user);

    $response = $this->get(route('admin.fuel-prices.index'));

    $response->assertSuccessful();
});

it('kullanıcı yeni motorin fiyatı kaydedebilir', function () {
    [$user, $company] = createAdminUser();
    session(['active_company_id' => $company->id]);

    $this->actingAs($user);

    $response = $this->post(route('admin.fuel-prices.store'), [
        'price_date' => now()->format('Y-m-d'),
        'price_type' => 'purchase',
        'price' => 35.50,
        'supplier_name' => 'Test Tedarikçi',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('fuel_prices', [
        'company_id' => $company->id,
        'price_type' => 'purchase',
        'price' => 35.50,
    ]);
});
