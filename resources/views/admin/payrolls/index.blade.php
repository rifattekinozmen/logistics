@extends('layouts.app')

@section('title', 'Bordrolar - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Bordrolar</h2>
        <p class="text-secondary mb-0">Tüm bordroları görüntüleyin ve yönetin</p>
    </div>
    <a href="{{ route('admin.payrolls.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni Bordro
    </a>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="receipt_long" color="primary" col="col-md-6" />
    <x-index-stat-card title="Bu Ay" :value="$stats['this_month'] ?? 0" icon="calendar_month" color="info" col="col-md-6" />
</div>

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.payrolls.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Taslak</option>
                <option value="finalized" {{ request('status') === 'finalized' ? 'selected' : '' }}>Kesinleşti</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Ödendi</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Dönem Başlangıç</label>
            <input type="date" name="period_start" value="{{ request('period_start') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Dönem Bitiş</label>
            <input type="date" name="period_end" value="{{ request('period_end') }}" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="px-4 pt-3 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <select id="payrolls-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="payrolls-bulk-apply">Uygula</button>
        </div>
        <div class="small text-secondary"><span id="payrolls-selected-count">0</span> kayıt seçili</div>
    </div>
    <div class="table-responsive">
        @php $currentSort = request('sort'); $currentDirection = request('direction', 'asc'); @endphp
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 text-center align-middle" style="width: 40px;"><input type="checkbox" id="select-all-payrolls"></th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $d = $currentSort === 'payroll_number' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.payrolls.index', array_merge(request()->query(), ['sort' => 'payroll_number', 'direction' => $d])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">Bordro No @if($currentSort === 'payroll_number')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $d = $currentSort === 'employee_id' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.payrolls.index', array_merge(request()->query(), ['sort' => 'employee_id', 'direction' => $d])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">Personel @if($currentSort === 'employee_id')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $d = $currentSort === 'period_start' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.payrolls.index', array_merge(request()->query(), ['sort' => 'period_start', 'direction' => $d])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">Dönem @if($currentSort === 'period_start')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $d = $currentSort === 'net_salary' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.payrolls.index', array_merge(request()->query(), ['sort' => 'net_salary', 'direction' => $d])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">Net Maaş @if($currentSort === 'net_salary')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $d = $currentSort === 'status' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.payrolls.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => $d])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">Durum @if($currentSort === 'status')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $payroll)
                    <tr>
                        <td class="align-middle text-center"><input type="checkbox" class="payrolls-row-checkbox" value="{{ $payroll->id }}"></td>
                        <td class="align-middle">
                            <span class="fw-semibold text-dark">{{ $payroll->payroll_number }}</span>
                        </td>
                        <td class="align-middle">
                            <span class="fw-semibold text-dark">{{ $payroll->employee->first_name }} {{ $payroll->employee->last_name }}</span>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">
                                {{ $payroll->period_start->format('d.m.Y') }} - {{ $payroll->period_end->format('d.m.Y') }}
                            </small>
                        </td>
                        <td class="align-middle">
                            <span class="fw-bold text-dark">{{ number_format($payroll->net_salary, 2) }} ₺</span>
                        </td>
                        <td class="align-middle">
                            <span class="badge bg-{{ match($payroll->status) { 'paid' => 'success', 'finalized' => 'info', default => 'warning' } }}-200 text-{{ match($payroll->status) { 'paid' => 'success', 'finalized' => 'info', default => 'warning' } }} rounded-pill px-3 py-2">
                                {{ match($payroll->status) { 'paid' => 'Ödendi', 'finalized' => 'Kesinleşti', default => 'Taslak' } }}
                            </span>
                        </td>
                        <td class="align-middle text-end">
                            <a href="{{ route('admin.payrolls.show', $payroll) }}" class="btn btn-sm bg-primary-200 text-primary border-0">
                                Detay
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <p class="text-secondary mb-0">Henüz bordro bulunmuyor.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payrolls->hasPages())
        <div class="p-4 border-top">
            {{ $payrolls->links() }}
        </div>
    @endif
</div>
<form id="payrolls-bulk-form" method="POST" action="{{ route('admin.payrolls.bulk') }}" class="d-none">
    @csrf
    <input type="hidden" name="action" id="payrolls-bulk-action-input">
</form>
@endsection
@push('scripts')
<script>
(function(){
var m=document.getElementById('select-all-payrolls'),r=document.querySelectorAll('.payrolls-row-checkbox'),c=document.getElementById('payrolls-selected-count'),b=document.getElementById('payrolls-bulk-apply'),s=document.getElementById('payrolls-bulk-action'),f=document.getElementById('payrolls-bulk-form'),i=document.getElementById('payrolls-bulk-action-input');
function up(){var n=Array.from(r).filter(function(x){return x.checked;});if(c)c.textContent=n.length;if(m){m.checked=n.length&&n.length===r.length;m.indeterminate=n.length>0&&n.length<r.length;}}
if(m)m.addEventListener('change',function(){r.forEach(function(x){x.checked=m.checked;});up();});
r.forEach(function(x){x.addEventListener('change',up);});
if(b)b.addEventListener('click',function(){
var a=s.value,sel=Array.from(r).filter(function(x){return x.checked;});
if(!a){alert('Lütfen bir toplu işlem seçin.');return;}
if(sel.length===0){alert('Lütfen en az bir kayıt seçin.');return;}
if(a==='delete'&&!confirm('Seçili bordroları silmek istediğinize emin misiniz?'))return;
f.querySelectorAll('input[name="selected[]"]').forEach(function(x){x.remove();});
sel.forEach(function(x){var h=document.createElement('input');h.type='hidden';h.name='selected[]';h.value=x.value;f.appendChild(h);});
i.value=a;f.submit();
});
})();
</script>
@endpush
