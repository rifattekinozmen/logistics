@extends('layouts.app')

@section('title', 'Kullanıcılar - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Kullanıcılar</h2>
        <p class="text-secondary mb-0">Tüm kullanıcıları görüntüleyin ve yönetin</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni Kullanıcı
    </a>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="group" color="primary" col="col-md-6" />
    <x-index-stat-card title="Aktif" :value="$stats['active'] ?? 0" icon="check_circle" color="success" col="col-md-6" />
</div>

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pasif</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold text-dark">Kullanıcı Tipi</label>
            <select name="user_type" class="form-select">
                <option value="">Tümü</option>
                <option value="customer" {{ request('user_type') === 'customer' ? 'selected' : '' }}>Müşteri Kullanıcıları</option>
                <option value="system" {{ request('user_type') === 'system' ? 'selected' : '' }}>Sistem Kullanıcıları</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold text-dark">Rol</label>
            <select name="role_id" class="form-select">
                <option value="">Tüm Roller</option>
                @foreach($roles as $role)
                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Arama</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Ad, e-posta veya kullanıcı adı ile ara..." class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 fw-semibold text-secondary small">Kullanıcı</th>
                    <th class="border-0 fw-semibold text-secondary small">E-posta</th>
                    <th class="border-0 fw-semibold text-secondary small">Kullanıcı Adı</th>
                    <th class="border-0 fw-semibold text-secondary small" id="roles-column-header">Roller</th>
                    <th class="border-0 fw-semibold text-secondary small">Durum</th>
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    @php
                        $isCustomerUser = $user->roles->contains(function($role) {
                            return in_array($role->name, ['customer', 'customer_user', 'customer_viewer']);
                        });
                    @endphp
                <tr class="{{ $isCustomerUser ? 'bg-success-50' : '' }}">
                    <td class="align-middle">
                        <div class="d-flex align-items-center gap-2">
                            @if($user->avatar)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                            @else
                                <div class="rounded-circle border d-flex align-items-center justify-content-center bg-white text-secondary" style="width: 32px; height: 32px;">
                                    <span class="material-symbols-outlined" style="font-size: 18px;">{{ $isCustomerUser ? 'store' : 'person' }}</span>
                                </div>
                            @endif
                            <div>
                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                    {{ $user->name }}
                                    @if($isCustomerUser)
                                        <span class="badge bg-success-200 text-success px-2 py-1 rounded-pill small">
                                            <span class="material-symbols-outlined" style="font-size: 0.75rem; vertical-align: middle;">store</span>
                                            Müşteri
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $user->email }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $user->username ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        @if($user->roles->isNotEmpty() && !$isCustomerUser)
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($user->roles as $role)
                                    @php
                                        $isCustomerRole = in_array($role->name, ['customer', 'customer_user', 'customer_viewer']);
                                        $badgeClass = $isCustomerRole ? 'bg-success-200 text-success' : 'bg-primary-200 text-primary';
                                    @endphp
                                    <span class="badge {{ $badgeClass }} px-2 py-1 rounded-pill small">
                                        {{ $role->name }}
                                        @if($isCustomerRole)
                                            <span class="material-symbols-outlined" style="font-size: 0.875rem; vertical-align: middle;">store</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-secondary small">-</span>
                        @endif
                    </td>
                    <td class="align-middle">
                        @if($user->status == 1)
                            <span class="badge bg-success-200 text-success px-3 py-2 rounded-pill fw-semibold">Aktif</span>
                        @else
                            <span class="badge bg-danger-200 text-danger px-3 py-2 rounded-pill fw-semibold">Pasif</span>
                        @endif
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0 hover:bg-primary hover:text-white transition-all" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            @if(!$isCustomerUser)
                            <a href="{{ route('admin.users.edit-roles', $user->id) }}" class="btn btn-sm bg-warning-200 text-warning border-0 hover:bg-warning hover:text-white transition-all" title="Roller">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">badge</span>
                            </a>
                            @endif
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm bg-danger-200 text-danger border-0 hover:bg-danger hover:text-white transition-all" title="Sil">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">person</span>
                            <p class="text-secondary mb-0">Henüz kullanıcı bulunmuyor.</p>
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm mt-2">İlk Kullanıcıyı Oluştur</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="p-4 border-top">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
