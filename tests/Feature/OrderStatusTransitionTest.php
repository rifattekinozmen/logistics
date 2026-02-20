<?php

use App\Models\Order;
use App\Order\Services\OrderStatusTransitionService;

it('allows valid transition from pending to planned', function () {
    $service = app(OrderStatusTransitionService::class);

    expect($service->isValidTransition('pending', 'planned'))->toBeTrue();
});

it('rejects invalid transition skipping steps', function () {
    $service = app(OrderStatusTransitionService::class);

    expect($service->isValidTransition('pending', 'delivered'))->toBeFalse();
    expect($service->isValidTransition('pending', 'invoiced'))->toBeFalse();
    expect($service->isValidTransition('invoiced', 'pending'))->toBeFalse();
});

it('returns allowed next statuses for pending', function () {
    $service = app(OrderStatusTransitionService::class);
    $allowed = $service->allowedNextStatuses('pending');

    expect($allowed)->toContain('planned')
        ->and($allowed)->toContain('assigned')
        ->and($allowed)->toContain('cancelled')
        ->and($allowed)->not->toContain('delivered');
});

it('sets planned_at timestamp on transition to planned', function () {
    [$user, $company] = createAdminUser();
    $order = Order::factory()->create(['status' => 'pending']);

    $service = app(OrderStatusTransitionService::class);
    $updated = $service->transition($order, 'planned', $user);

    expect($updated->status)->toBe('planned')
        ->and($updated->planned_at)->not->toBeNull();
});

it('sets invoiced_at timestamp on transition to invoiced', function () {
    [$user, $company] = createAdminUser();
    $order = Order::factory()->create(['status' => 'delivered', 'delivered_at' => now()]);

    $service = app(OrderStatusTransitionService::class);
    $updated = $service->transition($order, 'invoiced', $user);

    expect($updated->status)->toBe('invoiced')
        ->and($updated->invoiced_at)->not->toBeNull();
});

it('throws DomainException on invalid transition', function () {
    $order = Order::factory()->create(['status' => 'pending']);
    $service = app(OrderStatusTransitionService::class);

    expect(fn () => $service->transition($order, 'invoiced'))
        ->toThrow(\DomainException::class);
});

it('transition endpoint requires auth', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    $this->post(route('admin.orders.transition', $order->id), ['status' => 'planned'])
        ->assertRedirect('/login');
});

it('transition endpoint updates order status via HTTP', function () {
    [$user, $company] = createAdminUser();
    $order = Order::factory()->create(['status' => 'pending']);

    $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.orders.transition', $order->id), ['status' => 'planned'])
        ->assertRedirect(route('admin.orders.show', $order->id));

    expect($order->fresh()->status)->toBe('planned');
});
