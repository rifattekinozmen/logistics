<?php

namespace App\Order\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderService
{
    /**
     * Create a new order.
     */
    public function create(array $data, ?User $user = null): Order
    {
        $data['created_by'] = $user?->id;
        $data['order_number'] = $this->generateOrderNumber();

        return Order::create($data);
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
