<?php

namespace App\Customer\Concerns;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

trait ResolvesCustomerFromUser
{
    /**
     * Aktif kullanıcıya bağlı müşteriyi döndürür.
     * Müşteri bulunamazsa 404 fırlatır.
     */
    protected function resolveCustomer(): Customer
    {
        $user = Auth::user();
        $customer = Customer::where('email', $user->email)->first();

        if (! $customer) {
            abort(404, 'Müşteri kaydı bulunamadı.');
        }

        return $customer;
    }

    /**
     * Yetki kontrolü yapar, yetki yoksa 403 fırlatır.
     */
    protected function authorizeCustomerPermission(string $permission): void
    {
        $user = Auth::user();

        if (! $user->hasPermission($permission)) {
            abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }
    }

    /**
     * Yetki kontrolü yapıp müşteriyi döndürür.
     */
    protected function authorizeAndResolveCustomer(string $permission): Customer
    {
        $this->authorizeCustomerPermission($permission);

        return $this->resolveCustomer();
    }
}
