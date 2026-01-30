@extends('layouts.app')

@section('title', 'Yeni Teslimat İmportu - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Teslimat İmportu</h2>
        <p class="text-secondary mb-0">Excel dosyası ile teslimat numarası import işlemini başlatın</p>
    </div>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.delivery-imports.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="file" class="form-label fw-semibold text-dark">Excel Dosyası (XLSX / CSV)</label>
            <input type="file"
                   name="file"
                   id="file"
                   class="form-control @error('file') is-invalid @enderror"
                   accept=".xlsx,.csv,.txt"
                   required>
            @error('file')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="form-text">
                    Maksimum 20 MB. Excel A yapısına uygun olmalıdır (dokümandaki teslimat numarası formatı).
                </div>
            @enderror
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.delivery-imports.index') }}" class="btn btn-outline-secondary">
                İptal
            </a>
            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 1.1rem;">upload_file</span>
                Yükle ve Başlat
            </button>
        </div>
    </form>
</div>
@endsection

