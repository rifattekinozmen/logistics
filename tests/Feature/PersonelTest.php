<?php

use App\Models\Branch;
use App\Models\Department;
use App\Models\Personel;
use App\Models\Position;

beforeEach(function () {
    $this->branch = Branch::factory()->create();
    $this->department = Department::factory()->create(['branch_id' => $this->branch->id]);
    $this->position = Position::factory()->create(['department_id' => $this->department->id]);
});

it('can list personnel', function () {
    [$user, $company] = createAdminUser();
    Personel::factory()->count(3)->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.personnel.index'));

    $response->assertSuccessful();
    $response->assertViewHas('personels');
});

it('can show create form', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.personnel.create'));

    $response->assertSuccessful();
    $response->assertViewHas('countries');
    $response->assertViewHas('departments');
    $response->assertViewHas('positions');
});

it('can create personnel', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.personnel.store'), [
            'ad_soyad' => 'Test Personel',
            'email' => 'test@example.com',
            'departman' => $this->department->name,
            'pozisyon' => $this->position->name,
            'ise_baslama_tarihi' => now()->format('Y-m-d'),
        ]);

    $response->assertRedirect(route('admin.personnel.index'));
    expect(Personel::count())->toBe(1);
    expect(Personel::first()->ad_soyad)->toBe('Test Personel');
});

it('can show personnel', function () {
    [$user, $company] = createAdminUser();
    $personel = Personel::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.personnel.show', $personel));

    $response->assertSuccessful();
    $response->assertViewHas('personnel');
});

it('can show edit form', function () {
    [$user, $company] = createAdminUser();
    $personel = Personel::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.personnel.edit', $personel));

    $response->assertSuccessful();
    $response->assertViewHas('personnel');
});

it('can update personnel', function () {
    [$user, $company] = createAdminUser();
    $personel = Personel::factory()->create(['ad_soyad' => 'Eski Ad']);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->put(route('admin.personnel.update', $personel), [
            'ad_soyad' => 'Yeni Ad',
            'email' => $personel->email,
            'departman' => $personel->departman,
            'pozisyon' => $personel->pozisyon,
            'ise_baslama_tarihi' => $personel->ise_baslama_tarihi->format('Y-m-d'),
        ]);

    $response->assertRedirect(route('admin.personnel.index'));
    expect($personel->fresh()->ad_soyad)->toBe('Yeni Ad');
});

it('can delete personnel', function () {
    [$user, $company] = createAdminUser();
    $personel = Personel::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->delete(route('admin.personnel.destroy', $personel));

    $response->assertRedirect(route('admin.personnel.index'));
    expect(Personel::count())->toBe(0);
});
