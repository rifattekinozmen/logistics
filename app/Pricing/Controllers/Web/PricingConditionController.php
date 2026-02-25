<?php

namespace App\Pricing\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Pricing\Models\PricingCondition;
use App\Pricing\Requests\StorePricingConditionRequest;
use App\Pricing\Requests\UpdatePricingConditionRequest;
use App\Pricing\Services\PricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingConditionController extends Controller
{
    public function __construct(protected PricingService $pricingService) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['condition_type', 'status', 'company_id', 'sort', 'direction']);
        $conditions = $this->pricingService->getPaginated($filters);

        $stats = [
            'total' => PricingCondition::count(),
            'active' => PricingCondition::where('status', 1)->count(),
        ];

        return view('admin.pricing-conditions.index', compact('conditions', 'stats'));
    }

    public function create(): View
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('admin.pricing-conditions.create', compact('companies'));
    }

    public function store(StorePricingConditionRequest $request): RedirectResponse
    {
        $condition = PricingCondition::create($request->validated());

        return redirect()->route('admin.pricing-conditions.index')
            ->with('success', 'Fiyatlandırma koşulu başarıyla oluşturuldu.');
    }

    public function edit(PricingCondition $pricingCondition): View
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('admin.pricing-conditions.edit', [
            'condition' => $pricingCondition,
            'companies' => $companies,
        ]);
    }

    public function update(UpdatePricingConditionRequest $request, PricingCondition $pricingCondition): RedirectResponse
    {
        $pricingCondition->update($request->validated());

        return redirect()->route('admin.pricing-conditions.index')
            ->with('success', 'Fiyatlandırma koşulu başarıyla güncellendi.');
    }

    public function destroy(PricingCondition $pricingCondition): RedirectResponse
    {
        $pricingCondition->delete();

        return redirect()->route('admin.pricing-conditions.index')
            ->with('success', 'Fiyatlandırma koşulu başarıyla silindi.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected' => ['required', 'array'],
            'selected.*' => ['integer', 'exists:pricing_conditions,id'],
            'action' => ['required', 'string', 'in:delete,activate,deactivate'],
        ]);

        $ids = $validated['selected'];

        if ($validated['action'] === 'delete') {
            PricingCondition::whereIn('id', $ids)->delete();
        }

        if ($validated['action'] === 'activate') {
            PricingCondition::whereIn('id', $ids)->update(['status' => 1]);
        }

        if ($validated['action'] === 'deactivate') {
            PricingCondition::whereIn('id', $ids)->update(['status' => 0]);
        }

        return redirect()->route('admin.pricing-conditions.index')
            ->with('success', 'Seçili fiyatlandırma koşulları için toplu işlem uygulandı.');
    }
}
