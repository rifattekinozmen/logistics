@extends('layouts.app')

@section('title', 'Personel - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Personel</h2>
        <p class="text-secondary mb-0">Tüm personeli görüntüleyin ve yönetin</p>
    </div>
    <a href="{{ route('admin.employees.create') }}" class="btn btn-employees d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni Personel
    </a>
</div>

<div class="filter-area filter-area-employees rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.employees.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Pasif</option>
                <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>İzinli</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Şube</label>
            <select name="branch_id" class="form-select">
                <option value="">Tümü</option>
                @foreach($branches ?? [] as $branch)
                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Pozisyon</label>
            <select name="position_id" class="form-select">
                <option value="">Tümü</option>
                @foreach($positions ?? [] as $position)
                <option value="{{ $position->id }}" {{ request('position_id') == $position->id ? 'selected' : '' }}>
                    {{ $position->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-employees w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 fw-semibold text-secondary small">Personel No</th>
                    <th class="border-0 fw-semibold text-secondary small">Ad Soyad</th>
                    <th class="border-0 fw-semibold text-secondary small">Şube</th>
                    <th class="border-0 fw-semibold text-secondary small">Pozisyon</th>
                    <th class="border-0 fw-semibold text-secondary small">İşe Başlama</th>
                    <th class="border-0 fw-semibold text-secondary small">Durum</th>
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                <tr>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">{{ $employee->employee_number }}</span>
                    </td>
                    <td class="align-middle">
                        {{ $employee->first_name }} {{ $employee->last_name }}
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $employee->branch->name ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $employee->position->name ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $employee->hire_date ? $employee->hire_date->format('d.m.Y') : '-' }}</small>
                    </td>
                    <td class="align-middle">
                        @php
                            $statusColors = [0 => 'secondary', 1 => 'success', 2 => 'warning'];
                            $statusLabels = [0 => 'Pasif', 1 => 'Aktif', 2 => 'İzinli'];
                            $color = $statusColors[$employee->status] ?? 'secondary';
                            $label = $statusLabels[$employee->status] ?? '-';
                        @endphp
                        @php
                            $softColors = ['secondary' => 'secondary-200', 'success' => 'success-200', 'warning' => 'warning-200'];
                            $softColor = $softColors[$color] ?? 'secondary-200';
                        @endphp
                        <span class="badge bg-{{ $softColor }} text-{{ $color }} px-3 py-2 rounded-pill fw-semibold">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.employees.show', $employee->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0 hover:bg-primary hover:text-white transition-all" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu personeli silmek istediğinize emin misiniz?');">
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
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">groups</span>
                            <p class="text-secondary mb-0">Henüz personel bulunmuyor.</p>
                            <a href="{{ route('admin.employees.create') }}" class="btn btn-employees btn-sm mt-2">İlk Personeli Ekle</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())
    <div class="p-4 border-top">
        {{ $employees->links() }}
    </div>
    @endif
</div>
@endsection
