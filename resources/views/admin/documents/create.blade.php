@extends('layouts.app')

@section('title', 'Yeni Belge - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Belge Oluştur</h2>
        <p class="text-secondary mb-0">Yeni bir belge kaydı oluşturun</p>
    </div>
    <a href="{{ route('admin.documents.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-secondary-200);">
    <form action="{{ route('admin.documents.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Bağlı Olduğu Model <span class="text-danger">*</span></label>
                <select name="documentable_type" class="form-select border-secondary-200 focus:border-secondary focus:ring-secondary @error('documentable_type') is-invalid border-danger @enderror" required>
                    <option value="">Model Seçin</option>
                    <option value="App\Models\Vehicle" {{ old('documentable_type') === 'App\Models\Vehicle' ? 'selected' : '' }}>Araç</option>
                    <option value="App\Models\Employee" {{ old('documentable_type') === 'App\Models\Employee' ? 'selected' : '' }}>Personel</option>
                    <option value="App\Models\Order" {{ old('documentable_type') === 'App\Models\Order' ? 'selected' : '' }}>Sipariş</option>
                </select>
                @error('documentable_type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Bağlı Olduğu ID <span class="text-danger">*</span></label>
                <input type="number" name="documentable_id" value="{{ old('documentable_id') }}" class="form-control border-secondary-200 focus:border-secondary focus:ring-secondary @error('documentable_id') is-invalid border-danger @enderror" required>
                @error('documentable_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Belge Türü <span class="text-danger">*</span></label>
                <select name="type" class="form-select border-secondary-200 focus:border-secondary focus:ring-secondary @error('type') is-invalid border-danger @enderror" required>
                    <option value="">Tür Seçin</option>
                    <option value="license" {{ old('type') === 'license' ? 'selected' : '' }}>Lisans</option>
                    <option value="insurance" {{ old('type') === 'insurance' ? 'selected' : '' }}>Sigorta</option>
                    <option value="inspection" {{ old('type') === 'inspection' ? 'selected' : '' }}>Muayene</option>
                    <option value="identity" {{ old('type') === 'identity' ? 'selected' : '' }}>Kimlik</option>
                </select>
                @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Belge Adı <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control border-secondary-200 focus:border-secondary focus:ring-secondary @error('name') is-invalid border-danger @enderror" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Dosya Yolu <span class="text-danger">*</span></label>
                <input type="text" name="file_path" value="{{ old('file_path') }}" class="form-control border-secondary-200 focus:border-secondary focus:ring-secondary @error('file_path') is-invalid border-danger @enderror" required>
                @error('file_path')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Bitiş Tarihi</label>
                <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="form-control border-secondary-200 focus:border-secondary focus:ring-secondary @error('expiry_date') is-invalid border-danger @enderror">
                @error('expiry_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select border-secondary-200 focus:border-secondary focus:ring-secondary @error('status') is-invalid border-danger @enderror" required>
                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Pasif</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-secondary-200);">
            <a href="{{ route('admin.documents.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Belge Oluştur</button>
        </div>
    </form>
</div>
@endsection
