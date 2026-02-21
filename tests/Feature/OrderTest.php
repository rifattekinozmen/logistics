<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user, ['*']);
    session(['active_company_id' => 1]);
});

it('can create an order', function () {
    $customer = Customer::factory()->create();

    $orderData = [
        'customer_id' => $customer->id,
        'pickup_address' => 'İstanbul, Türkiye',
        'delivery_address' => 'Ankara, Türkiye',
        'planned_delivery_date' => now()->addDays(3)->format('Y-m-d'),
        'status' => 'pending',
        'total_weight' => 1500,
        'total_volume' => 10,
    ];

    $response = $this->postJson('/api/v1/orders', $orderData);

    $response->assertCreated();
    expect($response->json())->toHaveKey('id');
});

it('can list orders', function () {
    Order::factory()->count(5)->create();

    $response = $this->getJson('/api/v1/orders');

    $response->assertSuccessful();
    expect($response->json('data'))->toHaveCount(5);
});

it('can show an order', function () {
    $order = Order::factory()->create();

    $response = $this->getJson("/api/v1/orders/{$order->id}");

    $response->assertSuccessful();
    expect($response->json('id'))->toBe($order->id);
});

it('can update an order', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    $response = $this->putJson("/api/v1/orders/{$order->id}", [
        'status' => 'assigned',
        'pickup_address' => $order->pickup_address,
        'delivery_address' => $order->delivery_address,
    ]);

    $response->assertSuccessful();
    expect($order->fresh()->status)->toBe('assigned');
});

it('can delete an order', function () {
    $order = Order::factory()->create();

    $response = $this->deleteJson("/api/v1/orders/{$order->id}");

    $response->assertSuccessful();
    expect(Order::find($order->id))->toBeNull();
});

it('validates required fields when creating order', function () {
    $response = $this->postJson('/api/v1/orders', []);

    $response->assertUnprocessable();
});

it('prevents updating order to invalid status', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    $response = $this->putJson("/api/v1/orders/{$order->id}", [
        'status' => 'invalid_status',
    ]);

    $response->assertUnprocessable();
});

it('calculates freight price correctly', function () {
    $order = Order::factory()->create([
        'freight_price' => 13583.73,
    ]);

    expect((float) $order->freight_price)->toBe(13583.73);
});
