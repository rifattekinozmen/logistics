@extends('layouts.customer-app')

@section('title', 'Favori Adreslerim - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">location_on</span>
            <h2 class="h3 fw-bold text-dark mb-0">Favori Adreslerim</h2>
        </div>
        <p class="text-secondary mb-0">Sık kullandığınız adresleri kaydedin ve hızlıca seçin</p>
    </div>
    <button type="button" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni Adres Ekle
    </button>
</div>

<div class="row g-4">
    @forelse($addresses as $address)
        <div class="col-md-6">
            <div class="bg-white rounded-3xl shadow-sm border p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="grow">
                        <h5 class="fw-bold text-dark mb-1">{{ $address->name }}</h5>
                        <span class="badge bg-primary-200 text-primary rounded-pill px-3 py-1">
                            {{ match($address->type) { 'pickup' => 'Alış', 'delivery' => 'Teslimat', 'both' => 'Her İkisi', default => $address->type } }}
                        </span>
                    </div>
                    <form method="POST" action="{{ route('customer.favorite-addresses.destroy', $address) }}" class="d-inline" onsubmit="return confirm('Bu adresi silmek istediğinizden emin misiniz?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <span class="material-symbols-outlined" style="font-size: 1rem;">delete</span>
                        </button>
                    </form>
                </div>
                <p class="text-secondary mb-2">{{ $address->address }}</p>
                @if($address->contact_name || $address->contact_phone)
                    <div class="small text-secondary mb-2">
                        @if($address->contact_name)
                            <span class="material-symbols-outlined align-middle" style="font-size: 0.875rem;">person</span>
                            {{ $address->contact_name }}
                        @endif
                        @if($address->contact_phone)
                            <span class="material-symbols-outlined align-middle ms-2" style="font-size: 0.875rem;">phone</span>
                            {{ $address->contact_phone }}
                        @endif
                    </div>
                @endif
                @if($address->notes)
                    <p class="small text-secondary mb-0">{{ $address->notes }}</p>
                @endif
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="bg-white rounded-3xl shadow-sm border p-5 text-center">
                <span class="material-symbols-outlined text-secondary mb-2 d-block" style="font-size: 3rem; opacity: 0.3;">location_off</span>
                <p class="text-secondary mb-3">Henüz favori adres eklenmemiş.</p>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                    <span class="material-symbols-outlined" style="font-size: 1rem;">add</span>
                    İlk Adresi Ekle
                </button>
            </div>
        </div>
    @endforelse
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('customer.favorite-addresses.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Favori Adres Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold text-dark">Adres Adı <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label fw-semibold text-dark">Adres Türü <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="pickup">Alış Adresi</option>
                            <option value="delivery">Teslimat Adresi</option>
                            <option value="both">Her İkisi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label fw-semibold text-dark">Adres <span class="text-danger">*</span></label>
                        <textarea name="address" id="address" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="contact_name" class="form-label fw-semibold text-dark">İletişim Adı</label>
                            <input type="text" name="contact_name" id="contact_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="contact_phone" class="form-label fw-semibold text-dark">İletişim Telefonu</label>
                            <input type="text" name="contact_phone" id="contact_phone" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="notes" class="form-label fw-semibold text-dark">Notlar</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
