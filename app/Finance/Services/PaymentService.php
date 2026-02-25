<?php

namespace App\Finance\Services;

use App\Events\OrderPaid;
use App\Events\PaymentReceived;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentIntent;
use Illuminate\Database\DatabaseManager;

class PaymentService
{
    public function __construct(protected DatabaseManager $db) {}

    public function createIntent(Order $order, float $amount, ?string $paymentMethod = null, ?string $provider = null, ?array $meta = null): PaymentIntent
    {
        $intent = $order->paymentIntents()
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if ($intent) {
            return $intent;
        }

        return PaymentIntent::create([
            'order_id' => $order->id,
            'amount' => $amount,
            'currency' => 'TRY',
            'payment_method' => $paymentMethod,
            'status' => 'pending',
            'provider' => $provider,
            'meta' => $meta,
        ]);
    }

    /**
     * Gateway callback veya manuel onay sonrası ödeme onayını işler.
     */
    public function approve(PaymentIntent $intent, array $gatewayPayload = []): Payment
    {
        return $this->db->transaction(function () use ($intent, $gatewayPayload): Payment {
            $intent->refresh();

            if ($intent->status === 'approved') {
                /** @var Payment $existing */
                $existing = $intent->payments()->where('status', Payment::STATUS_PAID)->latest('id')->first();

                return $existing ?? $intent->payments()->latest('id')->first();
            }

            $order = $intent->order()->with('customer')->firstOrFail();
            /** @var Customer|null $customer */
            $customer = $order->customer;

            $payment = Payment::create([
                'payment_intent_id' => $intent->id,
                'related_type' => $customer ? Customer::class : null,
                'related_id' => $customer?->id,
                'payment_type' => 'incoming',
                'amount' => $intent->amount,
                'due_date' => now()->toDateString(),
                'paid_date' => now()->toDateString(),
                'status' => Payment::STATUS_PAID,
                'payment_method' => $intent->payment_method ?? ($gatewayPayload['payment_method'] ?? null),
                'reference_number' => $gatewayPayload['transaction_id'] ?? null,
                'notes' => $gatewayPayload['note'] ?? ('Sipariş #'.$order->order_number.' ödemesi'),
                'created_by' => null,
            ]);

            $intent->status = 'approved';
            $intent->provider = $intent->provider ?? ($gatewayPayload['provider'] ?? null);
            $intent->provider_intent_id = $gatewayPayload['provider_intent_id'] ?? $intent->provider_intent_id;
            $intent->meta = array_merge($intent->meta ?? [], [
                'last_callback' => $gatewayPayload,
            ]);
            $intent->save();

            event(new PaymentReceived($payment));
            event(new OrderPaid($order));

            return $payment;
        });
    }
}
