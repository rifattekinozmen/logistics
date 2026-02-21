<?php

use App\Models\Company;
use App\Models\Document;
use App\Models\Vehicle;

it('can access document create form', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.documents.create'));

    $response->assertSuccessful();
    $response->assertViewHas(['vehicles', 'employees', 'orders']);
});

it('can list documents', function () {
    [$user, $company] = createAdminUser();
    $this->actingAs($user)->withSession(['active_company_id' => $company->id]);
    Document::factory()->count(3)->create();

    $response = $this->get(route('admin.documents.index'));

    $response->assertSuccessful();
    $response->assertViewHas('documents');
});

it('can show a document', function () {
    [$user, $company] = createAdminUser();
    $this->actingAs($user)->withSession(['active_company_id' => $company->id]);
    $document = Document::factory()->create();

    $response = $this->get(route('admin.documents.show', $document));

    $response->assertSuccessful();
    $response->assertViewHas('document');
});

it('can create a document', function () {
    [$user, $company] = createAdminUser();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.documents.store'), [
            'documentable_type' => Vehicle::class,
            'documentable_id' => $vehicle->id,
            'type' => 'license',
            'name' => 'Ehliyet Belgesi',
            'file_path' => 'documents/test.pdf',
            'expiry_date' => now()->addYear()->format('Y-m-d'),
            'status' => 1,
        ]);

    $response->assertRedirect();
    expect(Document::count())->toBe(1);
    expect(Document::first()->category)->toBe('license');
});

it('can delete a document', function () {
    [$user, $company] = createAdminUser();
    $this->actingAs($user)->withSession(['active_company_id' => $company->id]);
    $document = Document::factory()->create();

    $response = $this->delete(route('admin.documents.destroy', $document));

    $response->assertRedirect();
    expect(Document::count())->toBe(0);
});

it('requires authentication to access document routes', function () {
    $company = Company::factory()->create();
    session(['active_company_id' => $company->id]);
    $document = Document::factory()->create(['valid_until' => null]);

    $this->get(route('admin.documents.index'))
        ->assertRedirect('/login');

    $this->get(route('admin.documents.show', $document))
        ->assertRedirect('/login');
});

it('belongs to documentable polymorphically', function () {
    $company = Company::factory()->create();
    session(['active_company_id' => $company->id]);
    $vehicle = Vehicle::factory()->create();
    $document = Document::factory()->create([
        'documentable_type' => Vehicle::class,
        'documentable_id' => $vehicle->id,
        'valid_until' => null,
    ]);

    expect($document->documentable)->toBeInstanceOf(Vehicle::class);
    expect($document->documentable->id)->toBe($vehicle->id);
});
