@extends('layouts.app')

@section('title', 'Vardiyalar - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Vardiyalar</h2>
        <p class="text-secondary mb-0">Tüm vardiyaları görüntüleyin ve yönetin</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.shifts.planning') }}" class="btn btn-shifts d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">calendar_month</span>
            Planlama
        </a>
        <a href="{{ route('admin.shifts.templates') }}" class="btn btn-shifts d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">schedule</span>
            Şablonlar
        </a>
    </div>
</div>

<div class="filter-area filter-area-shifts rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.shifts.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Personel</label>
            <select name="employee_id" class="form-select">
                <option value="">Tümü</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->first_name }} {{ $employee->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Tarih Başlangıç</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Tarih Bitiş</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-shifts w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 fw-semibold text-secondary small">Personel</th>
                    <th class="border-0 fw-semibold text-secondary small">Vardiya Tarihi</th>
                    <th class="border-0 fw-semibold text-secondary small">Şablon</th>
                    <th class="border-0 fw-semibold text-secondary small">Başlangıç</th>
                    <th class="border-0 fw-semibold text-secondary small">Bitiş</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shifts as $shift)
                <tr>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">{{ $shift->employee->first_name ?? '-' }} {{ $shift->employee->last_name ?? '' }}</span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">
                            {{ $shift->shift_date ? $shift->shift_date->format('d.m.Y') : '-' }}
                        </small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $shift->template->name ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $shift->template->start_time ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $shift->template->end_time ?? '-' }}</small>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">schedule</span>
                            <p class="text-secondary mb-0">Henüz vardiya bulunmuyor.</p>
                            <a href="{{ route('admin.shifts.planning') }}" class="btn btn-shifts btn-sm mt-2">Vardiya Planla</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($shifts->hasPages())
    <div class="p-4 border-top">
        {{ $shifts->links() }}
    </div>
    @endif
</div>
@endsection
