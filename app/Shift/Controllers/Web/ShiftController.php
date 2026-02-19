<?php

namespace App\Shift\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    /**
     * Display a listing of shifts.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['employee_id', 'date_from', 'date_to', 'template_id']);
        $shifts = \App\Models\ShiftSchedule::query()
            ->with(['employee', 'template'])
            ->when($filters['employee_id'] ?? null, fn ($q, $employeeId) => $q->where('employee_id', $employeeId))
            ->when($filters['template_id'] ?? null, fn ($q, $templateId) => $q->where('shift_template_id', $templateId))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('shift_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('shift_date', '<=', $date))
            ->orderBy('shift_date', 'desc')
            ->paginate(25);

        $employees = \App\Models\Employee::where('status', 1)->orderBy('first_name')->get();
        $templates = \App\Models\ShiftTemplate::orderBy('name')->get();

        return view('admin.shifts.index', compact('shifts', 'employees', 'templates'));
    }

    /**
     * Display shift templates.
     */
    public function templates(): View
    {
        $templates = \App\Models\ShiftTemplate::orderBy('name')->paginate(25);

        return view('admin.shifts.templates', compact('templates'));
    }

    /**
     * Display shift planning.
     */
    public function planning(): View
    {
        $templates = \App\Models\ShiftTemplate::orderBy('name')->get();
        $employees = \App\Models\Employee::where('status', 1)->orderBy('first_name')->get();

        return view('admin.shifts.planning', compact('templates', 'employees'));
    }
}
