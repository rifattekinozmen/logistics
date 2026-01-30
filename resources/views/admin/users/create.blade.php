@extends('layouts.app')

@section('title', 'Yeni Kullanıcı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Kullanıcı Oluştur</h2>
        <p class="text-secondary mb-0">Yeni bir kullanıcı kaydı oluşturun</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-light">
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

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Ad Soyad <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('name') is-invalid border-danger @enderror" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Kullanıcı Adı</label>
                <input type="text" name="username" value="{{ old('username') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('username') is-invalid border-danger @enderror" placeholder="ornek_kullanici">
                <small class="text-secondary">Sadece harf, rakam, tire ve alt çizgi içerebilir</small>
                @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">E-posta <span class="text-danger">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('email') is-invalid border-danger @enderror" required>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Telefon</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('phone') is-invalid border-danger @enderror">
                @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Şifre <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('password') is-invalid border-danger @enderror" required>
                <small class="text-secondary">En az 8 karakter olmalıdır</small>
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Şifre (Tekrar) <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" class="form-control border-primary-200 focus:border-primary focus:ring-primary" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select border-primary-200 focus:border-primary focus:ring-primary @error('status') is-invalid border-danger @enderror" required>
                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Pasif</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12" id="system-roles-section">
                <label class="form-label fw-semibold text-dark mb-3">Sistem Rolleri</label>
                <div class="row g-3 mb-4">
                    @forelse($systemRoles ?? [] as $role)
                    <div class="col-md-4">
                        <div class="form-check p-3 border rounded-3xl hover:shadow-sm transition-all" style="border-color: var(--bs-primary-200);">
                            <input class="form-check-input system-role-checkbox" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}" {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label w-100" for="role_{{ $role->id }}">
                                <div class="fw-semibold text-dark">{{ $role->name }}</div>
                                @if($role->description)
                                <small class="text-secondary">{{ $role->description }}</small>
                                @endif
                            </label>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <p class="text-secondary small mb-0">Sistem rolü bulunmuyor.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            @if(($customerRoles ?? [])->isNotEmpty())
            <div class="col-12 border-top pt-4 mt-4" style="border-color: var(--bs-primary-200);">
                <label class="form-label fw-semibold text-dark mb-3">
                    <span class="material-symbols-outlined align-middle" style="font-size: 1.25rem;">store</span>
                    Müşteri Portalı Rolleri
                </label>
                <div class="row g-3">
                    @foreach($customerRoles as $role)
                    <div class="col-md-4">
                        <div class="form-check p-3 border rounded-3xl hover:shadow-sm transition-all bg-success-50" style="border-color: var(--bs-success-200);">
                            <input class="form-check-input customer-role-checkbox" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}" {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label w-100" for="role_{{ $role->id }}">
                                <div class="fw-semibold text-dark d-flex align-items-center gap-2">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">store</span>
                                    {{ $role->name }}
                                </div>
                                @if($role->description)
                                <small class="text-secondary">{{ $role->description }}</small>
                                @endif
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
                @error('roles.*')
                <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-primary-200);">
            <a href="{{ route('admin.users.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Kullanıcı Oluştur</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerRoleCheckboxes = document.querySelectorAll('.customer-role-checkbox');
    const systemRoleCheckboxes = document.querySelectorAll('.system-role-checkbox');
    const systemRolesSection = document.getElementById('system-roles-section');

    function toggleSystemRoles() {
        const hasCustomerRole = Array.from(customerRoleCheckboxes).some(cb => cb.checked);
        
        if (hasCustomerRole) {
            // Müşteri rolü seçildiyse sistem rolleri gizle ve temizle
            systemRolesSection.style.display = 'none';
            systemRoleCheckboxes.forEach(cb => {
                cb.checked = false;
                cb.disabled = true;
            });
        } else {
            // Müşteri rolü seçilmediyse sistem rolleri göster
            systemRolesSection.style.display = 'block';
            systemRoleCheckboxes.forEach(cb => {
                cb.disabled = false;
            });
        }
    }

    function toggleCustomerRoles() {
        const hasSystemRole = Array.from(systemRoleCheckboxes).some(cb => cb.checked);
        
        if (hasSystemRole) {
            // Sistem rolü seçildiyse müşteri rolleri temizle
            customerRoleCheckboxes.forEach(cb => {
                cb.checked = false;
                cb.disabled = true;
            });
        } else {
            // Sistem rolü seçilmediyse müşteri rolleri aktif
            customerRoleCheckboxes.forEach(cb => {
                cb.disabled = false;
            });
        }
    }

    // İlk yüklemede kontrol et
    toggleSystemRoles();
    toggleCustomerRoles();

    // Müşteri rolü değişikliklerini dinle
    customerRoleCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.checked) {
                // Müşteri rolü seçildi, sistem rolleri temizle
                systemRoleCheckboxes.forEach(systemCb => {
                    systemCb.checked = false;
                });
            }
            toggleSystemRoles();
        });
    });

    // Sistem rolü değişikliklerini dinle
    systemRoleCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.checked) {
                // Sistem rolü seçildi, müşteri rolleri temizle
                customerRoleCheckboxes.forEach(customerCb => {
                    customerCb.checked = false;
                });
            }
            toggleCustomerRoles();
        });
    });
});
</script>
@endsection
