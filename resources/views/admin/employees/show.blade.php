@extends('layouts.app')

@section('title', 'Personel Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Personel Detayı</h2>
        <p class="text-secondary mb-0">{{ $employee->first_name }} {{ $employee->last_name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-primary">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">edit</span>
            Düzenle
        </a>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span>
            Geri Dön
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h3 class="h4 fw-bold text-dark mb-4">Kişisel Bilgiler</h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Ad</label>
                    <p class="fw-bold text-dark mb-0">{{ $employee->first_name }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Soyad</label>
                    <p class="fw-bold text-dark mb-0">{{ $employee->last_name }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Personel No</label>
                    <p class="fw-bold text-dark mb-0">{{ $employee->employee_number }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Durum</label>
                    <div>
                        @php
                            $statusColors = [0 => 'secondary', 1 => 'success', 2 => 'warning'];
                            $statusLabels = [0 => 'Pasif', 1 => 'Aktif', 2 => 'İzinli'];
                            $color = $statusColors[$employee->status] ?? 'secondary';
                            $label = $statusLabels[$employee->status] ?? '-';
                        @endphp
                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-3 py-2 rounded-pill">
                            {{ $label }}
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Telefon</label>
                    <p class="text-dark mb-0">{{ $employee->phone ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">E-posta</label>
                    <p class="text-dark mb-0">{{ $employee->email ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-4">İş Bilgileri</h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Şube</label>
                    <p class="fw-bold text-dark mb-0">{{ $employee->branch->name ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Pozisyon</label>
                    <p class="fw-bold text-dark mb-0">{{ $employee->position->name ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">İşe Başlama Tarihi</label>
                    <p class="text-dark mb-0">{{ $employee->hire_date ? $employee->hire_date->format('d.m.Y') : '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Maaş</label>
                    <p class="fw-bold text-dark mb-0">{{ $employee->salary ? number_format($employee->salary, 2).' ₺' : '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-4">Hızlı İşlemler</h3>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2">
                    <span class="material-symbols-outlined">edit</span>
                    Düzenle
                </a>
                <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('Bu personeli silmek istediğinize emin misiniz?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined">delete</span>
                        Sil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
