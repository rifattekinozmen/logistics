<?php

use App\Models\Employee;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Vehicle;

it('can create a shipment', function () {
    [$user, $company] = createAdminUser();
    $order = Order::factory()->create(['status' => 'pending']);
    $vehicle = Vehicle::factory()->create();
    $driver = Employee::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.shipments.store'), [
            'order_id' => $order->id,
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'status' => 'pending',
            'pickup_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'delivery_date' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'notes' => 'Test shipment',
        ]);

    $response->assertRedirect();
    expect(Shipment::count())->toBe(1);
    expect(Shipment::first()->order_id)->toBe($order->id);
});

it('can list shipments', function () {
    [$user, $company] = createAdminUser();
    Shipment::factory()->count(3)->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.shipments.index'));

    $response->assertSuccessful();
    $response->assertViewHas('shipments');
});

it('can show a shipment', function () {
    [$user, $company] = createAdminUser();
    $shipment = Shipment::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.shipments.show', $shipment));

    $response->assertSuccessful();
    $response->assertViewHas('shipment');
});

it('can update shipment status', function () {
    [$user, $company] = createAdminUser();
    $shipment = Shipment::factory()->pending()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->put(route('admin.shipments.update', $shipment), [
            'order_id' => $shipment->order_id,
            'vehicle_id' => $shipment->vehicle_id,
            'driver_id' => $shipment->driver_id,
            'status' => 'in_transit',
            'pickup_date' => now()->format('Y-m-d H:i:s'),
            'delivery_date' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ]);

    $response->assertRedirect();
    expect($shipment->fresh()->status)->toBe('in_transit');
});

it('can delete a shipment', function () {
    [$user, $company] = createAdminUser();
    $shipment = Shipment::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->delete(route('admin.shipments.destroy', $shipment));

    $response->assertRedirect();
    expect(Shipment::count())->toBe(0);
});

it('belongs to an order', function () {
    $order = Order::factory()->create();
    $shipment = Shipment::factory()->create(['order_id' => $order->id]);

    expect($shipment->order)->toBeInstanceOf(Order::class);
    expect($shipment->order->id)->toBe($order->id);
});

it('belongs to a vehicle', function () {
    $vehicle = Vehicle::factory()->create();
    $shipment = Shipment::factory()->create(['vehicle_id' => $vehicle->id]);

    expect($shipment->vehicle)->toBeInstanceOf(Vehicle::class);
    expect($shipment->vehicle->id)->toBe($vehicle->id);
});

it('belongs to a driver', function () {
    $driver = Employee::factory()->create();
    $shipment = Shipment::factory()->create(['driver_id' => $driver->id]);

    expect($shipment->driver)->toBeInstanceOf(Employee::class);
    expect($shipment->driver->id)->toBe($driver->id);
});

it('requires authentication to access shipment routes', function () {
    $shipment = Shipment::factory()->create();

    $this->get(route('admin.shipments.index'))
        ->assertRedirect('/login');

    $this->get(route('admin.shipments.show', $shipment))
        ->assertRedirect('/login');
});

it('generates qr code on creation', function () {
    $shipment = Shipment::factory()->create();

    expect($shipment->qr_code)->not->toBeNull();
});

it('has correct status transitions', function () {
    $shipment = Shipment::factory()->pending()->create();

    expect($shipment->status)->toBe('pending');

    $shipment->update(['status' => 'in_transit']);
    expect($shipment->fresh()->status)->toBe('in_transit');

    $shipment->update(['status' => 'delivered']);
    expect($shipment->fresh()->status)->toBe('delivered');
});
