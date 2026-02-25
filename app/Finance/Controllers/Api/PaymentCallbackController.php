<?php

namespace App\Finance\Controllers\Api;

use App\Finance\Services\PaymentGatewayService;
use App\Finance\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentCallbackController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $gatewayService,
        protected PaymentService $paymentService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $signature = (string) $request->header('X-Payment-Signature', $payload['signature'] ?? '');

        if (! $this->gatewayService->verifySignature($payload, $signature)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
        }

        $intentId = (string) ($payload['intent_id'] ?? '');
        $intent = $this->gatewayService->resolveIntent($intentId);

        if (! $intent) {
            return response()->json(['status' => 'error', 'message' => 'Payment intent not found'], 404);
        }

        $payment = $this->paymentService->approve($intent, [
            'transaction_id' => $payload['transaction_id'] ?? null,
            'provider' => config('payment.default_provider'),
            'provider_intent_id' => $intentId,
            'payment_method' => $payload['payment_method'] ?? null,
            'note' => $payload['note'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'payment_id' => $payment->id,
        ]);
    }
}

