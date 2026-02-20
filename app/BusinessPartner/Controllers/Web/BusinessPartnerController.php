<?php

namespace App\BusinessPartner\Controllers\Web;

use App\BusinessPartner\Models\BusinessPartner;
use App\BusinessPartner\Requests\StoreBusinessPartnerRequest;
use App\BusinessPartner\Requests\UpdateBusinessPartnerRequest;
use App\BusinessPartner\Services\BusinessPartnerService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessPartnerController extends Controller
{
    public function __construct(protected BusinessPartnerService $partnerService) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['partner_type', 'search']);
        $partners = $this->partnerService->getPaginated($filters);

        return view('admin.business-partners.index', compact('partners'));
    }

    public function create(): View
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('admin.business-partners.create', compact('companies'));
    }

    public function store(StoreBusinessPartnerRequest $request): RedirectResponse
    {
        $partner = $this->partnerService->create($request->validated());

        return redirect()->route('admin.business-partners.show', $partner)
            ->with('success', 'İş ortağı başarıyla oluşturuldu.');
    }

    public function show(BusinessPartner $businessPartner): View
    {
        return view('admin.business-partners.show', ['partner' => $businessPartner->load('customers')]);
    }

    public function edit(BusinessPartner $businessPartner): View
    {
        return view('admin.business-partners.edit', ['partner' => $businessPartner]);
    }

    public function update(UpdateBusinessPartnerRequest $request, BusinessPartner $businessPartner): RedirectResponse
    {
        $businessPartner->update($request->validated());

        return redirect()->route('admin.business-partners.show', $businessPartner)
            ->with('success', 'İş ortağı başarıyla güncellendi.');
    }

    public function destroy(BusinessPartner $businessPartner): RedirectResponse
    {
        $businessPartner->delete();

        return redirect()->route('admin.business-partners.index')
            ->with('success', 'İş ortağı başarıyla silindi.');
    }
}
