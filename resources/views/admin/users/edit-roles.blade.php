@extends('layouts.app')

@section('title', 'Kullanıcı Rolleri - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Kullanıcı Rolleri</h2>
        <p class="text-secondary mb-0">{{ $user->name }} kullanıcısına rol atayın</p>
    </div>
    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-light">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
        Geri Dön
    </a>
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
            <p class="text-secondary mb-1">{{ $user->username }}</p>
            @endif
            <p class="text-secondary mb-3">{{ $user->email }}</p>
            
            @if($user->roles->isNotEmpty())
            <div class="mt-3 pt-3 border-top">
                <p class="small text-secondary mb-2 fw-semibold">Mevcut Roller</p>
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

    <!-- Roles Assignment Form -->
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
            <h4 class="h5 fw-bold text-dark mb-4">Rol Atama</h4>
            
            @if(isset($hasCustomerRole) && $hasCustomerRole)
            <div class="alert alert-info rounded-3xl mb-4 d-flex align-items-center gap-2" role="alert">
                <span class="material-symbols-outlined">info</span>
                <span class="small">Bu kullanıcı müşteri portalı kullanıcısıdır. Sadece müşteri portalı rolleri atanabilir.</span>
            </div>
            @else
            <div class="alert alert-info rounded-3xl mb-4 d-flex align-items-center gap-2" role="alert">
                <span class="material-symbols-outlined">info</span>
                <span class="small">Bu kullanıcı sistem kullanıcısıdır. Sadece sistem rolleri atanabilir.</span>
            </div>
            @endif
            
            <form action="{{ route('admin.users.update-roles', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                @if($roles->isEmpty())
                <div class="text-center py-5">
                    <span class="material-symbols-outlined text-secondary mb-3 d-block" style="font-size: 3rem; opacity: 0.3;">badge</span>
                    <p class="text-secondary mb-0">Bu kullanıcı tipi için atanabilir rol bulunmamaktadır.</p>
                </div>
                @else
                <div class="row g-3">
                    @foreach($roles as $role)
                        @php
                            $isCustomerRole = in_array($role->name, ['customer', 'customer_user', 'customer_viewer']);
                            $borderColor = $isCustomerRole ? 'var(--bs-success-200)' : 'var(--bs-primary-200)';
                            $bgColor = $isCustomerRole ? 'bg-success-50' : '';
                        @endphp
                    <div class="col-md-6">
                        <div class="border rounded-3xl p-3 hover:shadow-sm transition-all {{ $bgColor }}" style="border-color: {{ $borderColor }};">
                            <div class="form-check">
                                <input class="form-check-input {{ $isCustomerRole ? 'customer-role-checkbox' : 'system-role-checkbox' }}" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}" {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                                <label class="form-check-label w-100" for="role_{{ $role->id }}">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        @if($isCustomerRole)
                                            <span class="material-symbols-outlined text-success">store</span>
                                        @else
                                            <span class="material-symbols-outlined text-primary">badge</span>
                                        @endif
                                        <h5 class="h6 fw-bold text-dark mb-0">{{ $role->name }}</h5>
                                    </div>
                                    @if($role->description)
                                    <p class="small text-secondary mb-2">{{ $role->description }}</p>
                                    @endif
                                    
                                    @if($role->permissions->isNotEmpty())
                                    <div class="mt-2">
                                        <p class="small fw-semibold text-dark mb-1">Yetkiler:</p>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($role->permissions->take(5) as $permission)
                                            <span class="badge bg-secondary-200 text-secondary px-2 py-1 rounded-pill small">
                                                {{ $permission->code }}
                                            </span>
                                            @endforeach
                                            @if($role->permissions->count() > 5)
                                            <span class="badge bg-secondary-200 text-secondary px-2 py-1 rounded-pill small">
                                                +{{ $role->permissions->count() - 5 }} daha
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    @else
                                    <p class="small text-secondary mb-0">Bu role ait yetki bulunmamaktadır.</p>
                                    @endif
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                @if($roles->isNotEmpty())
                <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-primary-200);">
                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
                    <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Rolleri Güncelle</button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

@if($roles->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerRoleCheckboxes = document.querySelectorAll('.customer-role-checkbox');
    const systemRoleCheckboxes = document.querySelectorAll('.system-role-checkbox');

    function toggleRoles() {
        const hasCustomerRole = Array.from(customerRoleCheckboxes).some(cb => cb.checked);
        const hasSystemRole = Array.from(systemRoleCheckboxes).some(cb => cb.checked);
        
        if (hasCustomerRole) {
            // Müşteri rolü seçildiyse sistem rolleri devre dışı bırak
            systemRoleCheckboxes.forEach(cb => {
                cb.disabled = true;
                if (cb.checked) {
                    cb.checked = false;
                }
            });
        } else {
            systemRoleCheckboxes.forEach(cb => {
                cb.disabled = false;
            });
        }
        
        if (hasSystemRole) {
            // Sistem rolü seçildiyse müşteri rolleri devre dışı bırak
            customerRoleCheckboxes.forEach(cb => {
                cb.disabled = true;
                if (cb.checked) {
                    cb.checked = false;
                }
            });
        } else {
            customerRoleCheckboxes.forEach(cb => {
                cb.disabled = false;
            });
        }
    }

    // İlk yüklemede kontrol et
    toggleRoles();

    // Müşteri rolü değişikliklerini dinle
    customerRoleCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.checked) {
                systemRoleCheckboxes.forEach(systemCb => {
                    systemCb.checked = false;
                });
            }
            toggleRoles();
        });
    });

    // Sistem rolü değişikliklerini dinle
    systemRoleCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.checked) {
                customerRoleCheckboxes.forEach(customerCb => {
                    customerCb.checked = false;
                });
            }
            toggleRoles();
        });
    });
});
</script>
@endif
@endsection
