<?php

use App\DocumentFlow\Models\DocumentFlow;
use App\DocumentFlow\Services\DocumentFlowService;
use App\Models\Order;
use App\Models\Shipment;

it('creates document flow record when order is created', function () {
    [$user, $company] = createAdminUser();
    $service = app(\App\Order\Services\OrderService::class);

    $order = $service->create([
        'customer_id' => \App\Models\Customer::factory()->create()->id,
        'status' => 'pending',
        'pickup_address' => 'Test Alış Adresi',
        'delivery_address' => 'Test Teslimat Adresi',
    ], $user);

    expect(DocumentFlow::where('source_type', Order::class)
        ->where('source_id', $order->id)
        ->where('step', 'order_created')
        ->exists()
    )->toBeTrue();
});

it('getChainFor returns flows for an order', function () {
    $order = Order::factory()->create(['status' => 'pending']);
    $service = app(DocumentFlowService::class);
    $service->initializeOrderChain($order);

    $chain = $service->getChainFor(Order::class, $order->id);

    expect($chain)->toHaveCount(1)
        ->and($chain->first()->step)->toBe('order_created');
});

it('records delivery step when shipment is linked to order', function () {
    $order = Order::factory()->create(['status' => 'assigned']);
    $shipment = Shipment::create([
        'order_id' => $order->id,
        'status' => 'assigned',
    ]);
    $service = app(DocumentFlowService::class);

    $flow = $service->recordDeliveryStep($order, $shipment);

    expect($flow->step)->toBe('delivery_assigned')
        ->and($flow->target_type)->toBe(Shipment::class)
        ->and($flow->target_id)->toBe($shipment->id);
});

it('completes a document flow step', function () {
    $order = Order::factory()->create(['status' => 'pending']);
    $service = app(DocumentFlowService::class);
    $flow = $service->initializeOrderChain($order);

    $completed = $service->completeStep($flow);

    expect($completed->completed_at)->not->toBeNull();
});

it('document flow show view is accessible for admin', function () {
    [$user, $company] = createAdminUser();
    $order = Order::factory()->create(['status' => 'pending']);

    $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.orders.document-flow', $order->id))
        ->assertOk()
        ->assertViewIs('admin.document-flows.show');
});

it('order model has documentFlows relationship', function () {
    $order = Order::factory()->create(['status' => 'pending']);
    $service = app(DocumentFlowService::class);
    $service->initializeOrderChain($order);

    expect($order->documentFlows)->toHaveCount(1);
});
