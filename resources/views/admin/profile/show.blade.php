@extends('layouts.app')

@section('title', 'Profil - Logistics')
@section('page-title', 'Profil')
@section('page-subtitle', 'Profil bilgilerinizi görüntüleyin ve güncelleyin')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3xl" role="alert">
    <span class="material-symbols-outlined me-2">check_circle</span>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    <!-- Profile Info Card -->
    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4 text-center" style="border-color: var(--bs-primary-200);">
            <div class="mb-3">
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="rounded-3xl shadow-lg" style="width: 120px; height: 120px; object-fit: cover;">
                @else
                    <div class="rounded-3xl border d-flex align-items-center justify-content-center bg-white text-secondary mx-auto" style="width: 120px; height: 120px;">
                        <span class="material-symbols-outlined" style="font-size: 40px;">person</span>
                    </div>
                @endif
            </div>
            <h3 class="h5 fw-bold text-dark mb-1">{{ $user->name }}</h3>
            @if($user->username)
            <p class="text-secondary mb-1">
                <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">badge</span>
                {{ $user->username }}
            </p>
            @endif
            <p class="text-secondary mb-3">{{ $user->email }}</p>
            @if($user->phone)
            <p class="small text-secondary mb-3">
                <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">phone</span>
                {{ $user->phone }}
            </p>
            @endif
            
            <!-- Roles -->
            @if($user->roles->isNotEmpty())
            <div class="mt-3 pt-3 border-top">
                <p class="small text-secondary mb-2 fw-semibold">Roller</p>
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    @foreach($user->roles as $role)
                    <span class="badge bg-primary-200 text-primary px-3 py-2 rounded-pill">
                        {{ $role->name }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Profile Form -->
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
            <h4 class="h5 fw-bold text-dark mb-4">Profil Bilgileri</h4>
            
            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <!-- Avatar Upload -->
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Profil Fotoğrafı</label>
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                @if($user->avatar)
                                    <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" class="rounded-3xl" style="width: 80px; height: 80px; object-fit: cover;">
                                @else
                                    <div class="rounded-3xl border d-flex align-items-center justify-content-center bg-white text-secondary" style="width: 80px; height: 80px;">
                                        <span class="material-symbols-outlined" style="font-size: 32px;">person</span>
                                    </div>
                                @endif
                            </div>
                            <div class="grow">
                                <input type="file" name="avatar" accept="image/*" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('avatar') is-invalid border-danger @enderror">
                                <small class="text-secondary">JPEG, PNG, JPG veya GIF (Max: 2MB)</small>
                                @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Ad Soyad <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('name') is-invalid border-danger @enderror" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Username -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Kullanıcı Adı</label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('username') is-invalid border-danger @enderror" placeholder="ornek_kullanici">
                        <small class="text-secondary">Sadece harf, rakam, tire ve alt çizgi içerebilir</small>
                        @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">E-posta <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('email') is-invalid border-danger @enderror" required>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Telefon</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('phone') is-invalid border-danger @enderror">
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="col-12">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-light">İptal</a>
                            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                                Kaydet
                            </button>
                        </div>
                    </div>
                </div>
            </form>
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
