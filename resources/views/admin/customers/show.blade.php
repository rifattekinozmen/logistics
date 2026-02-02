@extends('layouts.app')

@section('title', 'Müşteri Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Müşteri Detayı</h2>
        <p class="text-secondary mb-0">{{ $customer->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-primary">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">edit</span>
            Düzenle
        </a>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span>
            Geri Dön
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4" style="border-color: var(--bs-customers-200);">
            <h3 class="h4 fw-bold text-dark mb-4">Müşteri Bilgileri</h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Müşteri Adı</label>
                    <p class="fw-bold text-dark mb-0">{{ $customer->name }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Durum</label>
                    <div>
                        @if($customer->status == 1)
                            <span class="badge bg-success-200 text-success px-3 py-2 rounded-pill fw-semibold">Aktif</span>
                        @else
                            <span class="badge bg-danger-200 text-danger px-3 py-2 rounded-pill fw-semibold">Pasif</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">E-posta</label>
                    <p class="text-dark mb-0">{{ $customer->email ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Telefon</label>
                    <p class="text-dark mb-0">{{ $customer->phone ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Vergi Numarası</label>
                    <p class="text-dark mb-0">{{ $customer->tax_number ?? '-' }}</p>
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-semibold text-secondary">Adres</label>
                    <p class="text-dark mb-0">{{ $customer->address ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div id="favorite-addresses" class="bg-white rounded-3xl shadow-sm border p-4 mb-4" style="border-color: var(--bs-customers-200);">
            @php $addrsWithCoords = $customer->favoriteAddresses->filter(fn($a) => $a->latitude !== null && $a->longitude !== null); @endphp
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <h3 class="h4 fw-bold text-dark mb-0">Favori / Teslimat Adresleri</h3>
                <div class="d-flex align-items-center gap-2">
                    @if($addrsWithCoords->isNotEmpty())
                        <button type="button" id="adminBtnShowMap" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 1rem;">map</span>
                            Haritada Görüntüle
                        </button>
                    @endif
                    <span class="badge bg-primary-200 text-primary px-3 py-2 rounded-pill fw-semibold">{{ $customer->favoriteAddresses->count() }} Adres</span>
                </div>
            </div>
            @if($customer->favoriteAddresses->count() > 0)
                @if($addrsWithCoords->isNotEmpty())
                <div id="adminMapContainer" class="rounded-3xl overflow-hidden mb-4" style="display: none;">
                    <div id="adminAddressMap" style="height: 360px;"></div>
                </div>
                <script>window.ADMIN_FAVORITE_ADDRESSES_MAP_DATA = @json($addrsWithCoords->map(function ($a) { return ['name' => $a->name, 'lat' => (float) $a->latitude, 'lng' => (float) $a->longitude]; })->values());</script>
                @endif
                <div class="d-flex flex-column gap-3">
                    @foreach($customer->favoriteAddresses as $addr)
                        <div class="border rounded-3xl p-3 bg-light">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="fw-bold text-dark">{{ $addr->name }}</span>
                                <span class="badge bg-primary-200 text-primary rounded-pill px-2 py-1 small">
                                    {{ match($addr->type) { 'pickup' => 'Alış', 'delivery' => 'Teslimat', 'both' => 'Her İkisi', default => $addr->type } }}
                                </span>
                            </div>
                            <p class="text-secondary small mb-1">{{ $addr->address }}</p>
                            @if($addr->latitude !== null && $addr->longitude !== null)
                                <p class="text-secondary small mb-0">
                                    <span class="material-symbols-outlined align-middle" style="font-size: 0.75rem;">location_on</span>
                                    {{ number_format((float) $addr->latitude, 6) }}, {{ number_format((float) $addr->longitude, 6) }}
                                </p>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <a href="https://www.google.com/maps?q={{ (float) $addr->latitude }},{{ (float) $addr->longitude }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">Google Maps</a>
                                    <a href="https://www.openstreetmap.org/?mlat={{ (float) $addr->latitude }}&mlon={{ (float) $addr->longitude }}&zoom=17" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">OpenStreetMap</a>
                                </div>
                            @endif
                            @if($addr->contact_name || $addr->contact_phone)
                                <p class="text-secondary small mb-0">{{ $addr->contact_name ?? '' }}{{ $addr->contact_name && $addr->contact_phone ? ' · ' : '' }}{{ $addr->contact_phone ?? '' }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 2rem;">location_off</span>
                    <p class="text-secondary mb-0 small">Bu müşteriye ait favori veya teslimat adresi yok.</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-info-200);">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h3 class="h4 fw-bold text-dark mb-0">Siparişler</h3>
                <span class="badge bg-info-200 text-info px-3 py-2 rounded-pill fw-semibold">{{ $customer->orders->count() }} Sipariş</span>
            </div>
            @if($customer->orders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-info-200">
                            <tr>
                                <th class="border-0 fw-semibold text-secondary small">Sipariş No</th>
                                <th class="border-0 fw-semibold text-secondary small">Tarih</th>
                                <th class="border-0 fw-semibold text-secondary small">Durum</th>
                                <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customer->orders as $order)
                            <tr>
                                <td class="align-middle">
                                    <span class="fw-bold text-dark">#{{ $order->id }}</span>
                                </td>
                                <td class="align-middle">
                                    <small class="text-secondary">{{ $order->created_at ? $order->created_at->format('d.m.Y H:i') : '-' }}</small>
                                </td>
                                <td class="align-middle">
                                    <span class="badge bg-primary-200 text-primary px-3 py-2 rounded-pill fw-semibold">Sipariş</span>
                                </td>
                                <td class="align-middle text-end">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                        <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="d-flex flex-column align-items-center gap-2">
                        <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">shopping_cart</span>
                        <p class="text-secondary mb-0">Bu müşteriye ait henüz sipariş bulunmuyor.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-customers-200);">
            <h3 class="h4 fw-bold text-dark mb-4">Hızlı İşlemler</h3>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2">
                    <span class="material-symbols-outlined">edit</span>
                    Düzenle
                </a>
                <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Bu müşteriyi silmek istediğinize emin misiniz?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined">delete</span>
                        Sil
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border p-4 mt-4" style="border-color: var(--bs-info-200);">
            <h3 class="h4 fw-bold text-dark mb-4">İstatistikler</h3>
            <div class="d-flex flex-column gap-3">
                <div>
                    <label class="form-label small fw-semibold text-secondary mb-1">Toplam Sipariş</label>
                    <p class="h5 fw-bold text-dark mb-0">{{ $customer->orders->count() }}</p>
                </div>
                <div>
                    <label class="form-label small fw-semibold text-secondary mb-1">Kayıt Tarihi</label>
                    <p class="text-dark mb-0">{{ $customer->created_at ? $customer->created_at->format('d.m.Y H:i') : '-' }}</p>
                </div>
                @if($customer->updated_at)
                <div>
                    <label class="form-label small fw-semibold text-secondary mb-1">Son Güncelleme</label>
                    <p class="text-dark mb-0">{{ $customer->updated_at->format('d.m.Y H:i') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($addrsWithCoords->isNotEmpty())
@push('scripts')
<script>
(function () {
    var btn = document.getElementById('adminBtnShowMap');
    var container = document.getElementById('adminMapContainer');
    if (!btn || !container || typeof window.ADMIN_FAVORITE_ADDRESSES_MAP_DATA === 'undefined' || !window.ADMIN_FAVORITE_ADDRESSES_MAP_DATA.length) return;
    var mapInitialized = false;
    btn.addEventListener('click', function () {
        container.style.display = container.style.display === 'none' ? 'block' : 'none';
        if (container.style.display === 'block' && !mapInitialized) {
            mapInitialized = true;
            var linkCss = document.createElement('link');
            linkCss.rel = 'stylesheet';
            linkCss.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(linkCss);
            var script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.onload = function () {
                var L = window.L;
                var data = window.ADMIN_FAVORITE_ADDRESSES_MAP_DATA;
                var center = data[0];
                var map = L.map('adminAddressMap').setView([center.lat, center.lng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
                var bounds = [];
                data.forEach(function (p) {
                    bounds.push([p.lat, p.lng]);
                    L.marker([p.lat, p.lng]).addTo(map).bindPopup(p.name || 'Adres');
                });
                if (data.length > 1) map.fitBounds(bounds, { padding: [30, 30] });
            };
            document.head.appendChild(script);
        }
    });
})();
</script>
@endpush
@endif
@endsection
