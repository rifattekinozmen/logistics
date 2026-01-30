@extends('layouts.app')

@section('title', 'Ayarlar - Logistics')
@section('page-title', 'Ayarlar')
@section('page-subtitle', 'Hesap ayarlarınızı yönetin')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3xl" role="alert">
    <span class="material-symbols-outlined me-2">check_circle</span>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    <!-- Password Change Card -->
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
            <h4 class="h5 fw-bold text-dark mb-4">Şifre Değiştir</h4>
            
            <form action="{{ route('admin.settings.password.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <!-- Current Password -->
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Mevcut Şifre <span class="text-danger">*</span></label>
                        <input type="password" name="current_password" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('current_password') is-invalid border-danger @enderror" required>
                        @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Yeni Şifre <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('password') is-invalid border-danger @enderror" required>
                        <small class="text-secondary">En az 8 karakter olmalıdır</small>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Yeni Şifre (Tekrar) <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control border-primary-200 focus:border-primary focus:ring-primary" required>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-12">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-light">İptal</a>
                            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined" style="font-size: 18px;">lock</span>
                                Şifreyi Güncelle
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Card -->
    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
            <h4 class="h5 fw-bold text-dark mb-4">Hesap Bilgileri</h4>
            <div class="d-flex flex-column gap-3">
                <div>
                    <p class="small text-secondary mb-1">Ad Soyad</p>
                    <p class="fw-semibold text-dark mb-0">{{ $user->name }}</p>
                </div>
                @if($user->username)
                <div>
                    <p class="small text-secondary mb-1">Kullanıcı Adı</p>
                    <p class="fw-semibold text-dark mb-0">{{ $user->username }}</p>
                </div>
                @endif
                <div>
                    <p class="small text-secondary mb-1">E-posta</p>
                    <p class="fw-semibold text-dark mb-0">{{ $user->email }}</p>
                </div>
                @if($user->phone)
                <div>
                    <p class="small text-secondary mb-1">Telefon</p>
                    <p class="fw-semibold text-dark mb-0">{{ $user->phone }}</p>
                </div>
                @endif
                <div>
                    <p class="small text-secondary mb-1">Son Giriş</p>
                    <p class="fw-semibold text-dark mb-0">
                        @if($user->last_login_at)
                            {{ $user->last_login_at->format('d.m.Y H:i') }}
                        @else
                            <span class="text-secondary">Henüz giriş yapılmamış</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="small text-secondary mb-1">Üyelik Tarihi</p>
                    <p class="fw-semibold text-dark mb-0">{{ $user->created_at->format('d.m.Y') }}</p>
                </div>
                <div>
                    <p class="small text-secondary mb-1">Durum</p>
                    <span class="badge bg-success-200 text-success px-3 py-2 rounded-pill">
                        Aktif
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Roles and Permissions Card -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
            <h4 class="h5 fw-bold text-dark mb-4">Roller ve Yetkiler</h4>
            
            @if($user->roles->isNotEmpty())
            <div class="row g-4">
                @foreach($user->roles as $role)
                <div class="col-md-6 col-lg-4">
                    <div class="border rounded-3xl p-3" style="border-color: var(--bs-primary-200);">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-primary">badge</span>
                            <h5 class="h6 fw-bold text-dark mb-0">{{ $role->name }}</h5>
                        </div>
                        @if($role->description)
                        <p class="small text-secondary mb-3">{{ $role->description }}</p>
                        @endif
                        
                        @if($role->permissions->isNotEmpty())
                        <div>
                            <p class="small fw-semibold text-dark mb-2">Yetkiler:</p>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($role->permissions as $permission)
                                <span class="badge bg-secondary-200 text-secondary px-2 py-1 rounded-pill small">
                                    {{ $permission->code }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <p class="small text-secondary mb-0">Bu role ait yetki bulunmamaktadır.</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-4">
                <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 48px;">badge</span>
                <p class="text-secondary mb-0">Henüz size atanmış bir rol bulunmamaktadır.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
