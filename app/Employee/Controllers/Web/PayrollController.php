<?php

namespace App\Employee\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PayrollController extends Controller
{
    /**
     * Bordro listesi.
     */
    public function index(Request $request): View
    {
        $query = Payroll::with(['employee', 'creator']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('period_start')) {
            $query->where('period_start', '>=', $request->period_start);
        }

        if ($request->filled('period_end')) {
            $query->where('period_end', '<=', $request->period_end);
        }

        $sort = $request->string('sort')->toString();
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'payroll_number' => 'payroll_number',
            'period_start' => 'period_start',
            'period_end' => 'period_end',
            'net_salary' => 'net_salary',
            'status' => 'status',
            'created_at' => 'created_at',
        ];
        if ($sort !== '' && \array_key_exists($sort, $sortableColumns)) {
            $query->orderBy($sortableColumns[$sort], $direction);
        } else {
            $query->latest('period_start');
        }

        $payrolls = $query->paginate(25)->withQueryString();

        $stats = [
            'total' => Payroll::count(),
            'this_month' => Payroll::whereMonth('period_start', now()->month)
                ->whereYear('period_start', now()->year)
                ->count(),
        ];

        return view('admin.payrolls.index', compact('payrolls', 'stats'));
    }

    /**
     * Toplu işlem: sil.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected' => ['required', 'array'],
            'selected.*' => ['integer', 'exists:payrolls,id'],
            'action' => ['required', 'string', 'in:delete'],
        ]);

        if ($validated['action'] === 'delete') {
            Payroll::whereIn('id', $validated['selected'])->delete();
        }

        return redirect()->route('admin.payrolls.index')
            ->with('success', 'Seçili bordrolar için toplu işlem uygulandı.');
    }

    /**
     * Yeni bordro formu.
     */
    public function create(): View
    {
        $employees = Employee::where('status', 1)->orderBy('first_name')->get();

        return view('admin.payrolls.create', compact('employees'));
    }

    /**
     * Bordro oluştur.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'base_salary' => 'required|numeric|min:0',
            'overtime_amount' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'social_security' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        // Net maaşı hesapla
        $gross = $validated['base_salary']
            + ($validated['overtime_amount'] ?? 0)
            + ($validated['bonus'] ?? 0);

        $totalDeduction = ($validated['deduction'] ?? 0)
            + ($validated['tax'] ?? 0)
            + ($validated['social_security'] ?? 0);

        $validated['net_salary'] = $gross - $totalDeduction;
        $validated['payroll_number'] = $this->generatePayrollNumber();
        $validated['created_by'] = Auth::id();

        Payroll::create($validated);

        return redirect()
            ->route('admin.payrolls.index')
            ->with('success', 'Bordro başarıyla oluşturuldu.');
    }

    /**
     * Bordro detayı.
     */
    public function show(Payroll $payroll): View
    {
        $payroll->load(['employee', 'creator']);

        return view('admin.payrolls.show', compact('payroll'));
    }

    /**
     * Bordro belgesi (yazdırma / PDF için uygun HTML).
     * İleride DomPDF vb. ile PDF çıktı alınabilir.
     */
    public function pdf(Payroll $payroll): View
    {
        $payroll->load(['employee']);

        return view('admin.payrolls.pdf', compact('payroll'));
    }

    /**
     * Bordro numarası oluştur.
     */
    protected function generatePayrollNumber(): string
    {
        do {
            $number = 'PRL-'.date('Ymd').'-'.strtoupper(Str::random(4));
        } while (Payroll::where('payroll_number', $number)->exists());

        return $number;
    }
}
