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

        $sort = $request->string('sort')->toString();
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'amount' => 'amount',
            'requested_date' => 'requested_date',
            'employee_id' => 'employee_id',
            'status' => 'status',
            'created_at' => 'created_at',
        ];
        if ($sort !== '' && \array_key_exists($sort, $sortableColumns)) {
            $query->orderBy($sortableColumns[$sort], $direction);
        } else {
            $query->latest('requested_date');
        }

        $advances = $query->paginate(25)->withQueryString();

        $stats = [
            'total' => Advance::count(),
            'pending' => Advance::where('status', 'pending')->count(),
            'approved' => Advance::whereIn('status', ['approved', 'paid'])->count(),
        ];

        return view('admin.advances.index', compact('advances', 'stats'));
    }

    /**
     * Toplu işlem: sil, onayla, reddet.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected' => ['required', 'array'],
            'selected.*' => ['integer', 'exists:advances,id'],
            'action' => ['required', 'string', 'in:delete,approve,reject'],
        ]);

        $ids = $validated['selected'];
        $user = Auth::user();

        if ($validated['action'] === 'delete') {
            Advance::whereIn('id', $ids)->delete();
            $message = 'Seçili avans talepleri silindi.';
        } elseif ($validated['action'] === 'approve') {
            Advance::whereIn('id', $ids)->where('status', 'pending')->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
            $message = 'Seçili avans talepleri onaylandı.';
        } else {
            Advance::whereIn('id', $ids)->where('status', 'pending')->update([
                'status' => 'rejected',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
            $message = 'Seçili avans talepleri reddedildi.';
        }

        return redirect()->route('admin.advances.index')->with('success', $message);
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
