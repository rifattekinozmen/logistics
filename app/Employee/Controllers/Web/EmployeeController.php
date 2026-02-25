<?php

namespace App\Employee\Controllers\Web;

use App\Core\Services\ExportService;
use App\Employee\Requests\StoreEmployeeRequest;
use App\Employee\Requests\UpdateEmployeeRequest;
use App\Employee\Services\EmployeeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService,
        protected ExportService $exportService
    ) {}

    /**
     * Display a listing of employees.
     */
    public function index(Request $request): View|StreamedResponse|Response
    {
        $filters = $request->only(['status', 'branch_id', 'position_id', 'sort', 'direction']);

        if ($request->has('export')) {
            return $this->export($filters, $request->get('export'));
        }

        $employees = $this->employeeService->getPaginated($filters)->withQueryString();
        $branches = \App\Models\Branch::where('status', 1)->orderBy('name')->get();
        $positions = \App\Models\Position::orderBy('name')->get();

        return view('admin.employees.index', compact('employees', 'branches', 'positions'));
    }

    /**
     * Personel listesini CSV veya XML olarak dışa aktar.
     */
    protected function export(array $filters, string $format): StreamedResponse|Response
    {
        $employees = $this->employeeService->getForExport($filters);

        $headers = ['Personel No', 'Ad Soyad', 'E-posta', 'Telefon', 'Şube', 'Pozisyon', 'Durum', 'Oluşturulma'];

        $statusLabels = [0 => 'Pasif', 1 => 'Aktif', 2 => 'İzinli'];

        $rows = $employees->map(fn ($e) => [
            $e->employee_number ?? '-',
            trim(($e->first_name ?? '').' '.($e->last_name ?? '')) ?: '-',
            $e->user?->email ?? $e->email ?? '-',
            $e->phone ?? '-',
            $e->branch?->name ?? '-',
            $e->position?->name ?? '-',
            $statusLabels[$e->status] ?? (string) $e->status,
            $e->created_at->format('d.m.Y H:i'),
        ])->all();

        $filename = 'personel';

        return $format === 'xml'
            ? $this->exportService->xml($headers, $rows, $filename, 'employees', 'employee')
            : $this->exportService->csv($headers, $rows, $filename);
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(): View
    {
        $branches = \App\Models\Branch::where('status', 1)->orderBy('name')->get();
        $positions = \App\Models\Position::orderBy('name')->get();

        return view('admin.employees.create', compact('branches', 'positions'));
    }

    /**
     * Store a newly created employee.
     */
    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = $this->employeeService->create($request->validated());

        return redirect()->route('admin.employees.show', $employee)
            ->with('success', 'Personel başarıyla oluşturuldu.');
    }

    /**
     * Display the specified employee.
     */
    public function show(int $id): View
    {
        $employee = \App\Models\Employee::with(['user', 'branch', 'position', 'attendance'])->findOrFail($id);

        return view('admin.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(int $id): View
    {
        $employee = \App\Models\Employee::findOrFail($id);
        $branches = \App\Models\Branch::where('status', 1)->orderBy('name')->get();
        $positions = \App\Models\Position::orderBy('name')->get();

        return view('admin.employees.edit', compact('employee', 'branches', 'positions'));
    }

    /**
     * Update the specified employee.
     */
    public function update(UpdateEmployeeRequest $request, int $id): RedirectResponse
    {
        $employee = \App\Models\Employee::findOrFail($id);

        $this->employeeService->update($employee, $request->validated());

        return redirect()->route('admin.employees.show', $employee)
            ->with('success', 'Personel başarıyla güncellendi.');
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(int $id): RedirectResponse
    {
        $employee = \App\Models\Employee::findOrFail($id);
        $employee->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', 'Personel başarıyla silindi.');
    }

    /**
     * Apply bulk actions to employees.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected' => ['required', 'array'],
            'selected.*' => ['integer', 'exists:employees,id'],
            'action' => ['required', 'string', 'in:delete,activate,deactivate'],
        ]);

        $ids = $validated['selected'];

        if ($validated['action'] === 'delete') {
            \App\Models\Employee::whereIn('id', $ids)->delete();
        }
        if ($validated['action'] === 'activate') {
            \App\Models\Employee::whereIn('id', $ids)->update(['status' => 1]);
        }
        if ($validated['action'] === 'deactivate') {
            \App\Models\Employee::whereIn('id', $ids)->update(['status' => 0]);
        }

        return redirect()->route('admin.employees.index')
            ->with('success', 'Seçili personel için toplu işlem uygulandı.');
    }
}
