<?php

it('kullanıcı teslimat import sayfasına erişebilir', function () {
    [$user] = createAdminUser();
    $this->actingAs($user);

    $response = $this->get(route('admin.delivery-imports.index'));

    $response->assertSuccessful();
});

it('kullanıcı yeni teslimat import formunu görebilir', function () {
    [$user] = createAdminUser();
    $this->actingAs($user);

    $response = $this->get(route('admin.delivery-imports.create'));

    $response->assertSuccessful();
});

it('kullanıcı Excel dosyası yükleyebilir', function () {
    [$user, $company] = createAdminUser();
    session(['active_company_id' => $company->id]);

    $this->actingAs($user);

    // Basit CSV dosyası oluştur
    $csvContent = "teslimat_no,musteri_adi,teslimat_adresi\n";
    $csvContent .= "DEL001,Test Müşteri,Test Adresi\n";

    $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('test.csv', $csvContent);

    $response = $this->post(route('admin.delivery-imports.store'), [
        'file' => $file,
    ]);

    $response->assertRedirect();
    // Queue sync olduğunda iş hemen tamamlanır, status 'completed' olur
    $this->assertDatabaseHas('delivery_import_batches', [
        'file_name' => 'test.csv',
    ]);
});
