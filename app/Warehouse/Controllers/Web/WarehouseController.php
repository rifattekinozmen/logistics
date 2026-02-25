<?php

namespace App\Warehouse\Controllers\Web;

use App\Core\Services\ExportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WarehouseController extends Controller
{
    public function __construct(
        protected ExportService $exportService
    ) {}

    /**
     * Display a listing of warehouses.
     */
    public function index(Request $request): View|StreamedResponse|Response
    {
        $companyId = session('active_company_id') ?? Auth::user()?->activeCompany()?->id;

        if (! $companyId) {
            return redirect()->route('admin.companies.select')
                ->with('error', 'Aktif firma seçmeden depo listesini görüntüleyemezsiniz.');
        }

        $filters = $request->only(['status', 'branch_id', 'search', 'sort', 'direction']);

        if ($request->has('export')) {
            return $this->export($companyId, $filters, $request->get('export'));
        }

        $warehouses = \App\Models\Warehouse::query()
            ->where('company_id', $companyId)
            ->with(['branch'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->tap(function ($query) use ($filters) {
                $sort = $filters['sort'] ?? null;
                $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

                $sortableColumns = [
                    'name' => 'name',
                    'code' => 'code',
                    'branch_id' => 'branch_id',
                    'address' => 'address',
                    'status' => 'status',
                    'created_at' => 'created_at',
                ];

                if ($sort && \array_key_exists($sort, $sortableColumns)) {
                    $query->orderBy($sortableColumns[$sort], $direction);
                } else {
                    $query->orderBy('name');
                }
            })
            ->paginate(25)
            ->withQueryString();

        $branches = \App\Models\Branch::where('company_id', $companyId)->where('status', 1)->orderBy('name')->get();

        $stats = [
            'total' => \App\Models\Warehouse::where('company_id', $companyId)->count(),
            'active' => \App\Models\Warehouse::where('company_id', $companyId)->where('status', 1)->count(),
        ];

        return view('admin.warehouses.index', compact('warehouses', 'branches', 'stats'));
    }

    /**
     * Depo listesini CSV veya XML olarak dışa aktar.
     */
    protected function export(int $companyId, array $filters, string $format): StreamedResponse|Response
    {
        $warehouses = \App\Models\Warehouse::query()
            ->where('company_id', $companyId)
            ->with(['branch'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        $headers = ['Depo Adı', 'Kod', 'Şube', 'Adres', 'Durum', 'Oluşturulma'];

        $rows = $warehouses->map(fn ($w) => [
            $w->name,
            $w->code ?? '-',
            $w->branch?->name ?? '-',
            $w->address ?? '-',
            $w->status ? 'Aktif' : 'Pasif',
            $w->created_at->format('d.m.Y H:i'),
        ])->all();

        $filename = 'depolar';

        return $format === 'xml'
            ? $this->exportService->xml($headers, $rows, $filename, 'warehouses', 'warehouse')
            : $this->exportService->csv($headers, $rows, $filename);
    }

    /**
     * Show the form for creating a new warehouse.
     */
    public function create(): View
    {
        $companyId = session('active_company_id') ?? Auth::user()?->activeCompany()?->id;
        $branches = \App\Models\Branch::where('company_id', $companyId)->where('status', 1)->orderBy('name')->get();

        return view('admin.warehouses.create', compact('branches'));
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(Request $request): RedirectResponse
    {
        $companyId = session('active_company_id') ?? Auth::user()?->activeCompany()?->id;

        if (! $companyId) {
            return back()->withErrors(['company' => 'Aktif firma gerekli.']);
        }

        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code',
            'address' => 'nullable|string|max:1000',
            'status' => 'required|integer|in:0,1',
        ]);

        $validated['company_id'] = $companyId;
        $validated['warehouse_type'] = $validated['warehouse_type'] ?? 'main';
        $validated['code'] = $validated['code'] ?? 'WH-'.strtoupper(\Illuminate\Support\Str::random(6));

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
        $companyId = $warehouse->company_id;
        $branches = \App\Models\Branch::where('company_id', $companyId)->where('status', 1)->orderBy('name')->get();

        return view('admin.warehouses.edit', compact('warehouse', 'branches'));
    }

    /**
     * Update the specified warehouse.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $warehouse = \App\Models\Warehouse::findOrFail($id);

        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code,'.$warehouse->id,
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

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected' => ['required', 'array'],
            'selected.*' => ['integer', 'exists:warehouses,id'],
            'action' => ['required', 'string', 'in:delete,activate,deactivate'],
        ]);

        $ids = $validated['selected'];

        if ($validated['action'] === 'delete') {
            \App\Models\Warehouse::whereIn('id', $ids)->delete();
        }

        if ($validated['action'] === 'activate') {
            \App\Models\Warehouse::whereIn('id', $ids)->update(['status' => 1]);
        }

        if ($validated['action'] === 'deactivate') {
            \App\Models\Warehouse::whereIn('id', $ids)->update(['status' => 0]);
        }

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Seçili depolar için toplu işlem uygulandı.');
    }
}
