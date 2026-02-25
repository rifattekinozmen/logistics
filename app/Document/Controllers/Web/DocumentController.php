<?php

namespace App\Document\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['type', 'documentable_type', 'documentable_id', 'expiry_date_from', 'expiry_date_to', 'sort', 'direction']);
        $documents = \App\Models\Document::query()
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('category', $type))
            ->when($filters['documentable_type'] ?? null, fn ($q, $type) => $q->where('documentable_type', $type))
            ->when($filters['documentable_id'] ?? null, fn ($q, $id) => $q->where('documentable_id', $id))
            ->when($filters['expiry_date_from'] ?? null, fn ($q, $date) => $q->whereDate('valid_until', '>=', $date))
            ->when($filters['expiry_date_to'] ?? null, fn ($q, $date) => $q->whereDate('valid_until', '<=', $date))
            ->tap(function ($query) use ($filters) {
                $sort = $filters['sort'] ?? null;
                $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
                $sortableColumns = [
                    'name' => 'name',
                    'category' => 'category',
                    'documentable_type' => 'documentable_type',
                    'valid_until' => 'valid_until',
                    'created_at' => 'created_at',
                ];
                if ($sort !== null && \array_key_exists($sort, $sortableColumns)) {
                    $query->orderBy($sortableColumns[$sort], $direction);
                } else {
                    $query->orderBy('valid_until', 'asc');
                }
            })
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'total' => \App\Models\Document::count(),
        ];

        return view('admin.documents.index', compact('documents', 'stats'));
    }

    /**
     * Show the form for creating a new document.
     */
    public function create(): View
    {
        $vehicles = \App\Models\Vehicle::where('status', 1)->orderBy('plate')->get();
        $employees = \App\Models\Employee::where('status', 1)->orderBy('first_name')->orderBy('last_name')->get();
        $orders = \App\Models\Order::where('status', '!=', 'cancelled')->orderBy('id', 'desc')->get();

        return view('admin.documents.create', compact('vehicles', 'employees', 'orders'));
    }

    /**
     * Store a newly created document.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'documentable_type' => 'required|string',
            'documentable_id' => 'required|integer',
            'type' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'file_path' => 'required|string|max:1000',
            'expiry_date' => 'nullable|date',
            'status' => 'required|integer|in:0,1',
        ]);

        $document = \App\Models\Document::create([
            'documentable_type' => $validated['documentable_type'],
            'documentable_id' => $validated['documentable_id'],
            'category' => $validated['type'],
            'name' => $validated['name'],
            'file_path' => $validated['file_path'],
            'valid_until' => $validated['expiry_date'] ?? null,
        ]);

        return redirect()->route('admin.documents.show', $document)
            ->with('success', 'Belge başarıyla oluşturuldu.');
    }

    /**
     * Display the specified document.
     */
    public function show(int $id): View
    {
        $document = \App\Models\Document::with(['documentable'])->findOrFail($id);

        return view('admin.documents.show', compact('document'));
    }

    /**
     * Show the form for editing the specified document.
     */
    public function edit(int $id): View
    {
        $document = \App\Models\Document::findOrFail($id);

        return view('admin.documents.edit', compact('document'));
    }

    /**
     * Update the specified document.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $document = \App\Models\Document::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'file_path' => 'required|string|max:1000',
            'expiry_date' => 'nullable|date',
            'status' => 'required|integer|in:0,1',
        ]);

        $document->update([
            'category' => $validated['type'],
            'name' => $validated['name'],
            'file_path' => $validated['file_path'],
            'valid_until' => $validated['expiry_date'] ?? null,
        ]);

        return redirect()->route('admin.documents.show', $document)
            ->with('success', 'Belge başarıyla güncellendi.');
    }

    /**
     * Remove the specified document.
     */
    public function destroy(int $id): RedirectResponse
    {
        $document = \App\Models\Document::findOrFail($id);
        $document->delete();

        return redirect()->route('admin.documents.index')
            ->with('success', 'Belge başarıyla silindi.');
    }

    /**
     * Apply bulk actions to documents.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected' => ['required', 'array'],
            'selected.*' => ['integer', 'exists:documents,id'],
            'action' => ['required', 'string', 'in:delete'],
        ]);

        $ids = $validated['selected'];

        if ($validated['action'] === 'delete') {
            \App\Models\Document::whereIn('id', $ids)->delete();
        }

        return redirect()->route('admin.documents.index')
            ->with('success', 'Seçili belgeler için toplu işlem uygulandı.');
    }
}
