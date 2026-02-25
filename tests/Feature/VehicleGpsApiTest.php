<?php

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleGpsPosition;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user, ['*']);
});

it('gps positions index returns empty data when no positions', function () {
    $response = $this->getJson('/api/v1/gps/positions');

    $response->assertSuccessful();
    $response->assertJsonPath('data', []);
});

it('gps positions index returns positions when exist', function () {
    $vehicle = Vehicle::factory()->create();
    VehicleGpsPosition::create([
        'vehicle_id' => $vehicle->id,
        'latitude' => 41.0082,
        'longitude' => 28.9784,
        'recorded_at' => now(),
        'source' => 'manual',
    ]);

    $response = $this->getJson('/api/v1/gps/positions');

    $response->assertSuccessful();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.latitude', 41.0082);
    $response->assertJsonPath('data.0.vehicle_id', $vehicle->id);
});

it('vehicle gps latest returns null message when no position', function () {
    $vehicle = Vehicle::factory()->create();

    $response = $this->getJson("/api/v1/vehicles/{$vehicle->id}/gps/latest");

    $response->assertSuccessful();
    $response->assertJsonPath('data', null);
});

it('gps position can be stored via api', function () {
    $vehicle = Vehicle::factory()->create();

    $response = $this->postJson('/api/v1/gps/positions', [
        'vehicle_id' => $vehicle->id,
        'latitude' => 39.9334,
        'longitude' => 32.8597,
        'source' => 'driver_app',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.vehicle_id', $vehicle->id);
    expect((float) $response->json('data.latitude'))->toBe(39.9334);
    $this->assertDatabaseHas('vehicle_gps_positions', [
        'vehicle_id' => $vehicle->id,
        'source' => 'driver_app',
    ]);
});
