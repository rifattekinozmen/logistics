<?php

namespace App\Listeners;

use App\Events\InvoiceIssued;
use App\Order\Services\OrderStatusTransitionService;

class CloseOrderAfterInvoice
{
    public function __construct(protected OrderStatusTransitionService $transitionService) {}

    public function handle(InvoiceIssued $event): void
    {
        $order = $event->order->fresh();

        if ($order->status === 'delivered' && $this->transitionService->isValidTransition($order->status, 'invoiced')) {
            $this->transitionService->transition($order, 'invoiced');
        }
    }
}
