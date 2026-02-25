<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\PaymentIntent;

class CreatePaymentIntentForOrder
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order->fresh();

        if (! $order) {
            return;
        }

        // Eğer zaten bir intent varsa veya tutar yoksa yeni kayıt oluşturma.
        if ($order->paymentIntents()->exists()) {
            return;
        }

        $amount = (float) ($order->freight_price ?? 0);

        if ($amount <= 0) {
            return;
        }

        PaymentIntent::create([
            'order_id' => $order->id,
            'amount' => $amount,
            'currency' => 'TRY',
            'payment_method' => null,
            'status' => 'pending',
        ]);
    }
}
