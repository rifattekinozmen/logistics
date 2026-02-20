<?php

use App\Models\Branch;
use App\Models\InventoryStock;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;

it('can create a warehouse', function () {
    [$user, $company] = createAdminUser();
    $branch = Branch::factory()->create(['company_id' => $company->id]);
    $manager = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.warehouses.store'), [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => 'WH-001',
            'name' => 'Ana Depo',
            'address' => 'İstanbul',
            'warehouse_type' => 'main',
            'status' => 1,
            'manager_id' => $manager->id,
        ]);

    $response->assertRedirect();
    expect(Warehouse::count())->toBe(1);
    expect(Warehouse::first()->code)->toBe('WH-001');
});

it('can list warehouses', function () {
    [$user, $company] = createAdminUser();
    Warehouse::factory()->count(3)->create(['company_id' => $company->id]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.warehouses.index'));

    $response->assertSuccessful();
    $response->assertViewHas('warehouses');
});

it('can show a warehouse', function () {
    [$user, $company] = createAdminUser();
    $warehouse = Warehouse::factory()->create(['company_id' => $company->id]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.warehouses.show', $warehouse));

    $response->assertSuccessful();
    $response->assertViewHas('warehouse');
});

it('can update a warehouse', function () {
    [$user, $company] = createAdminUser();
    $warehouse = Warehouse::factory()->create([
        'company_id' => $company->id,
        'name' => 'Old Name',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->put(route('admin.warehouses.update', $warehouse), [
            'company_id' => $warehouse->company_id,
            'branch_id' => $warehouse->branch_id,
            'code' => $warehouse->code,
            'name' => 'New Name',
            'address' => $warehouse->address,
            'warehouse_type' => $warehouse->warehouse_type,
            'status' => $warehouse->status,
            'manager_id' => $warehouse->manager_id,
        ]);

    $response->assertRedirect();
    expect($warehouse->fresh()->name)->toBe('New Name');
});

it('can delete a warehouse', function () {
    [$user, $company] = createAdminUser();
    $warehouse = Warehouse::factory()->create(['company_id' => $company->id]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->delete(route('admin.warehouses.destroy', $warehouse));

    $response->assertRedirect();
    expect(Warehouse::count())->toBe(0);
});

it('belongs to a company', function () {
    $company = \App\Models\Company::factory()->create();
    $warehouse = Warehouse::factory()->create(['company_id' => $company->id]);

    expect($warehouse->company)->toBeInstanceOf(\App\Models\Company::class);
    expect($warehouse->company->id)->toBe($company->id);
});

it('belongs to a branch', function () {
    $branch = Branch::factory()->create();
    $warehouse = Warehouse::factory()->create(['branch_id' => $branch->id]);

    expect($warehouse->branch)->toBeInstanceOf(Branch::class);
    expect($warehouse->branch->id)->toBe($branch->id);
});

it('has a manager', function () {
    $manager = User::factory()->create();
    $warehouse = Warehouse::factory()->create(['manager_id' => $manager->id]);

    expect($warehouse->manager)->toBeInstanceOf(User::class);
    expect($warehouse->manager->id)->toBe($manager->id);
});

it('has many locations', function () {
    $warehouse = Warehouse::factory()->create();
    WarehouseLocation::factory()->count(5)->create(['warehouse_id' => $warehouse->id]);

    expect($warehouse->locations)->toHaveCount(5);
});

it('has many stocks', function () {
    $warehouse = Warehouse::factory()->create();
    InventoryStock::factory()->count(3)->create(['warehouse_id' => $warehouse->id]);

    expect($warehouse->stocks)->toHaveCount(3);
});

it('requires authentication to access warehouse routes', function () {
    $warehouse = Warehouse::factory()->create();

    $this->get(route('admin.warehouses.index'))
        ->assertRedirect('/login');

    $this->get(route('admin.warehouses.show', $warehouse))
        ->assertRedirect('/login');
});

it('can be marked as inactive', function () {
    $warehouse = Warehouse::factory()->create(['status' => 1]);

    expect($warehouse->status)->toBe(1);

    $warehouse->update(['status' => 0]);
    expect($warehouse->fresh()->status)->toBe(0);
});

it('has unique code per company', function () {
    [$user, $company] = createAdminUser();
    $warehouse1 = Warehouse::factory()->create([
        'company_id' => $company->id,
        'code' => 'WH-001',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.warehouses.store'), [
            'company_id' => $company->id,
            'branch_id' => $warehouse1->branch_id,
            'code' => 'WH-001',
            'name' => 'Duplicate Code Warehouse',
            'address' => 'Test Address',
            'warehouse_type' => 'main',
            'status' => 1,
            'manager_id' => $warehouse1->manager_id,
        ]);

    $response->assertSessionHasErrors(['code']);
});
