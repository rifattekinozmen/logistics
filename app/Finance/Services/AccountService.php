<?php

namespace App\Finance\Services;

use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\Payment;

class AccountService
{
    public function createCreditForPayment(Payment $payment, Customer $customer): AccountTransaction
    {
        return AccountTransaction::create([
            'customer_id' => $customer->id,
            'payment_id' => $payment->id,
            'e_invoice_id' => null,
            'type' => AccountTransaction::TYPE_CREDIT,
            'amount' => $payment->amount,
            'balance_after' => null,
            'currency' => 'TRY',
            'description' => 'Ödeme #'.$payment->id.' için cari alacak kaydı',
            'transaction_date' => now(),
        ]);
    }
}

