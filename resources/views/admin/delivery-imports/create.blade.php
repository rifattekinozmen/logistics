@extends('layouts.app')

@section('title', 'Yeni Teslimat Raporu - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Teslimat Raporu</h2>
        <p class="text-secondary mb-0">7 günlük Excel teslimat raporunuzu yükleyin. İşlem tamamlandıktan sonra rapor detayından <strong>Malzeme Pivot</strong> ile tarih bazlı özeti görüntüleyebilirsiniz.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.delivery-imports.template') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">download</span>
            Şablon İndir
        </a>
        @if(!empty($reportTypes) && count($reportTypes) > 1)
            @foreach($reportTypes as $key => $config)
                <a href="{{ route('admin.delivery-imports.template', ['type' => $key]) }}" class="btn btn-outline-secondary btn-sm">
                    {{ $config['label'] ?? $key }}
                </a>
            @endforeach
        @endif
    </div>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.delivery-imports.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        @if(!empty($reportTypes))
            <div class="mb-3">
                <label for="report_type" class="form-label fw-semibold text-dark">Rapor Tipi</label>
                <select name="report_type" id="report_type" class="form-select @error('report_type') is-invalid @enderror">
                    @foreach($reportTypes as $key => $config)
                        <option value="{{ $key }}" @selected(old('report_type', array_key_first($reportTypes)) === $key)>
                            {{ $config['label'] ?? $key }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Kolon başlıkları bu tipe göre normalize edilir.</div>
            </div>
        @endif

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
                    Maksimum 20 MB. İlk satır başlık olmalı; kolon isimleri <a href="{{ route('admin.delivery-imports.template') }}">şablon</a> ile aynı olmalıdır.
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

