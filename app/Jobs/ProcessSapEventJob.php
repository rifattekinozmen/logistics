<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSapEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 300;

    public function __construct(
        public string $eventType,
        public string $eventId,
        public array $payload
    ) {
        $this->onQueue('critical');
    }

    public function handle(): void
    {
        Log::info("Processing SAP event: {$this->eventType}", [
            'event_id' => $this->eventId,
        ]);

        try {
            match ($this->eventType) {
                'SalesOrder.Created' => $this->handleSalesOrderCreated(),
                'SalesOrder.Updated' => $this->handleSalesOrderUpdated(),
                'Delivery.Created' => $this->handleDeliveryCreated(),
                'Invoice.Created' => $this->handleInvoiceCreated(),
                'Material.StockUpdated' => $this->handleMaterialStockUpdated(),
                'Customer.CreditLimitChanged' => $this->handleCustomerCreditLimitChanged(),
                default => $this->handleUnknownEvent(),
            };

            Log::info("Successfully processed SAP event: {$this->eventType}", [
                'event_id' => $this->eventId,
            ]);
        } catch (Exception $e) {
            Log::error("Failed to process SAP event: {$this->eventType}", [
                'event_id' => $this->eventId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function handleSalesOrderCreated(): void
    {
        Log::info('Handling SalesOrder.Created event', $this->payload);
    }

    protected function handleSalesOrderUpdated(): void
    {
        Log::info('Handling SalesOrder.Updated event', $this->payload);
    }

    protected function handleDeliveryCreated(): void
    {
        Log::info('Handling Delivery.Created event', $this->payload);
    }

    protected function handleInvoiceCreated(): void
    {
        Log::info('Handling Invoice.Created event', $this->payload);
    }

    protected function handleMaterialStockUpdated(): void
    {
        Log::info('Handling Material.StockUpdated event', $this->payload);
    }

    protected function handleCustomerCreditLimitChanged(): void
    {
        Log::info('Handling Customer.CreditLimitChanged event', $this->payload);
    }

    protected function handleUnknownEvent(): void
    {
        Log::warning("Unknown SAP event type: {$this->eventType}", [
            'event_id' => $this->eventId,
            'payload' => $this->payload,
        ]);
    }
}
