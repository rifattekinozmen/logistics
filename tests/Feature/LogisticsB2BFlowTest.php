<?php

use App\Events\OrderCreated;
use App\Events\ShipmentDelivered;
use App\Finance\Services\PaymentService;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentIntent;
use App\Models\Shipment;
use App\Models\ShipmentPlan;

it('runs full logistics B2B happy path', function () {
    $user = \App\Models\User::factory()->create();
    $customer = Customer::factory()->create();

    /** @var Order $order */
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'freight_price' => 1000,
        'status' => 'pending',
    ]);

    event(new OrderCreated($order));

    /** @var PaymentIntent $intent */
    $intent = PaymentIntent::where('order_id', $order->id)->first();
    expect($intent)->not()->toBeNull();

    /** @var PaymentService $paymentService */
    $paymentService = app(PaymentService::class);
    $payment = $paymentService->approve($intent, [
        'transaction_id' => 'TEST-TRX-1',
        'provider' => 'generic',
        'provider_intent_id' => 'INTENT-1',
    ]);

    expect($payment->status)->toBe(Payment::STATUS_PAID);

    $shipmentPlan = ShipmentPlan::where('order_id', $order->id)->first();
    expect($shipmentPlan)->not()->toBeNull();

    // Sipariş ve sevkiyatın gerçekten teslim edildiği senaryo;
    // burada sadece payment flow'un başarıyla tamamlandığını test ediyoruz.
    $shipment = Shipment::factory()->create([
        'order_id' => $order->id,
        'shipment_plan_id' => $shipmentPlan->id,
        'status' => 'delivered',
    ]);

    event(new ShipmentDelivered($shipment));
});

it('rejects payment callback with invalid signature', function () {
    $response = $this->postJson('/api/payment/callback', [
        'intent_id' => 'UNKNOWN',
        'transaction_id' => 'X',
        'signature' => 'invalid',
    ]);

    $response->assertStatus(400);
});
