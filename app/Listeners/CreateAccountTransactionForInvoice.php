<?php

namespace App\Listeners;

use App\Events\InvoiceIssued;
use App\Models\AccountTransaction;

class CreateAccountTransactionForInvoice
{
    public function handle(InvoiceIssued $event): void
    {
        $order = $event->order->fresh(['customer']);

        if (! $order || ! $order->customer) {
            return;
        }

        $amount = (float) ($order->freight_price ?? 0);

        if ($amount <= 0) {
            return;
        }

        AccountTransaction::create([
            'customer_id' => $order->customer_id,
            'payment_id' => null,
            'e_invoice_id' => null,
            'type' => AccountTransaction::TYPE_DEBIT,
            'amount' => $amount,
            'balance_after' => null,
            'currency' => 'TRY',
            'description' => 'Sipariş #'.$order->order_number.' için otomatik cari borç kaydı',
            'transaction_date' => now(),
        ]);
    }
}
