<?php

namespace App\Finance\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['type', 'status', 'due_date_from', 'due_date_to', 'company_id']);
        $payments = \App\Models\Payment::query()
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['due_date_from'] ?? null, fn ($q, $date) => $q->whereDate('due_date', '>=', $date))
            ->when($filters['due_date_to'] ?? null, fn ($q, $date) => $q->whereDate('due_date', '<=', $date))
            ->when($filters['company_id'] ?? null, fn ($q, $companyId) => $q->where('company_id', $companyId))
            ->orderBy('due_date', 'asc')
            ->paginate(25);

        return view('admin.payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(): View
    {
        $companies = \App\Models\Company::where('status', 1)->orderBy('name')->get();

        return view('admin.payments.create', compact('companies'));
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'type' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|string|max:50',
        ]);

        $payment = \App\Models\Payment::create($validated);

        return redirect()->route('admin.payments.show', $payment)
            ->with('success', 'Ödeme başarıyla oluşturuldu.');
    }

    /**
     * Display the specified payment.
     */
    public function show(int $id): View
    {
        $payment = \App\Models\Payment::with(['company'])->findOrFail($id);

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(int $id): View
    {
        $payment = \App\Models\Payment::findOrFail($id);
        $companies = \App\Models\Company::where('status', 1)->orderBy('name')->get();

        return view('admin.payments.edit', compact('payment', 'companies'));
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $payment = \App\Models\Payment::findOrFail($id);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'type' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|string|max:50',
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
