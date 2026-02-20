@extends('layouts.customer-app')

@section('title', 'Müşteri Dashboard - Logistics')

@section('content')
<div class="mb-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary" style="font-size: 2rem;">store</span>
                <h1 class="h2 fw-bold text-dark mb-0">Hoş Geldiniz, {{ $customer->name }}!</h1>
            </div>
            <p class="text-secondary mb-0">Müşteri portalına hoş geldiniz. Siparişlerinizi takip edebilir ve yeni sipariş oluşturabilirsiniz.</p>
        </div>
        @if(Auth::user() && Auth::user()->hasPermission('customer.portal.orders.create'))
            <a href="{{ route('customer.orders.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
                Yeni Sipariş
            </a>
        @endif
    </div>

    <!-- İstatistik Kartları -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="bg-primary rounded-2xl d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">shopping_cart</span>
                    </div>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ $stats['total_orders'] }}</h3>
                <p class="small fw-semibold text-secondary mb-0">Toplam Sipariş</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="bg-warning rounded-2xl d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">schedule</span>
                    </div>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ $stats['active_orders'] }}</h3>
                <p class="small fw-semibold text-secondary mb-0">Aktif Sipariş</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white rounded-3xl shadow-sm border p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="bg-success rounded-2xl d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">check_circle</span>
                    </div>
                </div>
                <h3 class="h1 fw-bold text-dark mb-1">{{ $stats['delivered_orders'] }}</h3>
                <p class="small fw-semibold text-secondary mb-0">Teslim Edilen</p>
            </div>
        </div>
        <div class="col-md-3">
            <a href="{{ route('customer.payments.index') }}" class="text-decoration-none">
                <div class="bg-white rounded-3xl shadow-sm border p-4 {{ $stats['overdue_payments_count'] > 0 ? 'border-danger' : '' }}">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-{{ $stats['overdue_payments_count'] > 0 ? 'danger' : 'primary' }} rounded-2xl d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                            <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">payments</span>
                        </div>
                    </div>
                    <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($stats['pending_payments'], 0) }} ₺</h3>
                    <p class="small fw-semibold text-secondary mb-0">
                        Bekleyen Ödeme
                        @if($stats['overdue_payments_count'] > 0)
                            <span class="badge bg-danger-200 text-danger rounded-pill px-2 py-1 ms-1">{{ $stats['overdue_payments_count'] }} Gecikmiş</span>
                        @endif
                    </p>
                </div>
            </a>
        </div>
    </div>

    <!-- Aktif Siparişler -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="bg-white rounded-3xl shadow-sm border p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="h5 fw-bold text-dark mb-0">Aktif Siparişler</h3>
                    <a href="{{ route('customer.orders.index') }}" class="btn btn-link btn-sm">Tümünü Gör</a>
                </div>
                @forelse($activeOrders as $order)
                    <a href="{{ route('customer.orders.show', $order) }}" class="text-decoration-none">
                        <div class="p-3 rounded-2xl border mb-2 transition-all hover:shadow-sm" style="cursor: pointer;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="fw-bold text-dark mb-1">{{ $order->order_number }}</p>
                                    <p class="small text-secondary mb-0">{{ Str::limit($order->delivery_address, 40) }}</p>
                                </div>
                                <span class="badge bg-{{ match($order->status) { 'delivered' => 'success', 'in_transit' => 'primary', 'assigned' => 'info', default => 'warning' } }}-200 text-{{ match($order->status) { 'delivered' => 'success', 'in_transit' => 'primary', 'assigned' => 'info', default => 'warning' } }} rounded-pill px-3 py-2">
                                    {{ $order->status_label }}
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-4">
                        <span class="material-symbols-outlined text-secondary mb-2 d-block" style="font-size: 3rem; opacity: 0.3;">inbox</span>
                        <p class="text-secondary mb-0">Aktif sipariş bulunmuyor.</p>
                        @if(Auth::user() && Auth::user()->hasPermission('customer.portal.orders.create'))
                            <a href="{{ route('customer.orders.create') }}" class="btn btn-primary btn-sm mt-3 d-inline-flex align-items-center gap-1">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">add</span>
                                Yeni Sipariş Oluştur
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Son Teslim Edilenler -->
        <div class="col-lg-6">
            <div class="bg-white rounded-3xl shadow-sm border p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="h5 fw-bold text-dark mb-0">Son Teslim Edilenler</h3>
                    <a href="{{ route('customer.orders.index', ['status' => 'delivered']) }}" class="btn btn-link btn-sm">Tümünü Gör</a>
                </div>
                @forelse($recentDelivered as $order)
                    <a href="{{ route('customer.orders.show', $order) }}" class="text-decoration-none">
                        <div class="p-3 rounded-2xl border mb-2 transition-all hover:shadow-sm" style="cursor: pointer;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="fw-bold text-dark mb-1">{{ $order->order_number }}</p>
                                    <p class="small text-secondary mb-0">
                                        Teslim: {{ $order->delivered_at?->format('d.m.Y H:i') ?? '-' }}
                                    </p>
                                </div>
                                <span class="badge bg-success-200 text-success rounded-pill px-3 py-2">Teslim Edildi</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-4">
                        <span class="material-symbols-outlined text-secondary mb-2 d-block" style="font-size: 3rem; opacity: 0.3;">check_circle</span>
                        <p class="text-secondary mb-0">Henüz teslim edilen sipariş bulunmuyor.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Grafikler -->
    <div class="row g-4 mt-2">
        <div class="col-lg-8">
            <div class="bg-white rounded-3xl shadow-sm border p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">bar_chart</span>
                    <h3 class="h5 fw-bold text-dark mb-0">Aylık Sipariş Trendi</h3>
                </div>
                <canvas id="monthlyOrdersChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="bg-white rounded-3xl shadow-sm border p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">pie_chart</span>
                    <h3 class="h5 fw-bold text-dark mb-0">Durum Dağılımı</h3>
                </div>
                <canvas id="statusDistributionChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Aylık Sipariş Trendi Grafiği
const monthlyCtx = document.getElementById('monthlyOrdersChart');
if (monthlyCtx) {
    const monthlyData = @json($monthlyOrders);
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => item.label),
            datasets: [{
                label: 'Sipariş Sayısı',
                data: monthlyData.map(item => item.count),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Durum Dağılımı Grafiği
const statusCtx = document.getElementById('statusDistributionChart');
if (statusCtx) {
    const statusData = @json($statusDistribution);
    const labels = Object.keys(statusData).map(status => {
        const statusLabels = {
            'pending': 'Beklemede',
            'assigned': 'Atandı',
            'in_transit': 'Yolda',
            'delivered': 'Teslim Edildi',
            'cancelled': 'İptal'
        };
        return statusLabels[status] || status;
    });
    const colors = ['#F59E0B', '#3B82F6', '#10B981', '#EF4444', '#6B7280'];
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: colors.slice(0, Object.keys(statusData).length),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}
</script>
@endpush
@endsection
