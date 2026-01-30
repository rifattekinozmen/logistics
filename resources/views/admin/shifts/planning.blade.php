@extends('layouts.app')

@section('title', 'Vardiya Planlama - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Vardiya Planlama</h2>
        <p class="text-secondary mb-0">Personel vardiyalarını planlayın</p>
    </div>
    <a href="{{ route('admin.shifts.index') }}" class="btn btn-light">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
    <p class="text-secondary mb-4">Vardiya planlama özelliği yakında eklenecektir.</p>
    
    <div class="row g-4">
        <div class="col-md-6">
            <label class="form-label fw-semibold text-dark">Vardiya Şablonu</label>
            <select class="form-select border-primary-200 focus:border-primary focus:ring-primary">
                <option value="">Şablon Seçin</option>
                @foreach($templates ?? [] as $template)
                <option value="{{ $template->id }}">{{ $template->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="col-md-6">
            <label class="form-label fw-semibold text-dark">Personel</label>
            <select class="form-select border-primary-200 focus:border-primary focus:ring-primary">
                <option value="">Personel Seçin</option>
                @foreach($employees ?? [] as $employee)
                <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
@endsection
