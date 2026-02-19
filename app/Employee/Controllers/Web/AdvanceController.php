<?php

namespace App\Employee\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Advance;
use App\Models\Employee;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvanceController extends Controller
{
    /**
     * Avans listesi.
     */
    public function index(Request $request): View
    {
        $query = Advance::with(['employee', 'approver']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $advances = $query->latest('requested_date')->paginate(25);

        return view('admin.advances.index', compact('advances'));
    }

    /**
     * Yeni avans formu.
     */
    public function create(): View
    {
        $employees = Employee::where('status', 1)->orderBy('first_name')->get();

        return view('admin.advances.create', compact('employees'));
    }

    /**
     * Avans oluştur.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:2000',
        ]);

        $validated['requested_date'] = now()->toDateString();

        Advance::create($validated);

        return redirect()
            ->route('admin.advances.index')
            ->with('success', 'Avans talebi başarıyla oluşturuldu.');
    }

    /**
     * Avans onayla/reddet.
     */
    public function approve(Request $request, Advance $advance): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:1000',
        ]);

        $user = Auth::user();

        if ($validated['action'] === 'approve') {
            $advance->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
            $message = 'Avans talebi onaylandı.';
        } else {
            $advance->update([
                'status' => 'rejected',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);
            $message = 'Avans talebi reddedildi.';
        }

        return back()->with('success', $message);
    }
}
