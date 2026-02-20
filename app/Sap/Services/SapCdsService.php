<?php

namespace App\Sap\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SapCdsService
{
    /**
     * Get real-time inventory data from SAP CDS View.
     */
    public function getInventoryData(string $materialNumber): ?array
    {
        if (! config('sap.sync.enabled')) {
            Log::info('SAP sync disabled');

            return null;
        }

        $url = config('sap.odata_url').'/CDS_INVENTORY_SRV/InventoryView';

        try {
            $response = Http::timeout(config('sap.timeout'))
                ->withBasicAuth(
                    config('sap.username'),
                    config('sap.password')
                )
                ->get($url, [
                    '$filter' => "MaterialNumber eq '{$materialNumber}'",
                    '$format' => 'json',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['d']['results']) && count($data['d']['results']) > 0) {
                    $result = $data['d']['results'][0];

                    return [
                        'material_number' => $result['MaterialNumber'] ?? null,
                        'available_quantity' => $result['AvailableQuantity'] ?? 0,
                        'reserved_quantity' => $result['ReservedQuantity'] ?? 0,
                        'unit_of_measure' => $result['UnitOfMeasure'] ?? null,
                        'plant' => $result['Plant'] ?? null,
                        'storage_location' => $result['StorageLocation'] ?? null,
                        'last_updated' => isset($result['LastUpdated']) ? Carbon::parse($result['LastUpdated']) : null,
                    ];
                }
            }

            Log::warning('SAP CDS inventory query failed', [
                'material_number' => $materialNumber,
                'status' => $response->status(),
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('SAP CDS inventory query error', [
                'material_number' => $materialNumber,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get customer credit limit from SAP.
     */
    public function getCustomerCreditLimit(string $customerId): ?array
    {
        if (! config('sap.sync.enabled')) {
            Log::info('SAP sync disabled');

            return null;
        }

        $url = config('sap.odata_url').'/CDS_CUSTOMER_SRV/CustomerCreditView';

        try {
            $response = Http::timeout(config('sap.timeout'))
                ->withBasicAuth(
                    config('sap.username'),
                    config('sap.password')
                )
                ->get($url, [
                    '$filter' => "CustomerID eq '{$customerId}'",
                    '$format' => 'json',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['d']['results']) && count($data['d']['results']) > 0) {
                    $result = $data['d']['results'][0];

                    return [
                        'customer_id' => $result['CustomerID'] ?? null,
                        'credit_limit' => $result['CreditLimit'] ?? 0,
                        'credit_exposure' => $result['CreditExposure'] ?? 0,
                        'available_credit' => $result['AvailableCredit'] ?? 0,
                        'currency' => $result['Currency'] ?? 'TRY',
                        'payment_terms' => $result['PaymentTerms'] ?? null,
                        'risk_category' => $result['RiskCategory'] ?? null,
                    ];
                }
            }

            Log::warning('SAP CDS customer credit query failed', [
                'customer_id' => $customerId,
                'status' => $response->status(),
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('SAP CDS customer credit query error', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get analytics data from SAP Analytics Cloud.
     */
    public function getAnalyticsData(Carbon $date): ?array
    {
        if (! config('sap.sync.enabled')) {
            Log::info('SAP sync disabled');

            return null;
        }

        $url = config('sap.odata_url').'/CDS_ANALYTICS_SRV/SalesAnalyticsView';

        try {
            $response = Http::timeout(config('sap.timeout'))
                ->withBasicAuth(
                    config('sap.username'),
                    config('sap.password')
                )
                ->get($url, [
                    '$filter' => "SalesDate eq datetime'{$date->format('Y-m-d')}'",
                    '$format' => 'json',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['d']['results'])) {
                    $results = collect($data['d']['results']);

                    return [
                        'total_sales' => $results->sum('SalesAmount'),
                        'total_orders' => $results->count(),
                        'average_order_value' => $results->avg('SalesAmount'),
                        'top_products' => $results->sortByDesc('SalesAmount')->take(10)->values()->toArray(),
                        'sales_by_region' => $results->groupBy('Region')->map(fn ($items) => $items->sum('SalesAmount'))->toArray(),
                    ];
                }
            }

            Log::warning('SAP CDS analytics query failed', [
                'date' => $date->toDateString(),
                'status' => $response->status(),
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('SAP CDS analytics query error', [
                'date' => $date->toDateString(),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
