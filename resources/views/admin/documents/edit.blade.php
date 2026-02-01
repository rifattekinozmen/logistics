@extends('layouts.app')

@section('title', 'Belge Düzenle - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Belge Düzenle</h2>
        <p class="text-secondary mb-0">Belge bilgilerini güncelleyin</p>
    </div>
    <a href="{{ route('admin.documents.show', $document->id) }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.documents.update', $document->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="category" class="form-label fw-semibold text-dark">Kategori <span class="text-danger">*</span></label>
                <input type="text" name="category" id="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category', $document->category) }}" required>
                @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="name" class="form-label fw-semibold text-dark">Belge Adı <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $document->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="valid_from" class="form-label fw-semibold text-dark">Geçerlilik Başlangıç</label>
                <input type="date" name="valid_from" id="valid_from" class="form-control @error('valid_from') is-invalid @enderror" value="{{ old('valid_from', $document->valid_from?->format('Y-m-d')) }}">
                @error('valid_from')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="valid_until" class="form-label fw-semibold text-dark">Geçerlilik Bitiş</label>
                <input type="date" name="valid_until" id="valid_until" class="form-control @error('valid_until') is-invalid @enderror" value="{{ old('valid_until', $document->valid_until?->format('Y-m-d')) }}">
                @error('valid_until')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.documents.show', $document->id) }}" class="btn btn-outline-secondary">İptal</a>
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </div>
    </form>
</div>
@endsection
