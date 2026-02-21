<?php

it('yetkili kullanıcı finans analitiği sayfasına erişebilir', function () {
    [$user, $company] = createAdminUser();
    session(['active_company_id' => $company->id]);

    $this->actingAs($user);

    $response = $this->get(route('admin.analytics.finance'));

    $response->assertSuccessful();
});

it('yetkili kullanıcı operasyon analitiği sayfasına erişebilir', function () {
    [$user, $company] = createAdminUser();
    session(['active_company_id' => $company->id]);

    $this->actingAs($user);

    $response = $this->get(route('admin.analytics.operations'));

    $response->assertSuccessful();
});
