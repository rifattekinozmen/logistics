<?php

use App\Models\Employee;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\Vehicle;

beforeEach(function () {
    [$this->user, $this->company] = createAdminUser();
    $this->actingAs($this->user)
        ->withSession(['active_company_id' => $this->company->id]);
});

it('can create a shipment', function () {
    $order = Order::factory()->create();
    $vehicle = Vehicle::factory()->create();
    $driver = Employee::factory()->create();
    Payment::factory()->paid()->create([
        'related_type' => \App\Models\Customer::class,
        'related_id' => $order->customer_id,
    ]);

    $shipmentData = [
        'order_id' => $order->id,
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'status' => 'pending',
        'pickup_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'delivery_date' => now()->addDays(5)->format('Y-m-d H:i:s'),
    ];

    $response = $this->post(route('admin.shipments.store'), $shipmentData);

    $response->assertRedirect();
    expect(Shipment::count())->toBe(1);
});

it('blocks shipment creation when payment is not confirmed', function () {
    $order = Order::factory()->create(['status' => 'pending']);
    $vehicle = Vehicle::factory()->create();

    $response = $this->post(route('admin.shipments.store'), [
        'order_id' => $order->id,
        'vehicle_id' => $vehicle->id,
        'status' => 'pending',
        'pickup_date' => now()->addDay()->format('Y-m-d H:i:s'),
    ]);

    $response->assertForbidden();
});

it('can list shipments', function () {
    Shipment::factory()->count(3)->create();

    $response = $this->get('/admin/shipments');

    $response->assertSuccessful();
});

it('can show a shipment', function () {
    $shipment = Shipment::factory()->create();

    $response = $this->get("/admin/shipments/{$shipment->id}");

    $response->assertSuccessful();
});

it('can update shipment status', function () {
    $shipment = Shipment::factory()->create(['status' => 'pending']);

    $response = $this->put(route('admin.shipments.update', $shipment), [
        'order_id' => $shipment->order_id,
        'vehicle_id' => $shipment->vehicle_id,
        'driver_id' => $shipment->driver_id,
        'status' => 'in_transit',
        'pickup_date' => $shipment->pickup_date->format('Y-m-d H:i:s'),
        'delivery_date' => $shipment->delivery_date?->format('Y-m-d H:i:s'),
    ]);

    $response->assertRedirect();
    expect($shipment->fresh()->status)->toBe('in_transit');
});

it('can delete a shipment', function () {
    $shipment = Shipment::factory()->create();

    $response = $this->delete(route('admin.shipments.destroy', $shipment));

    $response->assertRedirect(route('admin.shipments.index'));
});

it('validates vehicle assignment', function () {
    $order = Order::factory()->create();

    $response = $this->postJson('/admin/shipments', [
        'order_id' => $order->id,
        'vehicle_id' => 99999, // Non-existent vehicle
        'status' => 'pending',
    ]);

    $response->assertUnprocessable();
});

it('tracks shipment status transitions', function () {
    $shipment = Shipment::factory()->create(['status' => 'pending']);

    $shipment->update(['status' => 'in_transit']);
    expect($shipment->status)->toBe('in_transit');

    $shipment->update(['status' => 'delivered']);
    expect($shipment->status)->toBe('delivered');
});

it('filters shipments by workflow shortcut', function () {
    Shipment::factory()->create(['status' => 'delivered']);
    Shipment::factory()->create(['status' => 'pending']);

    $response = $this->get(route('admin.shipments.index', ['workflow' => 'delivered']));

    $response->assertSuccessful()
        ->assertViewHas('shipments', function ($shipments) {
            return $shipments->getCollection()->every(fn ($shipment) => $shipment->status === 'delivered');
        });
});
