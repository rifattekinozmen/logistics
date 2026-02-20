<?php

namespace App\BusinessPartner\Services;

use App\BusinessPartner\Models\BusinessPartner;
use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;

class BusinessPartnerService
{
    /**
     * Yeni bir Business Partner oluşturur; partner_number otomatik üretilir.
     */
    public function create(array $data): BusinessPartner
    {
        $data['partner_number'] = $this->generatePartnerNumber();

        return BusinessPartner::create($data);
    }

    /**
     * Sayfalanmış BP listesi döner.
     */
    public function getPaginated(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = BusinessPartner::query();

        if (! empty($filters['partner_type'])) {
            $query->where('partner_type', $filters['partner_type']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('partner_number', 'like', '%'.$filters['search'].'%')
                    ->orWhere('tax_number', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Bir Customer kaydını BusinessPartner ile ilişkilendirir.
     */
    public function linkToCustomer(BusinessPartner $partner, Customer $customer): void
    {
        $customer->update(['business_partner_id' => $partner->id]);
    }

    /**
     * Mevcut Customer'dan BusinessPartner oluşturur ve bağlar.
     */
    public function createFromCustomer(Customer $customer): BusinessPartner
    {
        $partner = $this->create([
            'company_id' => 0,
            'partner_type' => 'customer',
            'name' => $customer->name,
            'tax_number' => $customer->tax_number ?? null,
            'phone' => $customer->phone ?? null,
            'email' => $customer->email ?? null,
            'address' => $customer->address ?? null,
            'status' => 1,
        ]);

        $this->linkToCustomer($partner, $customer);

        return $partner;
    }

    /**
     * BP-YYYYMMDD-NNNN formatında benzersiz numara üretir.
     */
    protected function generatePartnerNumber(): string
    {
        do {
            $number = 'BP-'.date('Ymd').'-'.str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (BusinessPartner::where('partner_number', $number)->exists());

        return $number;
    }
}
