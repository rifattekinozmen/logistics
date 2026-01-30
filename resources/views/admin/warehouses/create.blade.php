@extends('layouts.app')

@section('title', 'Yeni Depo - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Depo Oluştur</h2>
        <p class="text-secondary mb-0">Yeni bir depo kaydı oluşturun</p>
    </div>
    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-light">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-success-200);">
    <form action="{{ route('admin.warehouses.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Şube <span class="text-danger">*</span></label>
                <select name="branch_id" class="form-select border-success-200 focus:border-success focus:ring-success @error('branch_id') is-invalid border-danger @enderror" required>
                    <option value="">Şube Seçin</option>
                    @foreach($branches ?? [] as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                    @endforeach
                </select>
                @error('branch_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Depo Adı <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control border-success-200 focus:border-success focus:ring-success @error('name') is-invalid border-danger @enderror" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Depo Kodu</label>
                <input type="text" name="code" value="{{ old('code') }}" class="form-control border-success-200 focus:border-success focus:ring-success @error('code') is-invalid border-danger @enderror">
                @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select border-success-200 focus:border-success focus:ring-success @error('status') is-invalid border-danger @enderror" required>
                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Pasif</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold text-dark">Adres</label>
                <textarea name="address" class="form-control border-success-200 focus:border-success focus:ring-success @error('address') is-invalid border-danger @enderror" rows="3">{{ old('address') }}</textarea>
                @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-success-200);">
            <a href="{{ route('admin.warehouses.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Depo Oluştur</button>
        </div>
    </form>
</div>
@endsection
