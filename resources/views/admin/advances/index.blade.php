@extends('layouts.app')

@section('title', 'Avanslar - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Avans Talepleri</h2>
        <p class="text-secondary mb-0">Tüm avans taleplerini görüntüleyin ve yönetin</p>
    </div>
    <a href="{{ route('admin.advances.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni Avans Talebi
    </a>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="account_balance_wallet" color="primary" col="col-md-4" />
    <x-index-stat-card title="Bekleyen" :value="$stats['pending'] ?? 0" icon="schedule" color="warning" col="col-md-4" />
    <x-index-stat-card title="Onaylandı" :value="$stats['approved'] ?? 0" icon="check_circle" color="success" col="col-md-4" />
</div>

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.advances.index') }}" class="row g-3 align-items-end">
        <div class="col-md-10">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Beklemede</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Onaylandı</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Ödendi</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Reddedildi</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 small text-secondary fw-semibold">Personel</th>
                    <th class="border-0 small text-secondary fw-semibold">Tutar</th>
                    <th class="border-0 small text-secondary fw-semibold">Talep Tarihi</th>
                    <th class="border-0 small text-secondary fw-semibold">Durum</th>
                    <th class="border-0 small text-secondary fw-semibold text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($advances as $advance)
                    <tr>
                        <td class="align-middle">
                            <span class="fw-semibold text-dark">{{ $advance->employee->first_name }} {{ $advance->employee->last_name }}</span>
                        </td>
                        <td class="align-middle">
                            <span class="fw-bold text-dark">{{ number_format($advance->amount, 2) }} ₺</span>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $advance->requested_date->format('d.m.Y') }}</small>
                        </td>
                        <td class="align-middle">
                            <span class="badge bg-{{ match($advance->status) { 'approved' => 'success', 'paid' => 'info', 'rejected' => 'danger', default => 'warning' } }}-200 text-{{ match($advance->status) { 'approved' => 'success', 'paid' => 'info', 'rejected' => 'danger', default => 'warning' } }} rounded-pill px-3 py-2">
                                {{ match($advance->status) { 'approved' => 'Onaylandı', 'paid' => 'Ödendi', 'rejected' => 'Reddedildi', default => 'Beklemede' } }}
                            </span>
                        </td>
                        <td class="align-middle text-end">
                            @if($advance->status === 'pending')
                                <form action="{{ route('admin.advances.approve', $advance) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-sm bg-success-200 text-success border-0">Onayla</button>
                                </form>
                                <form action="{{ route('admin.advances.approve', $advance) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-sm bg-danger-200 text-danger border-0">Reddet</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <p class="text-secondary mb-0">Henüz avans talebi bulunmuyor.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($advances->hasPages())
        <div class="p-4 border-top">
            {{ $advances->links() }}
        </div>
    @endif
</div>
@endsection
