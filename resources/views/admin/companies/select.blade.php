@extends('layouts.app')

@section('title', 'Firma Seç - Logistics')
@section('page-title', 'Firma Seç')
@section('page-subtitle', 'Çalışmak istediğiniz firmayı seçin')

@section('content')
<div class="row g-4 justify-content-center">
    @foreach($companies as $company)
    <div class="col-md-6 col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 transition-all hover:shadow-md" style="border-color: var(--bs-primary-200); cursor: pointer;" onclick="selectCompany({{ $company->id }})">
            <div class="d-flex align-items-start gap-3 mb-3">
                @php
                    $companyLogoUrl = $company->logo_url;
                @endphp
                @if($companyLogoUrl)
                <img src="{{ $companyLogoUrl }}?v={{ time() }}" alt="{{ $company->name }}" class="rounded-3xl border" style="width: 64px; height: 64px; object-fit: cover; border-color: var(--bs-primary-200) !important;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="rounded-3xl border d-flex align-items-center justify-content-center bg-white text-secondary" style="width: 64px; height: 64px; display: none;">
                    <span class="material-symbols-outlined" style="font-size: 28px;">business</span>
                </div>
                @else
                <div class="rounded-3xl border d-flex align-items-center justify-content-center bg-white text-secondary" style="width: 64px; height: 64px;">
                    <span class="material-symbols-outlined" style="font-size: 28px;">business</span>
            </div>
            @endif
            <div class="flex-grow-1">
                <h4 class="h5 fw-bold text-dark mb-1">{{ $company->name }}</h4>
                    @if($company->short_name)
                    <p class="small text-secondary mb-0">{{ $company->short_name }}</p>
                    @endif
                </div>
            </div>
            
            @if($company->tax_number)
            <div class="mb-2">
                <p class="small text-secondary mb-0">Vergi No: <span class="fw-semibold text-dark">{{ $company->tax_number }}</span></p>
            </div>
            @endif
            
            <div class="d-flex align-items-center justify-content-between mt-3">
                <span class="badge {{ $company->is_active ? 'bg-success-200 text-success' : 'bg-secondary-200 text-secondary' }} px-3 py-2 rounded-pill">
                    {{ $company->is_active ? 'Aktif' : 'Pasif' }}
                </span>
                <span class="material-symbols-outlined text-primary">arrow_forward</span>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($companies->isEmpty())
<div class="text-center py-5">
    <span class="material-symbols-outlined text-secondary mb-3" style="font-size: 64px;">business_center</span>
    <h4 class="h5 fw-bold text-dark mb-2">Henüz bir firmaya atanmamışsınız</h4>
    <p class="text-secondary mb-0">Lütfen sistem yöneticinizle iletişime geçin.</p>
</div>
@endif

<form id="companySwitchForm" action="{{ route('admin.companies.switch') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="company_id" id="selectedCompanyId">
</form>

@push('scripts')
<script>
function selectCompany(companyId) {
    document.getElementById('selectedCompanyId').value = companyId;
    document.getElementById('companySwitchForm').submit();
}
</script>
@endpush
@endsection
