<?php

namespace App\Customer\Controllers\Web;

use App\Customer\Concerns\ResolvesCustomerFromUser;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    use ResolvesCustomerFromUser;

    public function invoices(Request $request): View
    {
        $this->authorizeCustomerPermission('customer.portal.invoices.view');
        $customer = $this->resolveCustomer();

        $orderIds = Order::where('customer_id', $customer->id)->pluck('id');

        $query = Document::where('documentable_type', Order::class)
            ->whereIn('documentable_id', $orderIds)
            ->where('category', 'invoice');

        if ($request->filled('order_id')) {
            $query->where('documentable_id', $request->order_id);
        }

        $invoices = $query->latest()->paginate(20);

        return view('customer.invoices.index', compact('invoices'));
    }

    public function downloadInvoice(Document $document): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorizeCustomerPermission('customer.portal.invoices.download');
        $customer = $this->resolveCustomer();

        if ($document->documentable_type === Order::class) {
            $order = Order::find($document->documentable_id);
            if (! $order || $order->customer_id !== $customer->id || $document->category !== 'invoice') {
                abort(403, 'Bu faturaya erişim yetkiniz yok.');
            }
        }

        if (! Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors(['document' => 'Fatura dosyası bulunamadı.']);
        }

        return Storage::disk('public')->download($document->file_path, $document->name);
    }
}
