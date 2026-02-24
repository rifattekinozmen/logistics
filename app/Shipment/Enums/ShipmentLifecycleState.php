<?php

namespace App\Shipment\Enums;

enum ShipmentLifecycleState: string
{
    case Planned = 'PLANNED';
    case Loading = 'LOADING';
    case InTransit = 'IN_TRANSIT';
    case AtDeliveryPoint = 'AT_DELIVERY_POINT';
    case Delivered = 'DELIVERED';
    case Failed = 'FAILED';
}
