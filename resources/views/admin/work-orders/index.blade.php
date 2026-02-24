@extends('layouts.app')

@section('title', 'İş Emirleri - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">İş Emirleri</h2>
        <p class="text-secondary mb-0">Tüm iş emirlerini görüntüleyin ve yönetin</p>
    </div>
    <a href="{{ route('admin.work-orders.create') }}" class="btn btn-work-orders d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni İş Emri
    </a>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="build" color="primary" col="col-md-4" />
    <x-index-stat-card title="Bekleyen" :value="$stats['pending'] ?? 0" icon="schedule" color="warning" col="col-md-4" />
    <x-index-stat-card title="Tamamlanan" :value="$stats['completed'] ?? 0" icon="check_circle" color="success" col="col-md-4" />
</div>

<div class="filter-area filter-area-work-orders rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.work-orders.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Onay Bekliyor</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Onaylandı</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Devam Ediyor</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>İptal</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Araç</label>
            <select name="vehicle_id" class="form-select">
                <option value="">Tümü</option>
                @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->plate }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Tarih Başlangıç</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-work-orders w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 fw-semibold text-secondary small">İş Emri No</th>
                    <th class="border-0 fw-semibold text-secondary small">Araç</th>
                    <th class="border-0 fw-semibold text-secondary small">Tür</th>
                    <th class="border-0 fw-semibold text-secondary small">Servis Sağlayıcı</th>
                    <th class="border-0 fw-semibold text-secondary small">Durum</th>
                    <th class="border-0 fw-semibold text-secondary small">Oluşturulma</th>
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workOrders as $workOrder)
                <tr>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">#{{ $workOrder->id }}</span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $workOrder->vehicle->plate ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $workOrder->type }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $workOrder->serviceProvider->name ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'approved' => 'info',
                                'in_progress' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                            ];
                            $softColors = [
                                'warning' => 'warning-200',
                                'info' => 'info-200',
                                'primary' => 'primary-200',
                                'success' => 'success-200',
                                'danger' => 'danger-200',
                            ];
                            $statusLabels = [
                                'pending' => 'Onay Bekliyor',
                                'approved' => 'Onaylandı',
                                'in_progress' => 'Devam Ediyor',
                                'completed' => 'Tamamlandı',
                                'cancelled' => 'İptal',
                            ];
                            $color = $statusColors[$workOrder->status] ?? 'secondary';
                            $softColor = $softColors[$color] ?? 'secondary-200';
                            $label = $statusLabels[$workOrder->status] ?? $workOrder->status;
                        @endphp
                        <span class="badge bg-{{ $softColor }} text-{{ $color }} px-3 py-2 rounded-pill fw-semibold">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">
                            {{ $workOrder->created_at ? $workOrder->created_at->format('d.m.Y') : '-' }}
                        </small>
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.work-orders.show', $workOrder->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('admin.work-orders.edit', $workOrder->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0 hover:bg-primary hover:text-white transition-all" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.work-orders.destroy', $workOrder->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu iş emrini silmek istediğinize emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm bg-danger-200 text-danger border-0 hover:bg-danger hover:text-white transition-all" title="Sil">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">build</span>
                            <p class="text-secondary mb-0">Henüz iş emri bulunmuyor.</p>
                            <a href="{{ route('admin.work-orders.create') }}" class="btn btn-work-orders btn-sm mt-2">İlk İş Emrini Oluştur</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($workOrders->hasPages())
    <div class="p-4 border-top">
        {{ $workOrders->links() }}
    </div>
    @endif
</div>
@endsection
