<?php

namespace App\Employee\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    /**
     * İzin listesi.
     */
    public function index(Request $request): View
    {
        $query = Leave::with(['employee', 'approver']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->latest('start_date')->paginate(25);

        $stats = [
            'total' => Leave::count(),
            'pending' => Leave::where('status', 'pending')->count(),
            'approved' => Leave::where('status', 'approved')->count(),
        ];

        return view('admin.leaves.index', compact('leaves', 'stats'));
    }

    /**
     * Yeni izin formu.
     */
    public function create(): View
    {
        $employees = Employee::where('status', 1)->orderBy('first_name')->get();

        return view('admin.leaves.create', compact('employees'));
    }

    /**
     * İzin oluştur.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:annual,sick,unpaid,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:2000',
        ]);

        // Toplam gün sayısını hesapla
        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end = \Carbon\Carbon::parse($validated['end_date']);
        $validated['total_days'] = $start->diffInDays($end) + 1;

        Leave::create($validated);

        return redirect()
            ->route('admin.leaves.index')
            ->with('success', 'İzin talebi başarıyla oluşturuldu.');
    }

    /**
     * İzin onayla/reddet.
     */
    public function approve(Request $request, Leave $leave): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:1000',
        ]);

        $user = Auth::user();

        if ($validated['action'] === 'approve') {
            $leave->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
            $message = 'İzin talebi onaylandı.';
        } else {
            $leave->update([
                'status' => 'rejected',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);
            $message = 'İzin talebi reddedildi.';
        }

        return back()->with('success', $message);
    }
}
