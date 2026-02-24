<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Order\Services\OrderStatusTransitionService;

class MoveOrderToPreparing
{
    public function __construct(protected OrderStatusTransitionService $transitionService) {}

    public function handle(OrderPaid $event): void
    {
        $order = $event->order->fresh();

        if ($order->status === 'pending' && $this->transitionService->isValidTransition($order->status, 'planned')) {
            $this->transitionService->transition($order, 'planned');
        }
    }
}
