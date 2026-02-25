<?php

namespace App\Finance\Controllers\Web;

use App\Core\Services\ExportService;
use App\Events\OrderPaid;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected ExportService $exportService
    ) {}

    /**
     * Display a listing of payments.
     */
    public function index(Request $request): View|StreamedResponse|Response
    {
        $filters = $request->only(['type', 'status', 'due_date_from', 'due_date_to', 'company_id', 'sort', 'direction']);

        if ($request->has('export')) {
            return $this->export($filters, $request->get('export'));
        }

        $payments = $this->buildQuery($filters)->paginate(25)->withQueryString();
        $payments->getCollection()->loadMorph('related', [
            Customer::class => ['businessPartner.company'],
        ]);

        $stats = [
            'total_amount' => (float) \App\Models\Payment::sum('amount'),
            'pending' => \App\Models\Payment::where('status', \App\Models\Payment::STATUS_PENDING)->count(),
            'paid' => \App\Models\Payment::where('status', \App\Models\Payment::STATUS_PAID)->count(),
            'overdue' => \App\Models\Payment::where('status', \App\Models\Payment::STATUS_OVERDUE)->count(),
        ];

        return view('admin.payments.index', compact('payments', 'stats'));
    }

    /**
     * Ödeme listesini CSV veya XML olarak dışa aktar.
     */
    protected function export(array $filters, string $format): StreamedResponse|Response
    {
        $payments = $this->buildQuery($filters)->with('related')->get();

        $headers = ['İlişkili', 'Tür', 'Tutar', 'Vade Tarihi', 'Ödeme Tarihi', 'Durum', 'Oluşturulma'];

        $statusLabels = [0 => 'Beklemede', 1 => 'Ödendi', 2 => 'Gecikmiş', 3 => 'İptal'];

        $rows = $payments->map(fn ($p) => [
            $p->related ? (method_exists($p->related, 'name') ? $p->related->name : class_basename($p->related_type)) : '-',
            $p->payment_type,
            (string) $p->amount,
            $p->due_date?->format('d.m.Y') ?? '-',
            $p->paid_date?->format('d.m.Y') ?? '-',
            $statusLabels[$p->status] ?? (string) $p->status,
            $p->created_at->format('d.m.Y H:i'),
        ])->all();

        $filename = 'odemeler';

        return $format === 'xml'
            ? $this->exportService->xml($headers, $rows, $filename, 'payments', 'payment')
            : $this->exportService->csv($headers, $rows, $filename);
    }

    protected function buildQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = \App\Models\Payment::query()->with('related');
        $query->when($filters['type'] ?? null, fn ($q, $type) => $q->where('payment_type', $type));
        $query->when($filters['status'] ?? null, function ($q, $status) {
            $statusMap = ['pending' => 0, 'paid' => 1, 'overdue' => 2, 'cancelled' => 3];

            return $q->where('status', $statusMap[$status] ?? $status);
        });
        $query->when($filters['due_date_from'] ?? null, fn ($q, $date) => $q->whereDate('due_date', '>=', $date));
        $query->when($filters['due_date_to'] ?? null, fn ($q, $date) => $q->whereDate('due_date', '<=', $date));

        $query->tap(function ($q) use ($filters) {
            $sort = $filters['sort'] ?? null;
            $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            $sortableColumns = [
                'amount' => 'amount',
                'due_date' => 'due_date',
                'status' => 'status',
                'payment_type' => 'payment_type',
                'created_at' => 'created_at',
            ];
            if ($sort !== null && \array_key_exists($sort, $sortableColumns)) {
                $q->orderBy($sortableColumns[$sort], $direction);
            } else {
                $q->orderBy('due_date', 'asc');
            }
        });

        return $query;
    }

    /**
     * Apply bulk actions to payments.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected' => ['required', 'array'],
            'selected.*' => ['integer', 'exists:payments,id'],
            'action' => ['required', 'string', 'in:delete'],
        ]);

        $ids = $validated['selected'];

        if ($validated['action'] === 'delete') {
            \App\Models\Payment::whereIn('id', $ids)->delete();
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Seçili ödemeler için toplu işlem uygulandı.');
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request): View
    {
        $customerId = $request->integer('customer_id');
        $orderId = $request->integer('from_order');
        $order = $orderId ? Order::with('customer')->find($orderId) : null;
        $customers = Customer::where('status', 1)->orderBy('name')->get();
        $selectedCustomer = $customerId ? Customer::find($customerId) : ($order?->customer ?? $customers->first());

        return view('admin.payments.create', compact('customers', 'selectedCustomer', 'order'));
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'related_type' => 'required|string|in:'.implode(',', [\App\Models\Customer::class]),
            'related_id' => 'required|integer|exists:customers,id',
            'payment_type' => 'required|string|in:incoming,outgoing',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|integer|in:0,1,2,3',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['created_by'] = $request->user()?->id;
        $payment = \App\Models\Payment::create($validated);

        if ((int) $payment->status === Payment::STATUS_PAID && $payment->related_type === Customer::class) {
            $this->dispatchOrderPaidForCustomer((int) $payment->related_id);
        }

        $orderId = $request->integer('from_order');
        if ($orderId) {
            return redirect()->route('admin.orders.show', $orderId)
                ->with('success', 'Ödeme başarıyla oluşturuldu. Sevkiyat oluşturabilirsiniz.');
        }

        return redirect()->route('admin.payments.show', $payment)
            ->with('success', 'Ödeme başarıyla oluşturuldu.');
    }

    /**
     * Display the specified payment.
     */
    public function show(int $id): View
    {
        $payment = \App\Models\Payment::with('related')->findOrFail($id);
        $payment->loadMorph('related', [
            Customer::class => ['businessPartner.company'],
        ]);

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(int $id): View
    {
        $payment = \App\Models\Payment::findOrFail($id);
        $companies = \App\Models\Company::active()->orderBy('name')->get();

        return view('admin.payments.edit', compact('payment', 'companies'));
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $payment = \App\Models\Payment::findOrFail($id);
        $wasPaid = (int) $payment->status === Payment::STATUS_PAID;

        $validated = $request->validate([
            'related_type' => 'required|string|in:'.implode(',', [\App\Models\Customer::class]),
            'related_id' => 'required|integer|exists:customers,id',
            'payment_type' => 'required|string|in:incoming,outgoing',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|integer|in:0,1,2,3',
            'notes' => 'nullable|string|max:1000',
        ]);

        $payment->update($validated);

        if (! $wasPaid && (int) $payment->status === Payment::STATUS_PAID && $payment->related_type === Customer::class) {
            $this->dispatchOrderPaidForCustomer((int) $payment->related_id);
        }

        return redirect()->route('admin.payments.show', $payment)
            ->with('success', 'Ödeme başarıyla güncellendi.');
    }

    /**
     * Remove the specified payment.
     */
    public function destroy(int $id): RedirectResponse
    {
        $payment = \App\Models\Payment::findOrFail($id);
        $payment->delete();

        return redirect()->route('admin.payments.index')
            ->with('success', 'Ödeme başarıyla silindi.');
    }

    protected function dispatchOrderPaidForCustomer(int $customerId): void
    {
        $order = Order::query()
            ->where('customer_id', $customerId)
            ->whereIn('status', ['pending', 'planned'])
            ->latest('id')
            ->first();

        if ($order) {
            event(new OrderPaid($order));
        }
    }
}
