<?php

namespace App\Finance\Controllers\Web;

use App\Core\Services\ExportService;
use App\Http\Controllers\Controller;
use App\Models\Customer;
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
        $filters = $request->only(['type', 'status', 'due_date_from', 'due_date_to', 'company_id']);

        if ($request->has('export')) {
            return $this->export($filters, $request->get('export'));
        }

        $payments = $this->buildQuery($filters)->paginate(25);
        $payments->getCollection()->loadMorph('related', [
            Customer::class => ['businessPartner.company'],
        ]);

        return view('admin.payments.index', compact('payments'));
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

        return $query->orderBy('due_date', 'asc');
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(): View
    {
        $companies = \App\Models\Company::active()->orderBy('name')->get();

        return view('admin.payments.create', compact('companies'));
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
}
