@extends('layouts.app')

@section('title', 'Depo Düzenle - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Depo Düzenle</h2>
        <p class="text-secondary mb-0">Depo bilgilerini güncelleyin</p>
    </div>
    <a href="{{ route('admin.warehouses.show', $warehouse->id) }}" class="btn btn-light">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.warehouses.update', $warehouse->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="branch_id" class="form-label fw-semibold text-dark">Şube <span class="text-danger">*</span></label>
                <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                    <option value="">Seçiniz</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $warehouse->branch_id) == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @error('branch_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="name" class="form-label fw-semibold text-dark">Depo Adı <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $warehouse->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="code" class="form-label fw-semibold text-dark">Kod</label>
                <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $warehouse->code) }}">
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="1" {{ old('status', $warehouse->status) == 1 ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('status', $warehouse->status) == 0 ? 'selected' : '' }}>Pasif</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label for="address" class="form-label fw-semibold text-dark">Adres</label>
                <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $warehouse->address) }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.warehouses.show', $warehouse->id) }}" class="btn btn-outline-secondary">İptal</a>
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </div>
    </form>
</div>
@endsection
