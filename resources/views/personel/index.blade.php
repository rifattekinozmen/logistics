@extends('layouts.app')

@section('title', 'Personel - Logistics')
@section('page-title', 'Personel')
@section('page-subtitle', 'Tüm personeli görüntüleyin ve yönetin')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3xl" role="alert">
    <span class="material-symbols-outlined me-2">check_circle</span>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Personel</h2>
        <p class="text-secondary mb-0">Tüm personeli görüntüleyin ve yönetin</p>
    </div>
    <a href="{{ route('personel.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni Personel
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4 mb-4" style="border-color: var(--bs-primary-200);">
    <form method="GET" action="{{ route('personel.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="aktif" class="form-select border-primary-200 focus:border-primary focus:ring-primary">
                <option value="">Tümü</option>
                <option value="1" {{ request('aktif') == '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('aktif') == '0' ? 'selected' : '' }}>Pasif</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Departman</label>
            <input type="text" name="departman" value="{{ request('departman') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary" placeholder="Departman ara...">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Pozisyon</label>
            <input type="text" name="pozisyon" value="{{ request('pozisyon') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary" placeholder="Pozisyon ara...">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 fw-semibold text-secondary small">Ad Soyad</th>
                    <th class="border-0 fw-semibold text-secondary small">Email</th>
                    <th class="border-0 fw-semibold text-secondary small">Telefon</th>
                    <th class="border-0 fw-semibold text-secondary small">Departman</th>
                    <th class="border-0 fw-semibold text-secondary small">Pozisyon</th>
                    <th class="border-0 fw-semibold text-secondary small">İşe Başlama</th>
                    <th class="border-0 fw-semibold text-secondary small">Maaş</th>
                    <th class="border-0 fw-semibold text-secondary small">Durum</th>
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($personels as $personel)
                <tr>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">{{ $personel->ad_soyad }}</span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $personel->email ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $personel->telefon ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $personel->departman ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $personel->pozisyon ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $personel->ise_baslama_tarihi ? $personel->ise_baslama_tarihi->format('d.m.Y') : '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $personel->maas ? number_format($personel->maas, 2, ',', '.') . ' ₺' : '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <span class="badge bg-{{ $personel->aktif ? 'success-200 text-success' : 'secondary-200 text-secondary' }} px-3 py-2 rounded-pill fw-semibold">
                            {{ $personel->aktif ? 'Aktif' : 'Pasif' }}
                        </span>
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('personel.show', $personel->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('personel.edit', $personel->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0 hover:bg-primary hover:text-white transition-all" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('personel.destroy', $personel->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu personeli silmek istediğinize emin misiniz?');">
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
                    <td colspan="9" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">groups</span>
                            <p class="text-secondary mb-0">Henüz personel bulunmuyor.</p>
                            <a href="{{ route('personel.create') }}" class="btn btn-primary btn-sm mt-2">İlk Personeli Ekle</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($personels->hasPages())
    <div class="p-4 border-top">
        {{ $personels->links() }}
    </div>
    @endif
</div>
@endsection
