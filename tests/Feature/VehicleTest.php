<?php

use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\VehicleDamage;
use App\Models\VehicleInspection;

it('can create a vehicle', function () {
    [$user, $company] = createAdminUser();
    $branch = Branch::factory()->create(['company_id' => $company->id]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.vehicles.store'), [
            'plate' => '34 ABC 123',
            'license_number' => 'ABC123456',
            'brand' => 'Mercedes',
            'series' => 'Actros',
            'model' => 'Actros',
            'year' => 2022,
            'vehicle_type' => 'truck',
            'fuel_type' => 'diesel',
            'transmission' => 'automatic',
            'owner_type' => 'owned',
            'capacity_kg' => 25000,
            'capacity_m3' => 80,
            'status' => 1,
            'branch_id' => $branch->id,
        ]);

    $response->assertRedirect();
    expect(Vehicle::count())->toBe(1);
    $vehicle = Vehicle::first();
    expect($vehicle->plate)->toBe('34 ABC 123');
    expect($vehicle->license_number)->toBe('ABC123456');
    expect($vehicle->fuel_type)->toBe('diesel');
});

it('can list vehicles', function () {
    [$user, $company] = createAdminUser();
    Vehicle::factory()->count(3)->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.vehicles.index'));

    $response->assertSuccessful();
    $response->assertViewHas('vehicles');
});

it('can show a vehicle', function () {
    [$user, $company] = createAdminUser();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.vehicles.show', $vehicle));

    $response->assertSuccessful();
    $response->assertViewHas('vehicle');
});

it('can update a vehicle', function () {
    [$user, $company] = createAdminUser();
    $vehicle = Vehicle::factory()->create(['plate' => '34 OLD 123']);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->put(route('admin.vehicles.update', $vehicle), [
            'plate' => '34 NEW 456',
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
            'vehicle_type' => $vehicle->vehicle_type,
            'capacity_kg' => $vehicle->capacity_kg,
            'capacity_m3' => $vehicle->capacity_m3,
            'status' => $vehicle->status,
            'branch_id' => $vehicle->branch_id,
        ]);

    $response->assertRedirect();
    expect($vehicle->fresh()->plate)->toBe('34 NEW 456');
});

it('can delete a vehicle', function () {
    [$user, $company] = createAdminUser();
    $vehicle = Vehicle::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->delete(route('admin.vehicles.destroy', $vehicle));

    $response->assertRedirect();
    expect(Vehicle::count())->toBe(0);
});

it('belongs to a branch', function () {
    $branch = Branch::factory()->create();
    $vehicle = Vehicle::factory()->create(['branch_id' => $branch->id]);

    expect($vehicle->branch)->toBeInstanceOf(Branch::class);
    expect($vehicle->branch->id)->toBe($branch->id);
});

it('has many inspections', function () {
    $vehicle = Vehicle::factory()->create();
    VehicleInspection::factory()->count(3)->create(['vehicle_id' => $vehicle->id]);

    expect($vehicle->inspections)->toHaveCount(3);
});

it('has many damages', function () {
    $vehicle = Vehicle::factory()->create();
    VehicleDamage::factory()->count(2)->create(['vehicle_id' => $vehicle->id]);

    expect($vehicle->damages)->toHaveCount(2);
});

it('requires authentication to access vehicle routes', function () {
    $vehicle = Vehicle::factory()->create();

    $this->get(route('admin.vehicles.index'))
        ->assertRedirect('/login');

    $this->get(route('admin.vehicles.show', $vehicle))
        ->assertRedirect('/login');
});

it('can be marked as inactive', function () {
    $vehicle = Vehicle::factory()->create(['status' => 1]);

    expect($vehicle->status)->toBe(1);

    $vehicle->update(['status' => 0]);
    expect($vehicle->fresh()->status)->toBe(0);
});

it('stores capacity with proper decimals', function () {
    $vehicle = Vehicle::factory()->create([
        'capacity_kg' => 25500.75,
        'capacity_m3' => 85.50,
    ]);

    expect($vehicle->capacity_kg)->toEqual('25500.75');
    expect($vehicle->capacity_m3)->toEqual('85.50');
});

it('validates required fields', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.vehicles.store'), [
            'brand' => 'Mercedes',
        ]);

    $response->assertSessionHasErrors(['plate']);
});
