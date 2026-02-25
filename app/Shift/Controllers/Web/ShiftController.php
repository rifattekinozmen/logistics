<?php

namespace App\Shift\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    /**
     * Display a listing of shifts.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['employee_id', 'date_from', 'date_to', 'template_id', 'sort', 'direction']);
        $query = \App\Models\ShiftAssignment::query()
            ->with(['employee', 'schedule.template'])
            ->when($filters['employee_id'] ?? null, fn ($q, $employeeId) => $q->where('employee_id', $employeeId))
            ->when($filters['template_id'] ?? null, fn ($q, $templateId) => $q->whereHas('schedule', fn ($s) => $s->where('template_id', $templateId)))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('shift_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('shift_date', '<=', $date));

        $sort = $filters['sort'] ?? null;
        $direction = (isset($filters['direction']) && $filters['direction'] === 'desc') ? 'desc' : 'asc';
        $sortableColumns = [
            'shift_date' => 'shift_date',
            'start_time' => 'start_time',
            'created_at' => 'created_at',
        ];
        if ($sort === 'template') {
            $query->leftJoin('shift_schedules', 'shift_assignments.schedule_id', '=', 'shift_schedules.id')
                ->leftJoin('shift_templates', 'shift_schedules.template_id', '=', 'shift_templates.id')
                ->orderBy('shift_templates.name', $direction)
                ->select('shift_assignments.*');
        } elseif ($sort !== null && \array_key_exists($sort, $sortableColumns)) {
            $query->orderBy($sortableColumns[$sort], $direction);
        } else {
            $query->orderBy('shift_date', 'desc');
        }

        $shifts = $query->paginate(25)->withQueryString();

        $employees = \App\Models\Employee::where('status', 1)->orderBy('first_name')->get();
        $templates = \App\Models\ShiftTemplate::orderBy('name')->get();

        $stats = [
            'total' => \App\Models\ShiftAssignment::count(),
        ];

        return view('admin.shifts.index', compact('shifts', 'employees', 'templates', 'stats'));
    }

    /**
     * Apply bulk actions to shifts.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected' => ['required', 'array'],
            'selected.*' => ['integer', 'exists:shift_assignments,id'],
            'action' => ['required', 'string', 'in:delete'],
        ]);

        if ($validated['action'] === 'delete') {
            \App\Models\ShiftAssignment::whereIn('id', $validated['selected'])->delete();
        }

        return redirect()->route('admin.shifts.index')
            ->with('success', 'Seçili vardiyalar için toplu işlem uygulandı.');
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
