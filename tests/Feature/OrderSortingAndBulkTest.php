<?php

use App\Models\Order;

it('sorts orders by order number and planned delivery date', function () {
    [$user, $company] = createAdminUser();

    $orderA = Order::factory()->create([
        'order_number' => 'ORD-001',
        'planned_delivery_date' => now()->addDay(),
    ]);
    $orderB = Order::factory()->create([
        'order_number' => 'ORD-010',
        'planned_delivery_date' => now()->addDays(2),
    ]);

    $responseAsc = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.orders.index', ['sort' => 'order_number', 'direction' => 'asc']));

    $responseAsc->assertSuccessful()
        ->assertViewHas('orders', function ($orders) use ($orderA, $orderB) {
            $ids = $orders->getCollection()->pluck('id')->values();

            return $ids->search($orderA->id) < $ids->search($orderB->id);
        });

    $responseDescDate = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.orders.index', ['sort' => 'planned_delivery_date', 'direction' => 'desc']));

    $responseDescDate->assertSuccessful()
        ->assertViewHas('orders', function ($orders) use ($orderA, $orderB) {
            $ids = $orders->getCollection()->pluck('id')->values();

            return $ids->search($orderB->id) < $ids->search($orderA->id);
        });
});

it('performs bulk delete on orders', function () {
    [$user, $company] = createAdminUser();

    $order1 = Order::factory()->create();
    $order2 = Order::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.orders.bulk'), [
            'action' => 'delete',
            'selected' => [$order1->id, $order2->id],
        ]);

    $response->assertRedirect(route('admin.orders.index'));

    expect(Order::whereKey([$order1->id, $order2->id])->count())->toBe(0);
});

it('validates bulk order request', function () {
    [$user, $company] = createAdminUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.orders.bulk'), [
            'action' => 'delete',
            // eksik selected
        ]);

    $response->assertSessionHasErrors(['selected']);
});
