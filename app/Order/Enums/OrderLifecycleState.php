<?php

namespace App\Order\Enums;

enum OrderLifecycleState: string
{
    case Draft = 'DRAFT';
    case OrderReceived = 'ORDER_RECEIVED';
    case WaitingPayment = 'WAITING_PAYMENT';
    case PaymentConfirmed = 'PAYMENT_CONFIRMED';
    case Preparing = 'PREPARING';
    case ReadyForShipment = 'READY_FOR_SHIPMENT';
    case Shipped = 'SHIPPED';
    case Delivered = 'DELIVERED';
    case Invoiced = 'INVOICED';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';
    case Returned = 'RETURNED';
}
