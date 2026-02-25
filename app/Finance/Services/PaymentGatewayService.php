<?php

namespace App\Finance\Services;

use App\Models\PaymentIntent;
use Illuminate\Support\Arr;

class PaymentGatewayService
{
    public function verifySignature(array $payload, string $providedSignature): bool
    {
        $secret = (string) config('payment.providers.generic.secret');

        if ($secret === '') {
            return false;
        }

        $data = Arr::except($payload, ['signature']);
        ksort($data);
        $baseString = http_build_query($data);

        $expected = hash_hmac('sha256', $baseString, $secret);

        return hash_equals($expected, $providedSignature);
    }

    public function resolveIntent(string $intentId): ?PaymentIntent
    {
        return PaymentIntent::where('provider_intent_id', $intentId)->first();
    }
}

