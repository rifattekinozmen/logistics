<?php

namespace App\Order\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Order\Enums\OrderLifecycleState;
use App\Shipment\Enums\ShipmentLifecycleState;

class OrderWorkflowGuardService
{
    public function hasConfirmedPayment(Order $order): bool
    {
        return Payment::query()
            ->where('related_type', \App\Models\Customer::class)
            ->where('related_id', $order->customer_id)
            ->where('status', Payment::STATUS_PAID)
            ->exists();
    }

    public function mapOrderLifecycleState(Order $order): OrderLifecycleState
    {
        return match ($order->status) {
            'cancelled' => OrderLifecycleState::Cancelled,
            'invoiced' => OrderLifecycleState::Invoiced,
            'delivered' => OrderLifecycleState::Delivered,
            'loaded', 'in_transit' => OrderLifecycleState::Shipped,
            'assigned' => OrderLifecycleState::ReadyForShipment,
            'planned' => OrderLifecycleState::Preparing,
            'pending' => $this->hasConfirmedPayment($order)
                ? OrderLifecycleState::PaymentConfirmed
                : OrderLifecycleState::WaitingPayment,
            default => OrderLifecycleState::OrderReceived,
        };
    }

    public function mapShipmentLifecycleState(Shipment $shipment): ShipmentLifecycleState
    {
        return match ($shipment->status) {
            'delivered' => ShipmentLifecycleState::Delivered,
            'in_transit' => ShipmentLifecycleState::InTransit,
            'cancelled' => ShipmentLifecycleState::Failed,
            default => ShipmentLifecycleState::Planned,
        };
    }

    public function canCreateShipment(Order $order): bool
    {
        if (in_array($order->status, ['cancelled', 'invoiced', 'delivered'], true)) {
            return false;
        }

        return $this->hasConfirmedPayment($order);
    }

    public function canMarkDelivered(Order $order, Shipment $shipment): bool
    {
        if (in_array($order->status, ['cancelled', 'invoiced'], true)) {
            return false;
        }

        return $shipment->status === 'in_transit';
    }

    public function canCreateInvoice(Order $order): bool
    {
        return $order->status === 'delivered';
    }

    /**
     * @return array<int, array{key: string, label: string, done: bool, active: bool, problem: bool}>
     */
    public function buildLinearTimeline(Order $order): array
    {
        $paymentConfirmed = $this->hasConfirmedPayment($order);
        $isCancelled = $order->status === 'cancelled';

        $steps = [
            [
                'key' => 'order_received',
                'label' => 'Sipariş Alındı',
                'done' => true,
            ],
            [
                'key' => 'payment_confirmed',
                'label' => $paymentConfirmed ? 'Ödeme Onaylandı' : 'Ödeme Bekleniyor',
                'done' => $paymentConfirmed,
            ],
            [
                'key' => 'preparing',
                'label' => 'Siparişiniz Hazırlanıyor',
                // Ödeme onaylanmadan hazırlık adımı tamamlanmış sayılmasın
                'done' => $paymentConfirmed && in_array($order->status, ['planned', 'assigned', 'loaded', 'in_transit', 'delivered', 'invoiced'], true),
            ],
            [
                'key' => 'shipment',
                'label' => 'Sevkiyat Aşamasında',
                // Ödeme onaylanmadan sevkiyat adımı tamamlanmış sayılmasın
                'done' => $paymentConfirmed && in_array($order->status, ['loaded', 'in_transit', 'delivered', 'invoiced'], true),
            ],
            [
                'key' => 'delivered',
                'label' => 'Teslim Edildi',
                // Ödeme onaylanmadan teslim adımı tamamlanmış sayılmasın
                'done' => $paymentConfirmed && in_array($order->status, ['delivered', 'invoiced'], true),
            ],
        ];

        $activeIndex = null;
        foreach ($steps as $index => $step) {
            if (! $step['done']) {
                $activeIndex = $index;
                break;
            }
        }

        if ($activeIndex === null) {
            $activeIndex = array_key_last($steps);
        }

        foreach ($steps as $index => $step) {
            $steps[$index]['active'] = $index === $activeIndex && ! $isCancelled;
            $steps[$index]['problem'] = $isCancelled && ! $step['done'];
        }

        return $steps;
    }
}
