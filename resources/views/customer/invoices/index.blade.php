@extends('layouts.customer-app')

@section('title', 'Faturalarım - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">receipt_long</span>
            <h2 class="h3 fw-bold text-dark mb-0">Faturalarım</h2>
        </div>
        <p class="text-secondary mb-0">Siparişlerinize ait faturaları görüntüleyin ve indirin</p>
    </div>
</div>

<!-- Filtreleme -->
<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('customer.invoices.index') }}" class="row g-3 align-items-end">
        <div class="col-md-10">
            <label class="form-label small fw-semibold text-dark">Sipariş No</label>
            <input type="text" name="order_id" value="{{ request('order_id') }}" placeholder="Sipariş ID girin" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<!-- Fatura Listesi -->
<div class="bg-white rounded-3xl shadow-sm border overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 small text-secondary fw-semibold">Fatura Adı</th>
                    <th class="border-0 small text-secondary fw-semibold">Sipariş No</th>
                    <th class="border-0 small text-secondary fw-semibold">Tarih</th>
                    <th class="border-0 small text-secondary fw-semibold">Dosya Boyutu</th>
                    <th class="border-0 small text-secondary fw-semibold text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    @php
                        $order = \App\Models\Order::find($invoice->documentable_id);
                    @endphp
                    <tr>
                        <td class="align-middle">
                            <div class="d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined text-primary" style="font-size: 1.25rem;">receipt_long</span>
                                <span class="fw-semibold text-dark">{{ $invoice->name }}</span>
                            </div>
                        </td>
                        <td class="align-middle">
                            @if($order)
                                <a href="{{ route('customer.orders.show', $order) }}" class="text-primary text-decoration-none">
                                    {{ $order->order_number }}
                                </a>
                            @else
                                <span class="text-secondary">-</span>
                            @endif
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $invoice->created_at->format('d.m.Y H:i') }}</small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">
                                @if($invoice->file_size)
                                    {{ number_format($invoice->file_size / 1024, 2) }} KB
                                @else
                                    -
                                @endif
                            </small>
                        </td>
                        <td class="align-middle text-end">
                            @if(Auth::user() && Auth::user()->hasPermission('customer.portal.invoices.download'))
                                <a href="{{ route('customer.invoices.download', $invoice) }}" class="btn btn-sm bg-primary-200 text-primary border-0 d-flex align-items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">download</span>
                                    İndir
                                </a>
                            @else
                                <span class="text-secondary small">Yetki yok</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <span class="material-symbols-outlined text-secondary mb-2 d-block" style="font-size: 3rem; opacity: 0.3;">receipt_long</span>
                            <p class="text-secondary mb-0">Henüz fatura bulunmuyor.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
        <div class="p-4 border-top">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
