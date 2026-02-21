<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    session(['active_company_id' => 1]);
});

it('can create an order', function () {
    $customer = Customer::factory()->create();

    $orderData = [
        'customer_id' => $customer->id,
        'order_number' => 'ORD-'.rand(1000, 9999),
        'order_date' => now()->format('Y-m-d'),
        'planned_delivery_date' => now()->addDays(3)->format('Y-m-d'),
        'status' => 'pending',
        'total_amount' => 1500.00,
    ];

    $response = $this->postJson('/api/v1/orders', $orderData);

    $response->assertCreated();
    expect($response->json('data'))->toHaveKey('id');
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
    expect($response->json('data.id'))->toBe($order->id);
});

it('can update an order', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    $response = $this->putJson("/api/v1/orders/{$order->id}", [
        'status' => 'confirmed',
    ]);

    $response->assertSuccessful();
    expect($order->fresh()->status)->toBe('confirmed');
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

it('calculates order total correctly', function () {
    $order = Order::factory()->create([
        'subtotal' => 1000.00,
        'tax_amount' => 180.00,
        'discount_amount' => 50.00,
    ]);

    $expectedTotal = 1000.00 + 180.00 - 50.00;

    expect((float) $order->total_amount)->toBe($expectedTotal);
});
