<?php

use App\Models\Company;
use App\Models\CustomRole;
use App\Models\User;

it('kullanıcı yetkili olduğu firmayı seçebilir', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->companies()->attach($company->id, [
        'role' => 'admin',
        'is_default' => true,
    ]);
    $user->roles()->attach(
        CustomRole::firstOrCreate(['name' => 'admin'], ['description' => 'Sistem yöneticisi'])->id
    );

    $this->actingAs($user);

    $response = $this->post(route('admin.companies.switch'), [
        'company_id' => $company->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('active_company_id', $company->id);
});

it('kullanıcı yetkisiz olduğu firmayı seçemez', function () {
    $user = User::factory()->create();
    $ownCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user->companies()->attach($ownCompany->id, ['role' => 'admin', 'is_default' => true]);
    $user->roles()->attach(
        CustomRole::firstOrCreate(['name' => 'admin'], ['description' => 'Sistem yöneticisi'])->id
    );

    $this->actingAs($user);
    session(['active_company_id' => $ownCompany->id]);

    $response = $this->post(route('admin.companies.switch'), [
        'company_id' => $otherCompany->id,
    ]);

    $response->assertForbidden();
});

it('firma değiştirme sonrası session güncellenir', function () {
    $user = User::factory()->create();
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $user->companies()->attach($company1->id, ['role' => 'admin', 'is_default' => true]);
    $user->companies()->attach($company2->id, ['role' => 'admin', 'is_default' => false]);
    $user->roles()->attach(
        CustomRole::firstOrCreate(['name' => 'admin'], ['description' => 'Sistem yöneticisi'])->id
    );

    $this->actingAs($user);

    // İlk firmayı seç
    $this->post(route('admin.companies.switch'), ['company_id' => $company1->id]);
    expect(session('active_company_id'))->toBe($company1->id);

    // İkinci firmaya geç
    $this->post(route('admin.companies.switch'), ['company_id' => $company2->id]);
    expect(session('active_company_id'))->toBe($company2->id);
});

it('aktif firma yoksa varsayılan firma seçilir', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->companies()->attach($company->id, [
        'role' => 'admin',
        'is_default' => true,
    ]);
    $user->roles()->attach(
        CustomRole::firstOrCreate(['name' => 'admin'], ['description' => 'Sistem yöneticisi'])->id
    );

    $this->actingAs($user);

    // Session'da aktif firma yok
    session()->forget('active_company_id');

    $response = $this->get(route('admin.companies.settings', $company));

    // ActiveCompany middleware varsayılan firmayı session'a set eder,
    // ardından controller settings sayfasını görüntüler (200).
    expect(session('active_company_id'))->toBe($company->id);
    $response->assertOk();
});

it('firma ayarları sayfası sadece aktif firma için erişilebilir', function () {
    $user = User::factory()->create();
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $user->companies()->attach($company1->id, ['role' => 'admin', 'is_default' => true]);
    $user->companies()->attach($company2->id, ['role' => 'admin', 'is_default' => false]);
    $user->roles()->attach(
        CustomRole::firstOrCreate(['name' => 'admin'], ['description' => 'Sistem yöneticisi'])->id
    );

    $this->actingAs($user);

    // company1'i aktif yap
    session(['active_company_id' => $company1->id]);

    // company2'nin ayarlarına erişmeye çalış
    $response = $this->get(route('admin.companies.settings', $company2));

    // company1'e yönlendirilmeli
    $response->assertRedirect(route('admin.companies.settings', $company1));
});

it('firma seçim sayfası kullanıcının firmalarını listeler', function () {
    $user = User::factory()->create();
    $company1 = Company::factory()->create(['is_active' => true]);
    $company2 = Company::factory()->create(['is_active' => true]);
    $company3 = Company::factory()->create(['is_active' => false]);
    $user->companies()->attach($company1->id, ['role' => 'admin', 'is_default' => true]);
    $user->companies()->attach($company2->id, ['role' => 'admin', 'is_default' => false]);
    $user->companies()->attach($company3->id, ['role' => 'admin', 'is_default' => false]);
    $user->roles()->attach(
        CustomRole::firstOrCreate(['name' => 'admin'], ['description' => 'Sistem yöneticisi'])->id
    );

    $this->actingAs($user);

    // Kullanıcının firması olduğunda select, varsayılan firma ayarlarına yönlendirir
    $response = $this->get(route('admin.companies.select'));

    $response->assertRedirect(route('admin.companies.settings', $company1));
});
