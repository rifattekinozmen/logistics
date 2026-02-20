@extends('layouts.app')

@section('title', 'Dashboard - Logistics')

@section('content')
<div class="mb-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">Hoş Geldiniz, {{ Auth::user()->name }}!</h1>
            <p class="text-secondary mb-0">Logistics yönetim paneline hoş geldiniz. İşte bugünün özeti.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dashboard.export') }}" class="btn btn-outline-primary d-flex align-items-center gap-2 text-decoration-none" style="border-color: var(--bs-primary); color: var(--bs-primary);">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">download</span>
                <span>Rapor İndir</span>
            </a>
        </div>
    </div>

    <!-- İstatistik Kartları -->
    <div class="row g-4 mb-4">
        <!-- Siparişler -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.orders.index') }}" class="text-decoration-none text-body d-block h-100">
                <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden transition-all hover:shadow-md" style="border-color: rgba(61, 105, 206, 0.12);">
                    <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, var(--bs-primary) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                    <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                        <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--bs-primary-200), var(--bs-primary-red-200));">
                            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">shopping_cart</span>
                        </div>
                        <span class="badge rounded-pill px-3 py-1 fw-semibold bg-primary-200 text-primary">+{{ round(($stats['orders_pending'] / max($stats['orders_count'], 1)) * 100) }}%</span>
                    </div>
                    <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($stats['orders_count'] ?? 0) }}</h3>
                    <p class="small fw-semibold text-secondary mb-2">Toplam Sipariş</p>
                    <div class="d-flex gap-3">
                        <div class="flex-grow-1">
                            <p class="small text-secondary mb-0">Bekleyen</p>
