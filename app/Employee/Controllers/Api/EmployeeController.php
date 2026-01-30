<?php

namespace App\Employee\Controllers\Api;

use App\Employee\Services\EmployeeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService
    ) {
    }

    /**
     * Display a listing of employees.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'branch_id', 'position_id']);
        $employees = $this->employeeService->getPaginated($filters);

        return response()->json($employees);
    }

    /**
     * Store a newly created employee.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'position_id' => 'nullable|exists:positions,id',
            'employee_number' => 'nullable|string|max:50|unique:employees,employee_number',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'salary' => 'nullable|numeric|min:0',
            'hire_date' => 'required|date',
            'status' => 'required|integer|in:0,1,2',
        ]);

        $employee = $this->employeeService->create($validated);

        return response()->json($employee, 201);
    }

    /**
     * Display the specified employee.
     */
    public function show(int $id): JsonResponse
    {
        $employee = \App\Models\Employee::with(['user', 'branch', 'position'])->findOrFail($id);

        return response()->json($employee);
    }

    /**
     * Update the specified employee.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $employee = \App\Models\Employee::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'position_id' => 'nullable|exists:positions,id',
            'employee_number' => 'nullable|string|max:50|unique:employees,employee_number,'.$employee->id,
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'salary' => 'nullable|numeric|min:0',
            'hire_date' => 'required|date',
            'status' => 'required|integer|in:0,1,2',
        ]);

        $employee = $this->employeeService->update($employee, $validated);

        return response()->json($employee);
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(int $id): JsonResponse
    {
        $employee = \App\Models\Employee::findOrFail($id);
        $employee->delete();

        return response()->json(['message' => 'Personel başarıyla silindi.'], 200);
    }
}
