<?php

use App\Events\OrderPaid;
use App\Events\ShipmentDelivered;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;

it('moves order to preparing after payment event', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    event(new OrderPaid($order));

    expect($order->fresh()->status)->toBe('planned');
});

it('creates invoice draft and invoices order after shipment delivered event', function () {
    $order = Order::factory()->create(['status' => 'delivered']);
    $shipment = Shipment::factory()->create([
        'order_id' => $order->id,
        'status' => 'delivered',
    ]);

    event(new ShipmentDelivered($shipment));

    expect(Payment::query()
        ->where('related_type', \App\Models\Customer::class)
        ->where('related_id', $order->customer_id)
        ->where('status', Payment::STATUS_PENDING)
        ->where('notes', 'like', '%Sipariş #'.$order->order_number.'%')
        ->exists()
    )->toBeTrue();

    expect($order->fresh()->status)->toBe('invoiced');
});

it('prevents updates on invoiced orders', function () {
    [$user, $company] = createAdminUser();
    $order = Order::factory()->create(['status' => 'invoiced']);

    $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->put(route('admin.orders.update', $order), [
            'status' => 'invoiced',
            'pickup_address' => 'Yeni Adres',
            'delivery_address' => 'Teslimat',
            'total_weight' => 100,
            'total_volume' => 2,
        ])
        ->assertForbidden();
});
