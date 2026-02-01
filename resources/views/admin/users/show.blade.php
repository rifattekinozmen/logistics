@extends('layouts.app')

@section('title', 'Kullanıcı Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Kullanıcı Detayı</h2>
        <p class="text-secondary mb-0">{{ $user->name }} kullanıcısının detayları</p>
    </div>
    <div class="d-flex gap-2">
        @php
            $isCustomerUser = $user->roles->contains(function($role) {
                return in_array($role->name, ['customer', 'customer_user', 'customer_viewer']);
            });
        @endphp
        @if(!$isCustomerUser)
        <a href="{{ route('admin.users.edit-roles', $user->id) }}" class="btn btn-warning d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">badge</span>
            Roller
        </a>
        @endif
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">edit</span>
            Düzenle
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span>
            Geri Dön
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3xl mb-4" role="alert">
    <span class="material-symbols-outlined me-2">check_circle</span>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    <!-- User Info Card -->
    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4 text-center" style="border-color: var(--bs-primary-200);">
            <div class="mb-3">
                @if($user->avatar)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="rounded-3xl shadow-lg" style="width: 120px; height: 120px; object-fit: cover;">
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
            
            <div class="mb-3">
                @if($user->status == 1)
                    <span class="badge bg-success-200 text-success px-3 py-2 rounded-pill fw-semibold">Aktif</span>
                @else
                    <span class="badge bg-danger-200 text-danger px-3 py-2 rounded-pill fw-semibold">Pasif</span>
                @endif
            </div>

            @php
                $isCustomerUser = $user->roles->contains(function($role) {
                    return in_array($role->name, ['customer', 'customer_user', 'customer_viewer']);
                });
            @endphp
            @if($user->roles->isNotEmpty() && !$isCustomerUser)
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

    <!-- User Details -->
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4" style="border-color: var(--bs-primary-200);">
            <h4 class="h5 fw-bold text-dark mb-4">Kullanıcı Bilgileri</h4>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <p class="small text-secondary mb-1">Ad Soyad</p>
                    <p class="fw-semibold text-dark mb-0">{{ $user->name }}</p>
                </div>
                @if($user->username)
                <div class="col-md-6">
                    <p class="small text-secondary mb-1">Kullanıcı Adı</p>
                    <p class="fw-semibold text-dark mb-0">{{ $user->username }}</p>
                </div>
                @endif
                <div class="col-md-6">
                    <p class="small text-secondary mb-1">E-posta</p>
                    <p class="fw-semibold text-dark mb-0">{{ $user->email }}</p>
                </div>
                @if($user->phone)
                <div class="col-md-6">
                    <p class="small text-secondary mb-1">Telefon</p>
                    <p class="fw-semibold text-dark mb-0">{{ $user->phone }}</p>
                </div>
                @endif
                <div class="col-md-6">
                    <p class="small text-secondary mb-1">Son Giriş</p>
                    <p class="fw-semibold text-dark mb-0">
                        @if($user->last_login_at)
                            {{ $user->last_login_at->format('d.m.Y H:i') }}
                        @else
                            <span class="text-secondary">Henüz giriş yapılmamış</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="small text-secondary mb-1">Üyelik Tarihi</p>
                    <p class="fw-semibold text-dark mb-0">{{ $user->created_at->format('d.m.Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $isCustomerUser = $user->roles->contains(function($role) {
        return in_array($role->name, ['customer', 'customer_user', 'customer_viewer']);
    });
@endphp
@if(!$isCustomerUser)
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
                <p class="text-secondary mb-0">Bu kullanıcıya henüz rol atanmamıştır.</p>
                <a href="{{ route('admin.users.edit-roles', $user->id) }}" class="btn btn-primary btn-sm mt-3">Rol Ata</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
@endsection
