<?php

namespace App\Listeners;

use App\DocumentFlow\Services\DocumentFlowService;
use App\Events\InvoiceIssued;
use App\Events\ShipmentDelivered;
use App\Models\Payment;

class CreateInvoiceDraft
{
    public function __construct(protected DocumentFlowService $documentFlowService) {}

    public function handle(ShipmentDelivered $event): void
    {
        $shipment = $event->shipment->fresh(['order']);
        $order = $shipment->order;

        if (! $order || $order->status !== 'delivered') {
            return;
        }

        $existingDraft = Payment::query()
            ->where('related_type', \App\Models\Customer::class)
            ->where('related_id', $order->customer_id)
            ->where('status', Payment::STATUS_PENDING)
            ->where('notes', 'like', '%Sipariş #'.$order->order_number.'%')
            ->exists();

        if ($existingDraft) {
            return;
        }

        $payment = Payment::create([
            'related_type' => \App\Models\Customer::class,
            'related_id' => $order->customer_id,
            'payment_type' => 'incoming',
            'amount' => (float) ($order->freight_price ?? 0),
            'due_date' => now()->toDateString(),
            'status' => Payment::STATUS_PENDING,
            'notes' => 'Otomatik fatura taslağı - Sipariş #'.$order->order_number,
        ]);

        $this->documentFlowService->recordInvoiceStep($shipment, $payment);

        event(new InvoiceIssued($order));
    }
}
