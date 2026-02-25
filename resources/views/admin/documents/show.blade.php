@extends('layouts.app')

@section('title', 'Belge Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Belge Detayı</h2>
        <p class="text-secondary mb-0">{{ $document->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.documents.edit', $document->id) }}" class="btn btn-primary">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">edit</span>
            Düzenle
        </a>
        <a href="{{ route('admin.documents.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span>
            Geri Dön
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h3 class="h4 fw-bold text-dark mb-4 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">info</span>
                Genel Bilgiler
            </h3>
            <dl class="row mb-0">
                <dt class="col-sm-4">Belge Adı</dt>
                <dd class="col-sm-8"><span class="fw-bold">{{ $document->name }}</span></dd>

                <dt class="col-sm-4">Kategori</dt>
                <dd class="col-sm-8">{{ $document->category ?? '-' }}</dd>

                <dt class="col-sm-4">İlişkili Model</dt>
                <dd class="col-sm-8">{{ class_basename($document->documentable_type ?? '') }} #{{ $document->documentable_id ?? '-' }}</dd>

                <dt class="col-sm-4">Geçerlilik Başlangıç</dt>
                <dd class="col-sm-8">{{ $document->valid_from?->format('d.m.Y') ?? '-' }}</dd>

                <dt class="col-sm-4">Geçerlilik Bitiş</dt>
                <dd class="col-sm-8">{{ $document->valid_until?->format('d.m.Y') ?? '-' }}</dd>

                <dt class="col-sm-4">Dosya Boyutu</dt>
                <dd class="col-sm-8">{{ $document->file_size ? number_format($document->file_size / 1024, 2) . ' KB' : '-' }}</dd>

                <dt class="col-sm-4">Dosya Türü</dt>
                <dd class="col-sm-8">{{ $document->mime_type ?? '-' }}</dd>

                @if($document->file_path)
                    <dt class="col-sm-4">Dosya</dt>
                    <dd class="col-sm-8">
                        <a href="{{ Storage::disk('public')->url($document->file_path) }}" target="_blank" class="btn btn-sm btn-primary">
                            <span class="material-symbols-outlined" style="font-size: 1rem;">download</span>
                            İndir
                        </a>
                    </dd>
                @endif
            </dl>
        </div>
    </div>
</div>
@endsection
