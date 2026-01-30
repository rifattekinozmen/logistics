@extends('layouts.app')

@section('title', 'Bordrolar - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Bordrolar</h2>
        <p class="text-secondary mb-0">Tüm bordroları görüntüleyin ve yönetin</p>
    </div>
    <a href="{{ route('admin.payrolls.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni Bordro
    </a>
</div>

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.payrolls.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Taslak</option>
                <option value="finalized" {{ request('status') === 'finalized' ? 'selected' : '' }}>Kesinleşti</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Ödendi</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Dönem Başlangıç</label>
            <input type="date" name="period_start" value="{{ request('period_start') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Dönem Bitiş</label>
            <input type="date" name="period_end" value="{{ request('period_end') }}" class="form-control">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 small text-secondary fw-semibold">Bordro No</th>
                    <th class="border-0 small text-secondary fw-semibold">Personel</th>
                    <th class="border-0 small text-secondary fw-semibold">Dönem</th>
                    <th class="border-0 small text-secondary fw-semibold">Net Maaş</th>
                    <th class="border-0 small text-secondary fw-semibold">Durum</th>
                    <th class="border-0 small text-secondary fw-semibold text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $payroll)
                    <tr>
                        <td class="align-middle">
                            <span class="fw-semibold text-dark">{{ $payroll->payroll_number }}</span>
                        </td>
                        <td class="align-middle">
                            <span class="fw-semibold text-dark">{{ $payroll->employee->first_name }} {{ $payroll->employee->last_name }}</span>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">
                                {{ $payroll->period_start->format('d.m.Y') }} - {{ $payroll->period_end->format('d.m.Y') }}
                            </small>
                        </td>
                        <td class="align-middle">
                            <span class="fw-bold text-dark">{{ number_format($payroll->net_salary, 2) }} ₺</span>
                        </td>
                        <td class="align-middle">
                            <span class="badge bg-{{ match($payroll->status) { 'paid' => 'success', 'finalized' => 'info', default => 'warning' } }}-200 text-{{ match($payroll->status) { 'paid' => 'success', 'finalized' => 'info', default => 'warning' } }} rounded-pill px-3 py-2">
                                {{ match($payroll->status) { 'paid' => 'Ödendi', 'finalized' => 'Kesinleşti', default => 'Taslak' } }}
                            </span>
                        </td>
                        <td class="align-middle text-end">
                            <a href="{{ route('admin.payrolls.show', $payroll) }}" class="btn btn-sm bg-primary-200 text-primary border-0">
                                Detay
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <p class="text-secondary mb-0">Henüz bordro bulunmuyor.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payrolls->hasPages())
        <div class="p-4 border-top">
            {{ $payrolls->links() }}
        </div>
    @endif
</div>
@endsection
