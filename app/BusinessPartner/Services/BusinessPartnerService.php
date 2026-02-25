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

        $sort = $filters['sort'] ?? null;
        $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $sortableColumns = [
            'partner_number' => 'partner_number',
            'name' => 'name',
            'partner_type' => 'partner_type',
            'tax_number' => 'tax_number',
            'currency' => 'currency',
            'status' => 'status',
            'created_at' => 'created_at',
        ];

        if (! empty($filters['partner_type'])) {
            $query->where('partner_type', $filters['partner_type']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('partner_number', 'like', '%'.$search.'%')
                    ->orWhere('tax_number', 'like', '%'.$search.'%');
            });
        }

        if ($sort && \array_key_exists($sort, $sortableColumns)) {
            $query->orderBy($sortableColumns[$sort], $direction);
        } else {
            $query->orderBy('name');
        }

        return $query->paginate($perPage)->withQueryString();
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
