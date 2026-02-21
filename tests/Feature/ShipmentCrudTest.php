<?php

use App\Models\Employee;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vehicle;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    session(['active_company_id' => 1]);
});

it('can create a shipment', function () {
    $order = Order::factory()->create();
    $vehicle = Vehicle::factory()->create();
    $driver = Employee::factory()->create();

    $shipmentData = [
        'order_id' => $order->id,
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'shipment_number' => 'SHIP-'.rand(1000, 9999),
        'status' => 'pending',
        'pickup_date' => now()->format('Y-m-d'),
        'planned_delivery_date' => now()->addDays(2)->format('Y-m-d'),
    ];

    $response = $this->postJson('/admin/shipments', $shipmentData);

    $response->assertSuccessful();
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

    $response = $this->putJson("/admin/shipments/{$shipment->id}", [
        'status' => 'in_transit',
    ]);

    $response->assertSuccessful();
    expect($shipment->fresh()->status)->toBe('in_transit');
});

it('can delete a shipment', function () {
    $shipment = Shipment::factory()->create();

    $response = $this->delete("/admin/shipments/{$shipment->id}");

    $response->assertSuccessful();
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
