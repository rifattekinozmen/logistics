<?php

namespace App\Delivery\Services;

use App\Models\DeliveryNumber;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Company;
use App\Order\Services\OrderService;
use Illuminate\Support\Str;

class AutoOrderCreationService
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Teslimat numarasından otomatik sipariş oluştur.
     */
    public function createOrderFromDeliveryNumber(DeliveryNumber $deliveryNumber, Company $company): ?Order
    {
        try {
            // Müşteri bul veya oluştur
            $customer = $this->findOrCreateCustomer(
                $deliveryNumber->customer_name,
                $deliveryNumber->customer_phone,
                $company
            );

            // Sipariş numarası oluştur
            $orderNumber = $this->generateOrderNumber($company);

            // Sipariş oluştur
            $order = Order::create([
                'customer_id' => $customer->id,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'pickup_address' => $company->addresses()->where('is_default', true)->first()?->address ?? 'Belirtilmemiş',
                'delivery_address' => $deliveryNumber->delivery_address,
                'planned_delivery_date' => now()->addDays(1), // Varsayılan: 1 gün sonra
                'notes' => "Otomatik oluşturuldu - Teslimat No: {$deliveryNumber->delivery_number}",
                'created_by' => $deliveryNumber->importBatch?->imported_by,
            ]);

            // Teslimat numarasını siparişe bağla
            $deliveryNumber->update([
                'order_id' => $order->id,
                'status' => 'order_created',
            ]);

            return $order;
        } catch (\Exception $e) {
            // Hata durumunda teslimat numarasını error durumuna çek
            $deliveryNumber->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Müşteri bul veya oluştur.
     */
    protected function findOrCreateCustomer(string $name, ?string $phone, Company $company): Customer
    {
        // Önce telefon ile ara
        if ($phone) {
            $customer = Customer::where('phone', $phone)->first();
            if ($customer) {
                return $customer;
            }
        }

        // İsim ile ara
        $customer = Customer::where('name', $name)->first();
        if ($customer) {
            return $customer;
        }

        // Yeni müşteri oluştur
        return Customer::create([
            'name' => $name,
            'phone' => $phone,
            'status' => 1,
        ]);
    }

    /**
     * Sipariş numarası oluştur.
     */
    protected function generateOrderNumber(Company $company): string
    {
        $prefix = $company->settings()
            ->where('setting_key', 'order_prefix')
            ->value('setting_value') ?? 'ORD';
        
        do {
            $number = $prefix.'-'.date('Ymd').'-'.strtoupper(Str::random(4));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
