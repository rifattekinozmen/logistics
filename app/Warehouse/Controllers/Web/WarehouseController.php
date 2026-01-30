<?php

namespace App\Warehouse\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'branch_id', 'search']);
        $warehouses = \App\Models\Warehouse::query()
            ->with(['branch'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(25);

        $branches = \App\Models\Branch::where('status', 1)->orderBy('name')->get();

        return view('admin.warehouses.index', compact('warehouses', 'branches'));
    }

    /**
     * Show the form for creating a new warehouse.
     */
    public function create(): View
    {
        $branches = \App\Models\Branch::where('status', 1)->orderBy('name')->get();

        return view('admin.warehouses.create', compact('branches'));
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code',
            'address' => 'nullable|string|max:1000',
            'status' => 'required|integer|in:0,1',
        ]);

        $warehouse = \App\Models\Warehouse::create($validated);

        return redirect()->route('admin.warehouses.show', $warehouse)
            ->with('success', 'Depo başarıyla oluşturuldu.');
    }

    /**
     * Display the specified warehouse.
     */
    public function show(int $id): View
    {
        $warehouse = \App\Models\Warehouse::with(['branch', 'locations', 'stocks'])->findOrFail($id);

        return view('admin.warehouses.show', compact('warehouse'));
    }

    /**
     * Show the form for editing the specified warehouse.
     */
    public function edit(int $id): View
    {
        $warehouse = \App\Models\Warehouse::findOrFail($id);
        $branches = \App\Models\Branch::where('status', 1)->orderBy('name')->get();

        return view('admin.warehouses.edit', compact('warehouse', 'branches'));
    }

    /**
     * Update the specified warehouse.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $warehouse = \App\Models\Warehouse::findOrFail($id);

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'address' => 'nullable|string|max:1000',
            'status' => 'required|integer|in:0,1',
        ]);

        $warehouse->update($validated);

        return redirect()->route('admin.warehouses.show', $warehouse)
            ->with('success', 'Depo başarıyla güncellendi.');
    }

    /**
     * Remove the specified warehouse.
     */
    public function destroy(int $id): RedirectResponse
    {
        $warehouse = \App\Models\Warehouse::findOrFail($id);
        $warehouse->delete();

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Depo başarıyla silindi.');
    }
}