<p class="small fw-bold mb-0 text-primary-red-200-text">{{ $stats['orders_pending'] ?? 0 }}</p>
                    </div>
                    <div class="flex-grow-1">
                        <p class="small text-secondary mb-0">Teslim Edilen</p>
                        <p class="small fw-bold mb-0 text-success">{{ $stats['orders_delivered'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Sevkiyatlar -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.shipments.index') }}" class="text-decoration-none text-body d-block h-100">
                <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden transition-all hover:shadow-md" style="border-color: rgba(61, 105, 206, 0.12);">
                    <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, var(--bs-primary) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                    <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                        <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--bs-primary-200), var(--bs-primary-red-200));">
                            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">local_shipping</span>
                        </div>
                        <span class="badge rounded-pill px-3 py-1 fw-semibold bg-primary-200 text-primary">{{ $stats['shipments_active'] ?? 0 }} Aktif</span>
                    </div>
                    <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($stats['shipments_count'] ?? 0) }}</h3>
                    <p class="small fw-semibold text-secondary mb-2">Toplam Sevkiyat</p>
                    <div class="d-flex gap-3">
                        <div class="flex-grow-1">
                            <p class="small text-secondary mb-0">Aktif</p>
                            <p class="small fw-bold mb-0 text-primary">{{ $stats['shipments_active'] ?? 0 }}</p>
                        </div>
                        <div class="flex-grow-1">
                            <p class="small text-secondary mb-0">Tamamlanan</p>
                            <p class="small fw-bold mb-0 text-success">{{ ($stats['shipments_count'] ?? 0) - ($stats['shipments_active'] ?? 0) }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Araçlar -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.vehicles.index') }}" class="text-decoration-none text-body d-block h-100">
                <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden transition-all hover:shadow-md" style="border-color: rgba(61, 105, 206, 0.12);">
                    <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, var(--bs-primary) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                    <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                        <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--bs-primary-red-200), var(--bs-primary-200));">
                            <span class="material-symbols-outlined text-primary-red" style="font-size: 1.75rem;">directions_car</span>
                        </div>
                        <span class="badge rounded-pill px-3 py-1 fw-semibold bg-primary-red-200 text-primary-red-200-text">{{ $stats['vehicles_active'] ?? 0 }} Müsait</span>
                    </div>
                    <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($stats['vehicles_count'] ?? 0) }}</h3>
                    <p class="small fw-semibold text-secondary mb-2">Toplam Araç</p>
                    <div class="d-flex gap-3">
                        <div class="flex-grow-1">
                            <p class="small text-secondary mb-0">Müsait</p>
                            <p class="small fw-bold mb-0 text-success">{{ $stats['vehicles_active'] ?? 0 }}</p>
                        </div>
                        <div class="flex-grow-1">
                            <p class="small text-secondary mb-0">Kullanımda</p>
                            <p class="small fw-bold mb-0 text-primary-red-200-text">{{ ($stats['vehicles_count'] ?? 0) - ($stats['vehicles_active'] ?? 0) }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Müşteriler -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.customers.index') }}" class="text-decoration-none text-body d-block h-100">
                <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden transition-all hover:shadow-md" style="border-color: rgba(196, 30, 90, 0.12);">
                    <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, var(--bs-primary-red) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                    <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                        <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--bs-primary-red-200), var(--bs-primary-200));">
                            <span class="material-symbols-outlined text-primary-red" style="font-size: 1.75rem;">groups</span>
                        </div>
                        @php
                            $changePercent = $stats['customers_change_percent'] ?? 0;
                            $changeLabel = $changePercent > 0 ? '+' . $changePercent . '%' : ($changePercent < 0 ? $changePercent . '%' : '0%');
                        @endphp
                        <span class="badge rounded-pill px-3 py-1 fw-semibold bg-primary-red-200 text-primary-red-200-text">{{ $changeLabel }}</span>
                    </div>
                    <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($stats['customers_count'] ?? 0) }}</h3>
                    <p class="small fw-semibold text-secondary mb-2">Aktif Müşteri</p>
                    <div class="d-flex gap-3">
                        <div class="flex-grow-1">
                            <p class="small text-secondary mb-0">Personel</p>
                            <p class="small fw-bold mb-0 text-primary">{{ $stats['employees_count'] ?? 0 }}</p>
                        </div>
                        <div class="flex-grow-1">
                            <p class="small text-secondary mb-0">Depo</p>
                            <p class="small fw-bold mb-0 text-primary">{{ $stats['warehouses_count'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Finans & Operasyon Özet Kartları -->
    <div class="row g-4 mb-4">
        <!-- Finans Özet -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.payments.index') }}" class="text-decoration-none text-body d-block h-100">
                <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden transition-all hover:shadow-md" style="border-color: rgba(196, 30, 90, 0.12);">
                    <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, var(--bs-primary-red) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                    <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                        <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--bs-primary-red-200), var(--bs-primary-200));">
                            <span class="material-symbols-outlined text-primary-red" style="font-size: 1.75rem;">warning</span>
                        </div>
                        <span class="badge rounded-pill px-3 py-1 fw-semibold bg-primary-red-200 text-primary-red-200-text">Geciken</span>
                    </div>
                    <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($financeData['overdue']['total_amount'] ?? 0, 2) }} ₺</h3>
                    <p class="small fw-semibold text-secondary mb-2">Geciken Ödemeler</p>
                    <p class="small text-secondary mb-0">{{ $financeData['overdue']['count'] ?? 0 }} adet ödeme</p>
                </div>
            </a>
        </div>

        <!-- Bugün Vadesi Gelenler -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.payments.index') }}" class="text-decoration-none text-body d-block h-100">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden transition-all hover:shadow-md" style="border-color: rgba(61, 105, 206, 0.12);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, var(--bs-primary) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--bs-primary-200), var(--bs-primary-red-200));">
                        <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">today</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold bg-primary-200 text-primary">Bugün</span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($financeData['due_today']['total_amount'] ?? 0, 2) }} ₺</h3>
                <p class="small fw-semibold text-secondary mb-2">Bugün Vadesi Gelen</p>
                <p class="small text-secondary mb-0">{{ $financeData['due_today']['count'] ?? 0 }} adet ödeme</p>
            </div>
            </a>
        </div>

        <!-- 7 Gün İçinde -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.payments.index') }}" class="text-decoration-none text-body d-block h-100">
                <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden transition-all hover:shadow-md" style="border-color: rgba(61, 105, 206, 0.12);">
                    <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, var(--bs-primary) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                    <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                        <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--bs-primary-200), var(--bs-primary-red-200));">
                            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">schedule</span>
                        </div>
                        <span class="badge rounded-pill px-3 py-1 fw-semibold bg-primary-200 text-primary">7 Gün</span>
                    </div>
                    <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($financeData['due_in_7_days']['total_amount'] ?? 0, 2) }} ₺</h3>
                    <p class="small fw-semibold text-secondary mb-2">7 Gün İçinde</p>
                    <p class="small text-secondary mb-0">{{ $financeData['due_in_7_days']['count'] ?? 0 }} adet ödeme</p>
                </div>
            </a>
        </div>

        <!-- Bu Ay Ödenenler -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.payments.index') }}" class="text-decoration-none text-body d-block h-100">
                <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden transition-all hover:shadow-md" style="border-color: rgba(196, 30, 90, 0.12);">
                    <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, var(--bs-primary-red) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                    <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                        <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--bs-primary-red-200), var(--bs-primary-200));">
                            <span class="material-symbols-outlined text-primary-red" style="font-size: 1.75rem;">check_circle</span>
                        </div>
                        <span class="badge rounded-pill px-3 py-1 fw-semibold bg-primary-red-200 text-primary-red-200-text">Bu Ay</span>
                    </div>
                    <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($financeData['paid_this_month']['total_amount'] ?? 0, 2) }} ₺</h3>
                    <p class="small fw-semibold text-secondary mb-2">Bu Ay Ödenen</p>
                    <p class="small text-secondary mb-0">{{ $financeData['paid_this_month']['count'] ?? 0 }} adet ödeme</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Operasyon Performans Kartları -->
    <div class="row g-4 mb-4">
        <!-- Teslimat Performans Puanı -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden" style="border-color: rgba(61, 105, 206, 0.12);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, var(--bs-primary) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--bs-primary-200), var(--bs-primary-red-200));">
                        <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">speed</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold bg-primary-200 text-primary">
                        @if(($operationsData['delivery_performance_score'] ?? 0) >= 90) Mükemmel
                        @elseif(($operationsData['delivery_performance_score'] ?? 0) >= 70) İyi
                        @else Orta
                        @endif
                    </span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($operationsData['delivery_performance_score'] ?? 0, 1) }}</h3>
                <p class="small fw-semibold text-secondary mb-2">Teslimat Performans Puanı</p>
                <p class="small text-secondary mb-0">0-100 arası skor</p>
            </div>
        </div>

        <!-- Geciken Sipariş Oranı -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden" style="border-color: rgba(196, 30, 90, 0.12);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, var(--bs-primary-red) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--bs-primary-red-200), var(--bs-primary-200));">
                        <span class="material-symbols-outlined text-primary-red" style="font-size: 1.75rem;">schedule</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold bg-primary-red-200 text-primary-red-200-text">Gecikme</span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($operationsData['delayed_order_rate']['rate'] ?? 0, 1) }}%</h3>
                <p class="small fw-semibold text-secondary mb-2">Geciken Sipariş Oranı</p>
                <p class="small text-secondary mb-0">{{ $operationsData['delayed_order_rate']['count'] ?? 0 }} / {{ $operationsData['delayed_order_rate']['total'] ?? 0 }} sipariş</p>
            </div>
        </div>

        <!-- Araç Doluluk Oranı -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden" style="border-color: rgba(61, 105, 206, 0.12);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, var(--bs-primary) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--bs-primary-200), var(--bs-primary-red-200));">
                        <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">directions_car</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold bg-primary-200 text-primary">Kullanım</span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($operationsData['vehicle_utilization']['utilization_rate'] ?? 0, 1) }}%</h3>
                <p class="small fw-semibold text-secondary mb-2">Araç Doluluk Oranı</p>
                <p class="small text-secondary mb-0">{{ $operationsData['vehicle_utilization']['active_vehicles'] ?? 0 }} / {{ $operationsData['vehicle_utilization']['total_vehicles'] ?? 0 }} araç</p>
            </div>
        </div>

        <!-- Ortalama Teslimat Süresi -->
        <div class="col-md-6 col-lg-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 position-relative overflow-hidden" style="border-color: rgba(196, 30, 90, 0.12);">
                <div class="position-absolute top-0 end-0 opacity-5" style="width: 120px; height: 120px; background: radial-gradient(circle, var(--bs-primary-red) 0%, transparent 70%); transform: translate(30px, -30px);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                    <div class="rounded-2xl d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--bs-primary-red-200), var(--bs-primary-200));">
                        <span class="material-symbols-outlined text-primary-red" style="font-size: 1.75rem;">access_time</span>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 fw-semibold bg-primary-red-200 text-primary-red-200-text">Süre</span>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ $operationsData['average_delivery_time'] ? number_format($operationsData['average_delivery_time'], 1) : '-' }}</h3>
                <p class="small fw-semibold text-secondary mb-2">Ortalama Teslimat Süresi</p>
                <p class="small text-secondary mb-0">{{ $operationsData['average_delivery_time'] ? 'Saat' : 'Veri yok' }}</p>
            </div>
        </div>
    </div>

    <!-- SAP Entegrasyon KPI Kartları -->
    <div class="mb-2 mt-2">
        <h6 class="small text-muted fw-bold px-1" style="font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase;">SAP Entegrasyon Durumu</h6>
    </div>
    <div class="row g-3 mb-4">
        <!-- SAP Bekleyen -->
        <div class="col-md-4 col-lg-2">
            <div class="bg-white rounded-3xl shadow-sm border p-3 h-100" style="border-color: rgba(255, 193, 7, 0.3);">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-warning" style="font-size: 20px;">sync</span>
                    <span class="small fw-semibold text-secondary">Bekleyen SAP</span>
                </div>
                <h4 class="h3 fw-bold text-dark mb-0">{{ $sapStats->pending ?? 0 }}</h4>
                <p class="small text-secondary mb-0 mt-1">senkronize edilecek</p>
            </div>
        </div>
        <!-- SAP Senkronize -->
        <div class="col-md-4 col-lg-2">
            <div class="bg-white rounded-3xl shadow-sm border p-3 h-100" style="border-color: rgba(76, 175, 80, 0.3);">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-success" style="font-size: 20px;">check_circle</span>
                    <span class="small fw-semibold text-secondary">SAP Senkron</span>
                </div>
                <h4 class="h3 fw-bold text-dark mb-0">{{ $sapStats->synced ?? 0 }}</h4>
                <p class="small text-secondary mb-0 mt-1">doküman eşlendi</p>
            </div>
        </div>
        <!-- SAP Hata -->
        <div class="col-md-4 col-lg-2">
            <div class="bg-white rounded-3xl shadow-sm border p-3 h-100" style="border-color: rgba(244, 67, 54, 0.3);">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-danger" style="font-size: 20px;">error</span>
                    <span class="small fw-semibold text-secondary">SAP Hata</span>
                </div>
                <h4 class="h3 fw-bold text-dark mb-0">{{ $sapStats->errors ?? 0 }}</h4>
                <p class="small text-secondary mb-0 mt-1">başarısız kayıt</p>
            </div>
        </div>
        <!-- Faturalandırılan Siparişler -->
        <div class="col-md-4 col-lg-2">
            <a href="{{ route('admin.orders.index') }}?status=invoiced" class="text-decoration-none d-block h-100">
                <div class="bg-white rounded-3xl shadow-sm border p-3 h-100" style="border-color: rgba(61, 105, 206, 0.2);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-primary" style="font-size: 20px;">receipt_long</span>
                        <span class="small fw-semibold text-secondary">Faturalandı</span>
                    </div>
                    <h4 class="h3 fw-bold text-dark mb-0">{{ $stats['invoiced_orders'] ?? 0 }}</h4>
                    <p class="small text-secondary mb-0 mt-1">sipariş faturalandı</p>
                </div>
            </a>
        </div>
        <!-- İş Ortakları -->
        <div class="col-md-4 col-lg-2">
            <a href="{{ route('admin.business-partners.index') }}" class="text-decoration-none d-block h-100">
                <div class="bg-white rounded-3xl shadow-sm border p-3 h-100" style="border-color: rgba(61, 105, 206, 0.2);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-primary" style="font-size: 20px;">handshake</span>
                        <span class="small fw-semibold text-secondary">İş Ortakları</span>
                    </div>
                    <h4 class="h3 fw-bold text-dark mb-0">{{ $bpCount }}</h4>
                    <p class="small text-secondary mb-0 mt-1">aktif BP kaydı</p>
                </div>
            </a>
        </div>
        <!-- Aktif Fiyatlandırma Koşulları -->
        <div class="col-md-4 col-lg-2">
            <a href="{{ route('admin.pricing-conditions.index') }}" class="text-decoration-none d-block h-100">
                <div class="bg-white rounded-3xl shadow-sm border p-3 h-100" style="border-color: rgba(61, 105, 206, 0.2);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-primary" style="font-size: 20px;">price_check</span>
                        <span class="small fw-semibold text-secondary">Fiyatlandırma</span>
                    </div>
                    <h4 class="h3 fw-bold text-dark mb-0">{{ $activePricing }}</h4>
                    <p class="small text-secondary mb-0 mt-1">aktif koşul</p>
                </div>
            </a>
        </div>
    </div>

    <!-- AI Özet & Grafik ve Aktiviteler -->
    <div class="row g-4">
        <!-- AI Özet Kutusu -->
        <div class="col-lg-4 order-lg-1 order-2">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h3 class="h5 fw-bold text-dark mb-1">AI Özet</h3>
                        <p class="small text-secondary mb-0">Bugün dikkat edilmesi gerekenler</p>
                    </div>
                    <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">smart_toy</span>
                </div>

                @if(isset($aiReports) && $aiReports->isNotEmpty())
                    <div class="d-flex flex-column gap-3">
                        @foreach($aiReports as $report)
                            @php
                                $severityColors = [
                                    'low' => ['bg' => 'rgba(129, 199, 132, 0.12)', 'border' => 'rgba(129, 199, 132, 0.4)', 'text' => '#388e3c'],
                                    'medium' => ['bg' => 'rgba(255, 202, 40, 0.12)', 'border' => 'rgba(255, 202, 40, 0.4)', 'text' => '#f9a825'],
                                    'high' => ['bg' => 'rgba(239, 83, 80, 0.12)', 'border' => 'rgba(239, 83, 80, 0.4)', 'text' => '#c62828'],
                                ];
                                $colors = $severityColors[$report->severity] ?? $severityColors['low'];
                            @endphp
                            <div class="p-3 rounded-2xl border" style="background: {{ $colors['bg'] }}; border-color: {{ $colors['border'] }};">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="badge rounded-pill px-2 py-1 text-uppercase small" style="background: rgba(0,0,0,0.02); color: {{ $colors['text'] }};">
                                        {{ $report->type }}
                                    </span>
                                    <small class="text-secondary">{{ $report->generated_at?->diffForHumans() }}</small>
                                </div>
                                <p class="small mb-0" style="color: {{ $colors['text'] }};">{{ $report->summary_text }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 2.5rem; opacity: 0.4;">check_circle</span>
                        <p class="small text-secondary mb-0">AI şu an için kritik bir uyarı tespit etmedi.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Son Aktiviteler -->
        <div class="col-lg-8 order-lg-2 order-1">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h3 class="h4 fw-bold text-dark mb-1">Son Aktiviteler</h3>
                        <p class="small text-secondary mb-0">Sistemdeki son işlemler</p>
                    </div>
                    <a href="#" class="btn btn-link fw-semibold p-0 text-decoration-none d-flex align-items-center gap-2 text-primary" style="font-size: 0.875rem;">
                        <span>Tümünü Gör</span>
                        <span class="material-symbols-outlined" style="font-size: 1rem;">arrow_forward</span>
                    </a>
                </div>
                <div class="d-flex flex-column gap-3">
                    @forelse($recentActivities as $activity)
                    @php
                        $colorMap = [
                            'success' => ['bg' => 'var(--bs-primary)', 'badge' => 'var(--bs-primary-200)', 'text' => 'var(--bs-primary)'],
                            'info' => ['bg' => 'var(--bs-primary)', 'badge' => 'var(--bs-primary-200)', 'text' => 'var(--bs-primary)'],
                            'danger' => ['bg' => 'var(--bs-primary-red)', 'badge' => 'var(--bs-primary-red-200)', 'text' => 'var(--bs-primary-red)'],
                            'primary' => ['bg' => 'var(--bs-primary)', 'badge' => 'var(--bs-primary-200)', 'text' => 'var(--bs-primary)'],
                        ];
                        $colors = $colorMap[$activity['color']] ?? $colorMap['primary'];
                    @endphp
                    <div class="d-flex align-items-center gap-3 p-3 rounded-2xl border transition-all hover:shadow-sm position-relative" style="border-color: rgba(0,0,0,0.05); background: rgba(0,0,0,0.01);">
                        <div class="rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, var(--bs-primary-200), var(--bs-primary-red-200));">
                            <span class="material-symbols-outlined text-primary" style="font-size: 1.25rem;">{{ $activity['icon'] }}</span>
                        </div>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <p class="small fw-bold text-dark mb-1">{{ $activity['title'] }}</p>
                            <div class="d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined text-secondary" style="font-size: 0.875rem;">person</span>
                                <p class="small text-secondary mb-0">{{ $activity['description'] }}</p>
                                <span class="text-secondary">•</span>
                                <p class="small text-secondary mb-0">{{ $activity['time'] }}</p>
                            </div>
                        </div>
                        <span class="badge rounded-pill px-2 py-1 fw-semibold flex-shrink-0" style="background: {{ $colors['badge'] }}; color: {{ $colors['text'] }}; font-size: 0.75rem;">
                            {{ ucfirst($activity['color']) }}
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <span class="material-symbols-outlined text-secondary mb-3 d-block" style="font-size: 3rem; opacity: 0.3;">notifications_off</span>
                        <p class="text-secondary mb-0">Henüz aktivite bulunmuyor.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Hızlı Erişim -->
        <div class="col-lg-4 order-lg-3 order-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4 h-100">
                <div class="mb-4">
                    <h3 class="h4 fw-bold text-dark mb-1">Hızlı Erişim</h3>
                    <p class="small text-secondary mb-0">Sık kullanılan işlemler</p>
                </div>
                <div class="d-flex flex-column gap-3">
                    <a href="{{ route('admin.orders.create') }}" class="d-flex align-items-center gap-3 p-3 rounded-2xl border text-decoration-none transition-all hover:shadow-md position-relative overflow-hidden bg-primary-200 border-primary" style="border-color: rgba(61, 105, 206, 0.12) !important;">
                        <div class="rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, var(--bs-primary-200), var(--bs-primary-red-200));">
                            <span class="material-symbols-outlined text-primary">add_shopping_cart</span>
                        </div>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <p class="small fw-bold text-dark mb-0">Yeni Sipariş</p>
                            <p class="small text-secondary mb-0">Sipariş oluştur</p>
                        </div>
                        <span class="material-symbols-outlined text-secondary flex-shrink-0">arrow_forward</span>
                    </a>

                    <a href="{{ route('admin.shipments.create') }}" class="d-flex align-items-center gap-3 p-3 rounded-2xl border text-decoration-none transition-all hover:shadow-md bg-primary-200" style="border-color: rgba(61, 105, 206, 0.12) !important;">
                        <div class="rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, var(--bs-primary-red-200), var(--bs-primary-200));">
                            <span class="material-symbols-outlined text-primary-red">local_shipping</span>
                        </div>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <p class="small fw-bold text-dark mb-0">Yeni Sevkiyat</p>
                            <p class="small text-secondary mb-0">Sevkiyat oluştur</p>
                        </div>
                        <span class="material-symbols-outlined text-secondary flex-shrink-0">arrow_forward</span>
                    </a>

                    <a href="{{ route('admin.vehicles.create') }}" class="d-flex align-items-center gap-3 p-3 rounded-2xl border text-decoration-none transition-all hover:shadow-md bg-primary-200" style="border-color: rgba(61, 105, 206, 0.12) !important;">
                        <div class="rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, var(--bs-primary-200), var(--bs-primary-red-200));">
                            <span class="material-symbols-outlined text-primary">directions_car</span>
                        </div>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <p class="small fw-bold text-dark mb-0">Yeni Araç</p>
                            <p class="small text-secondary mb-0">Araç ekle</p>
                        </div>
                        <span class="material-symbols-outlined text-secondary flex-shrink-0">arrow_forward</span>
                    </a>

                    <a href="{{ route('admin.employees.create') }}" class="d-flex align-items-center gap-3 p-3 rounded-2xl border text-decoration-none transition-all hover:shadow-md bg-primary-200" style="border-color: rgba(61, 105, 206, 0.12) !important;">
                        <div class="rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, var(--bs-primary-200), var(--bs-primary-red-200));">
                            <span class="material-symbols-outlined text-primary-red-200">person_add</span>
                        </div>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <p class="small fw-bold text-dark mb-0">Yeni Personel</p>
                            <p class="small text-secondary mb-0">Personel ekle</p>
                        </div>
                        <span class="material-symbols-outlined text-secondary flex-shrink-0">arrow_forward</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="d-flex align-items-center gap-3 p-3 rounded-2xl border text-decoration-none transition-all hover:shadow-md bg-primary-200" style="border-color: rgba(61, 105, 206, 0.12) !important;">
                        <div class="rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, var(--bs-primary-200), var(--bs-primary-red-200));">
                            <span class="material-symbols-outlined text-primary">list</span>
                        </div>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <p class="small fw-bold text-dark mb-0">Sipariş Listesi</p>
                            <p class="small text-secondary mb-0">Tüm siparişler</p>
                        </div>
                        <span class="material-symbols-outlined text-secondary flex-shrink-0">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .transition-all {
        transition: all 0.3s ease;
    }
    
    .hover\:shadow-md:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    }
    
    .hover\:shadow-sm:hover {
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }
</style>
@endpush
@endsection
