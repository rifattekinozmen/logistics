<?php

use App\Models\Customer;
use App\Models\Order;

it('can access order list', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.orders.index'));

    $response->assertSuccessful();
    $response->assertViewHas('orders');
});

it('can access order create form', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.orders.create'));

    $response->assertSuccessful();
    $response->assertViewHas('customers');
});

it('can create an order via web', function () {
    [$user, $company] = createAdminUser();
    $customer = Customer::factory()->create(['status' => 1]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'pickup_address' => 'Test Alış Adresi',
            'delivery_address' => 'Test Teslimat Adresi',
            'planned_delivery_date' => now()->addDays(3)->format('Y-m-d'),
            'total_weight' => 100,
            'total_volume' => 2.5,
            'notes' => 'Test sipariş',
        ]);

    $response->assertRedirect();
    expect(Order::count())->toBe(1);
    expect(Order::first()->customer_id)->toBe($customer->id);
    expect(Order::first()->pickup_address)->toBe('Test Alış Adresi');
});

it('can show an order', function () {
    [$user, $company] = createAdminUser();
    $order = Order::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.orders.show', $order));

    $response->assertSuccessful();
    $response->assertViewHas('order');
});

it('can access order edit form', function () {
    [$user, $company] = createAdminUser();
    $order = Order::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.orders.edit', $order));

    $response->assertSuccessful();
    $response->assertViewHas('order');
});

it('can update an order via web', function () {
    [$user, $company] = createAdminUser();
    $order = Order::factory()->create([
        'status' => 'pending',
        'pickup_address' => 'Eski adres',
        'delivery_address' => 'Eski teslimat',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->put(route('admin.orders.update', $order), [
            'status' => 'pending',
            'pickup_address' => 'Yeni Alış Adresi',
            'delivery_address' => 'Yeni Teslimat Adresi',
            'total_weight' => 200,
            'total_volume' => 5,
        ]);

    $response->assertRedirect();
    expect($order->fresh()->pickup_address)->toBe('Yeni Alış Adresi');
    expect($order->fresh()->delivery_address)->toBe('Yeni Teslimat Adresi');
});

it('can delete an order', function () {
    [$user, $company] = createAdminUser();
    $order = Order::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->delete(route('admin.orders.destroy', $order));

    $response->assertRedirect();
    expect(Order::count())->toBe(0);
});

it('returns customer addresses as json for order form', function () {
    [$user, $company] = createAdminUser();
    $customer = Customer::factory()->create(['status' => 1]);
    \App\Models\FavoriteAddress::create([
        'customer_id' => $customer->id,
        'type' => 'pickup',
        'address' => 'Müşteri Alış Adresi',
        'name' => 'Depo',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.orders.customer-addresses', ['customer_id' => $customer->id]));

    $response->assertSuccessful();
    $data = $response->json();
    expect($data)->toHaveKeys(['pickup', 'delivery']);
    expect($data['pickup'])->toBeArray();
    expect($data['delivery'])->toBeArray();
});

it('can access order import form', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.orders.import'));

    $response->assertSuccessful();
});

it('can download order import template', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.orders.import-template'));

    $response->assertSuccessful();
    $response->assertHeader('content-disposition');
});

it('requires authentication to access order routes', function () {
    $order = Order::factory()->create();

    $this->get(route('admin.orders.index'))
        ->assertRedirect('/login');

    $this->get(route('admin.orders.show', $order))
        ->assertRedirect('/login');
});

it('filters orders by workflow shortcut', function () {
    [$user, $company] = createAdminUser();
    $deliveredOrder = Order::factory()->create(['status' => 'delivered']);
    $pendingOrder = Order::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.orders.index', ['workflow' => 'delivered']));

    $response->assertSuccessful()
        ->assertViewHas('orders', function ($orders) use ($deliveredOrder, $pendingOrder) {
            $ids = $orders->getCollection()->pluck('id');

            return $ids->contains($deliveredOrder->id) && ! $ids->contains($pendingOrder->id);
        });
});
