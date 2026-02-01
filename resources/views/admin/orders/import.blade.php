@extends('layouts.app')

@section('title', 'Sipariş İmportu - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Sipariş İmportu</h2>
        <p class="text-secondary mb-0">CSV veya Excel dosyası ile toplu sipariş oluşturun</p>
    </div>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
        Sipariş Listesi
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
    <h3 class="h6 fw-semibold text-dark mb-3">Şablon ve Kurallar</h3>
    <ul class="text-secondary small mb-0">
        <li>İlk satır başlık olmalıdır: <code>müşteri_id</code>, <code>alış_adresi</code>, <code>teslimat_adresi</code>, <code>planlanan_teslimat_tarihi</code>, <code>ağırlık_kg</code>, <code>hacim_m3</code>, <code>notlar</code></li>
        <li><strong>müşteri_id</strong> zorunludur; sistemde kayıtlı aktif müşteri ID’si olmalıdır.</li>
        <li>Alış ve teslimat adresi zorunludur.</li>
        <li>Tarih formatı: <code>Y-m-d H:i</code> veya <code>d.m.Y H:i</code></li>
        <li>CSV ayırıcı: noktalı virgül (;) veya virgül. UTF-8 encoding kullanın.</li>
    </ul>
    <div class="mt-3">
        <a href="{{ route('admin.orders.import-template') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.1rem;">download</span>
            Şablon CSV İndir
        </a>
    </div>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.orders.import.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="file" class="form-label fw-semibold text-dark">Dosya (CSV / XLSX / XLS)</label>
            <input type="file"
                   name="file"
                   id="file"
                   class="form-control @error('file') is-invalid @enderror"
                   accept=".csv,.xlsx,.xls,.txt"
                   required>
            @error('file')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="form-text">Maksimum 20 MB. Şablona uygun sütunları kullanın.</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">İptal</a>
            <button type="submit" class="btn btn-orders d-flex align-items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 1.1rem;">upload_file</span>
                Yükle ve İçe Aktar
            </button>
        </div>
    </form>
</div>
@endsection
