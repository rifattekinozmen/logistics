<?php

namespace App\Order\Services;

use App\Models\Order;
use App\Models\User;
use DomainException;

class OrderStatusTransitionService
{
    /**
     * SAP uyumlu durum geçiş haritası.
     * Her durum için geçiş yapılabilecek hedef durumların listesi.
     *
     * @var array<string, array<int, string>>
     */
    private array $allowedTransitions = [
        'pending' => ['planned', 'assigned', 'cancelled'],
        'planned' => ['assigned', 'cancelled'],
        'assigned' => ['loaded', 'in_transit', 'cancelled'],
        'loaded' => ['in_transit', 'cancelled'],
        'in_transit' => ['delivered', 'cancelled'],
        'delivered' => ['invoiced'],
        'invoiced' => [],
        'cancelled' => [],
    ];

    /**
     * Bir durum geçişinin geçerli olup olmadığını kontrol eder.
     */
    public function isValidTransition(string $from, string $to): bool
    {
        return in_array($to, $this->allowedTransitions[$from] ?? [], true);
    }

    /**
     * Mevcut durumdan geçilebilecek statüleri döner.
     *
     * @param  string  $current  Mevcut durum
     * @return array<int, string> İzin verilen hedef durumlar
     */
    public function allowedNextStatuses(string $current): array
    {
        return $this->allowedTransitions[$current] ?? [];
    }

    /**
     * Sipariş durumunu günceller ve ilgili zaman damgasını set eder.
     * Planned, delivered ve invoiced durumlarına geçişte otomatik timestamp eklenir.
     *
     * @param  Order  $order  Güncellenecek sipariş
     * @param  string  $newStatus  Hedef durum
     * @param  User|null  $actor  İşlemi yapan kullanıcı (gelecekte audit log için)
     * @return Order Güncellenmiş sipariş modeli
     *
     * @throws DomainException Geçersiz geçiş denemesinde
     */
    public function transition(Order $order, string $newStatus, ?User $actor = null): Order
    {
        if (! $this->isValidTransition($order->status, $newStatus)) {
            throw new DomainException(
                "'{$order->status}' durumundan '{$newStatus}' durumuna geçiş yapılamaz."
            );
        }

        $updates = ['status' => $newStatus];

        match ($newStatus) {
            'planned' => $updates['planned_at'] = now(),
            'loaded' => $updates['actual_pickup_date'] = now(),
            'delivered' => $updates['delivered_at'] = now(),
            'invoiced' => $updates['invoiced_at'] = now(),
            default => null,
        };

        $order->update($updates);

        return $order->fresh();
    }
}
