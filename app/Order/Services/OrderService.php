<?php

namespace App\Order\Services;

use App\DocumentFlow\Services\DocumentFlowService;
use App\Models\Order;
use App\Models\Customer;
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
        $data['order_number'] = $data['order_number'] ?? $this->generateOrderNumber();
        $data['status'] = $data['status'] ?? 'pending';

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
        $query = Order::query()->with(['customer', 'creator']);

        $sort = $filters['sort'] ?? null;
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $sortableColumns = [
            'order_number' => 'order_number',
            'status' => 'status',
            'pickup_address' => 'pickup_address',
            'planned_delivery_date' => 'planned_delivery_date',
            'total_weight' => 'total_weight',
            'created_at' => 'created_at',
        ];

        if (isset($filters['status'])) {
            is_array($filters['status'])
                ? $query->whereIn('status', $filters['status'])
                : $query->where('status', $filters['status']);
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

        if ($sort === 'customer_name') {
            $query->orderBy(
                Customer::select('name')
                    ->whereColumn('customers.id', 'orders.customer_id'),
                $direction
            );
        } else {
            if ($sort && \array_key_exists($sort, $sortableColumns)) {
                $query->orderBy($sortableColumns[$sort], $direction);
            } else {
                $query->latest();
            }
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Filtrelenmiş siparişleri export için getir (sayfalama yok).
     */
    public function getForExport(array $filters = []): Collection
    {
        $query = Order::query()->with(['customer', 'creator']);

        if (isset($filters['status'])) {
            is_array($filters['status'])
                ? $query->whereIn('status', $filters['status'])
                : $query->where('status', $filters['status']);
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
