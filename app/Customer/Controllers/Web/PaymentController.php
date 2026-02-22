<?php

namespace App\Customer\Controllers\Web;

use App\Customer\Concerns\ResolvesCustomerFromUser;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ResolvesCustomerFromUser;

    public function payments(Request $request): View
    {
        $this->authorizeCustomerPermission('customer.portal.payments.view');
        $customer = $this->resolveCustomer();

        $query = Payment::where('related_type', Customer::class)
            ->where('related_id', $customer->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('due_date_from')) {
            $query->whereDate('due_date', '>=', $request->due_date_from);
        }

        if ($request->filled('due_date_to')) {
            $query->whereDate('due_date', '<=', $request->due_date_to);
        }

        $payments = $query->latest('due_date')->paginate(20);

        $stats = [
            'total_pending' => Payment::where('related_type', Customer::class)
                ->where('related_id', $customer->id)
                ->where('status', 0)
                ->sum('amount'),
            'total_paid' => Payment::where('related_type', Customer::class)
                ->where('related_id', $customer->id)
                ->where('status', 1)
                ->sum('amount'),
            'overdue_count' => Payment::where('related_type', Customer::class)
                ->where('related_id', $customer->id)
                ->where('status', 0)
                ->whereDate('due_date', '<', now())
                ->count(),
        ];

        return view('customer.payments.index', compact('payments', 'stats'));
    }

    public function showPayment(Payment $payment): View
    {
        $this->authorizeCustomerPermission('customer.portal.payments.view');
        $customer = $this->resolveCustomer();

        if ($payment->related_type !== Customer::class || $payment->related_id !== $customer->id) {
            abort(403, 'Bu ödemeye erişim yetkiniz yok.');
        }

        return view('customer.payments.show', compact('payment'));
    }
}
