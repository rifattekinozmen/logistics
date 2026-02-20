<?php

namespace App\Order\Services;

use App\DocumentFlow\Services\DocumentFlowService;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderService
{
    public function __construct(protected DocumentFlowService $documentFlowService) {}

    /**
     * Create a new order.
     */
    public function create(array $data, ?User $user = null): Order
    {
        $data['created_by'] = $user?->id;
        $data['order_number'] = $this->generateOrderNumber();

        $order = Order::create($data);
        $this->documentFlowService->initializeOrderChain($order);

        return $order;
    }

    /**
     * Update an existing order.
     */
    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order->fresh();
    }

    /**
     * Get paginated orders.
     */
    public function getPaginated(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Order::query()->with(['customer', 'creator', 'pickupLocation', 'deliveryLocation']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Filtrelenmiş siparişleri export için getir (sayfalama yok).
     */
    public function getForExport(array $filters = []): Collection
    {
        $query = Order::query()->with(['customer', 'creator', 'pickupLocation', 'deliveryLocation']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->get();
    }

    /**
     * Generate unique order number.
     */
    protected function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.date('Ymd').'-'.\strtoupper(\fake()->bothify('####'));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
