<?php

namespace App\Employee\Controllers\Web;

use App\Employee\Requests\StoreEmployeeRequest;
use App\Employee\Requests\UpdateEmployeeRequest;
use App\Employee\Services\EmployeeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService
    ) {
    }

    /**
     * Display a listing of employees.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'branch_id', 'position_id']);
        $employees = $this->employeeService->getPaginated($filters);
        $branches = \App\Models\Branch::where('status', 1)->orderBy('name')->get();
        $positions = \App\Models\Position::orderBy('name')->get();

        return view('admin.employees.index', compact('employees', 'branches', 'positions'));
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
}
