<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Payment $payment
    ) {}

    public function broadcastOn(): Channel
    {
        $companyId = $this->payment->related?->company_id ?? session('active_company_id');

        return new PrivateChannel('company.'.$companyId);
    }

    public function broadcastWith(): array
    {
        return [
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'payment_type' => $this->payment->payment_type,
            'payment_method' => $this->payment->payment_method,
            'reference_number' => $this->payment->reference_number,
            'paid_date' => $this->payment->paid_date?->toIso8601String(),
            'related_type' => class_basename($this->payment->related_type),
            'related_name' => $this->payment->related?->name ?? 'N/A',
        ];
    }

    public function broadcastAs(): string
    {
        return 'payment.received';
    }
}
