<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SAP OData Configuration
    |--------------------------------------------------------------------------
    |
    | SAP S/4HANA OData service connection settings.
    | Use environment variables for sensitive data.
    |
    */

    'odata_url' => env('SAP_ODATA_URL', 'https://sap-system:port/sap/opu/odata/sap'),
    'username' => env('SAP_USERNAME'),
    'password' => env('SAP_PASSWORD'),
    'timeout' => env('SAP_TIMEOUT', 30),
    'verify_ssl' => env('SAP_VERIFY_SSL', true),

    /*
    |--------------------------------------------------------------------------
    | Document Types
    |--------------------------------------------------------------------------
    |
    | SAP document type mappings.
    |
    */

    'document_types' => [
        'sales_order' => 'TA',      // Sales Order
        'delivery' => 'J',           // Delivery
        'invoice' => 'F2',           // Invoice
        'shipment' => 'TK',          // Shipment
    ],

    /*
    |--------------------------------------------------------------------------
    | OData Service Paths
    |--------------------------------------------------------------------------
    |
    | SAP OData service endpoint paths.
    |
    */

    'service_paths' => [
        'sales_order' => '/API_SALES_ORDER_SRV/A_SalesOrder',
        'delivery' => '/API_OUTBOUND_DELIVERY_SRV_01/A_OutbDeliveryHeader',
        'invoice' => '/API_SALES_INVOICE_SRV/A_SalesInvoice',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for sync behavior.
    |
    */

    'sync' => [
        'enabled' => env('SAP_SYNC_ENABLED', false),
        'auto_sync' => env('SAP_AUTO_SYNC', false),
        'retry_attempts' => env('SAP_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('SAP_RETRY_DELAY', 5), // seconds
        'batch_size' => env('SAP_BATCH_SIZE', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Mappings
    |--------------------------------------------------------------------------
    |
    | Map local fields to SAP fields.
    |
    */

    'field_mappings' => [
        'sales_order' => [
            'order_number' => 'SalesOrder',
            'customer_id' => 'SoldToParty',
            'order_date' => 'SalesOrderDate',
            'requested_delivery_date' => 'RequestedDeliveryDate',
            'sales_organization' => 'SalesOrganization',
            'distribution_channel' => 'DistributionChannel',
            'division' => 'Division',
        ],
        'delivery' => [
            'delivery_number' => 'DeliveryDocument',
            'delivery_date' => 'ActualGoodsMovementDate',
            'sales_order' => 'ReferenceSDDocument',
        ],
        'invoice' => [
            'invoice_number' => 'BillingDocument',
            'billing_date' => 'BillingDocumentDate',
            'sales_order' => 'SalesOrder',
        ],
    ],
];
