@extends('layouts.app')

@section('title', 'İzinler - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">İzin Talepleri</h2>
        <p class="text-secondary mb-0">Tüm izin taleplerini görüntüleyin ve yönetin</p>
    </div>
    <a href="{{ route('admin.leaves.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni İzin Talebi
    </a>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="event_available" color="primary" col="col-md-4" />
    <x-index-stat-card title="Onay Bekleyen" :value="$stats['pending'] ?? 0" icon="schedule" color="warning" col="col-md-4" />
    <x-index-stat-card title="Onaylandı" :value="$stats['approved'] ?? 0" icon="check_circle" color="success" col="col-md-4" />
</div>

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.leaves.index') }}" class="row g-3">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Beklemede</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Onaylandı</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Reddedildi</option>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
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
                    <th class="border-0 small text-secondary fw-semibold">İzin Türü</th>
                    <th class="border-0 small text-secondary fw-semibold">Başlangıç</th>
                    <th class="border-0 small text-secondary fw-semibold">Bitiş</th>
                    <th class="border-0 small text-secondary fw-semibold">Gün</th>
                    <th class="border-0 small text-secondary fw-semibold">Durum</th>
                    <th class="border-0 small text-secondary fw-semibold text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                    <tr>
                        <td class="align-middle">
                            <span class="fw-semibold text-dark">{{ $leave->employee->first_name }} {{ $leave->employee->last_name }}</span>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ ucfirst($leave->leave_type) }}</small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $leave->start_date->format('d.m.Y') }}</small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $leave->end_date->format('d.m.Y') }}</small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $leave->total_days }} gün</small>
                        </td>
                        <td class="align-middle">
                            <span class="badge bg-{{ match($leave->status) { 'approved' => 'success', 'rejected' => 'danger', default => 'warning' } }}-200 text-{{ match($leave->status) { 'approved' => 'success', 'rejected' => 'danger', default => 'warning' } }} rounded-pill px-3 py-2">
                                {{ match($leave->status) { 'approved' => 'Onaylandı', 'rejected' => 'Reddedildi', default => 'Beklemede' } }}
                            </span>
                        </td>
                        <td class="align-middle text-end">
                            @if($leave->status === 'pending')
                                <form action="{{ route('admin.leaves.approve', $leave) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-sm bg-success-200 text-success border-0">Onayla</button>
                                </form>
                                <form action="{{ route('admin.leaves.approve', $leave) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-sm bg-danger-200 text-danger border-0">Reddet</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <p class="text-secondary mb-0">Henüz izin talebi bulunmuyor.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leaves->hasPages())
        <div class="p-4 border-top">
            {{ $leaves->links() }}
        </div>
    @endif
</div>
@endsection
