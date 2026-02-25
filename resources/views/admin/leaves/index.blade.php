@extends('layouts.app')

@section('title', 'İzinler - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">İzin Talepleri</h2>
        <p class="text-secondary mb-0">Tüm izin taleplerini görüntüleyin ve yönetin</p>
    </div>
    <a href="{{ route('admin.leaves.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni İzin Talebi
    </a>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="event_available" color="primary" col="col-md-4" />
    <x-index-stat-card title="Onay Bekleyen" :value="$stats['pending'] ?? 0" icon="schedule" color="warning" col="col-md-4" />
    <x-index-stat-card title="Onaylandı" :value="$stats['approved'] ?? 0" icon="check_circle" color="success" col="col-md-4" />
</div>

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.leaves.index') }}" class="row g-3 align-items-end">
        <div class="col-md-10">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Beklemede</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Onaylandı</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Reddedildi</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="px-4 pt-3 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <select id="leaves-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
                <option value="approve">Onayla</option>
                <option value="reject">Reddet</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="leaves-bulk-apply">Uygula</button>
        </div>
        <div class="small text-secondary"><span id="leaves-selected-count">0</span> kayıt seçili</div>
    </div>
    <div class="table-responsive">
        @php $currentSort = request('sort'); $currentDirection = request('direction', 'asc'); @endphp
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 text-center align-middle" style="width: 40px;"><input type="checkbox" id="select-all-leaves"></th>
                    <th class="border-0 small text-secondary fw-semibold">Personel</th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $d = $currentSort === 'leave_type' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.leaves.index', array_merge(request()->query(), ['sort' => 'leave_type', 'direction' => $d])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">İzin Türü @if($currentSort === 'leave_type')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $d = $currentSort === 'start_date' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.leaves.index', array_merge(request()->query(), ['sort' => 'start_date', 'direction' => $d])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">Başlangıç @if($currentSort === 'start_date')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $d = $currentSort === 'end_date' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.leaves.index', array_merge(request()->query(), ['sort' => 'end_date', 'direction' => $d])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">Bitiş @if($currentSort === 'end_date')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $d = $currentSort === 'total_days' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.leaves.index', array_merge(request()->query(), ['sort' => 'total_days', 'direction' => $d])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">Gün @if($currentSort === 'total_days')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $d = $currentSort === 'status' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.leaves.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => $d])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">Durum @if($currentSort === 'status')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                    <tr>
                        <td class="align-middle text-center"><input type="checkbox" class="leaves-row-checkbox" value="{{ $leave->id }}"></td>
                        <td class="align-middle">
                            <span class="fw-semibold text-dark">{{ $leave->employee->first_name }} {{ $leave->employee->last_name }}</span>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ ucfirst($leave->leave_type) }}</small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $leave->start_date->format('d.m.Y') }}</small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $leave->end_date->format('d.m.Y') }}</small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $leave->total_days }} gün</small>
                        </td>
                        <td class="align-middle">
                            <span class="badge bg-{{ match($leave->status) { 'approved' => 'success', 'rejected' => 'danger', default => 'warning' } }}-200 text-{{ match($leave->status) { 'approved' => 'success', 'rejected' => 'danger', default => 'warning' } }} rounded-pill px-3 py-2">
                                {{ match($leave->status) { 'approved' => 'Onaylandı', 'rejected' => 'Reddedildi', default => 'Beklemede' } }}
                            </span>
                        </td>
                        <td class="align-middle text-end">
                            @if($leave->status === 'pending')
                                <form action="{{ route('admin.leaves.approve', $leave) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-sm bg-success-200 text-success border-0">Onayla</button>
                                </form>
                                <form action="{{ route('admin.leaves.approve', $leave) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-sm bg-danger-200 text-danger border-0">Reddet</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <p class="text-secondary mb-0">Henüz izin talebi bulunmuyor.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leaves->hasPages())
        <div class="p-4 border-top">
            {{ $leaves->links() }}
        </div>
    @endif
</div>
<form id="leaves-bulk-form" method="POST" action="{{ route('admin.leaves.bulk') }}" class="d-none">
    @csrf
    <input type="hidden" name="action" id="leaves-bulk-action-input">
</form>
@endsection
@push('scripts')
<script>
(function(){
var m=document.getElementById('select-all-leaves'),r=document.querySelectorAll('.leaves-row-checkbox'),c=document.getElementById('leaves-selected-count'),b=document.getElementById('leaves-bulk-apply'),s=document.getElementById('leaves-bulk-action'),f=document.getElementById('leaves-bulk-form'),i=document.getElementById('leaves-bulk-action-input');
function up(){var n=Array.from(r).filter(function(x){return x.checked;});if(c)c.textContent=n.length;if(m){m.checked=n.length&&n.length===r.length;m.indeterminate=n.length>0&&n.length<r.length;}}
if(m)m.addEventListener('change',function(){r.forEach(function(x){x.checked=m.checked;});up();});
r.forEach(function(x){x.addEventListener('change',up);});
if(b)b.addEventListener('click',function(){
var a=s.value,sel=Array.from(r).filter(function(x){return x.checked;});
if(!a){alert('Lütfen bir toplu işlem seçin.');return;}
if(sel.length===0){alert('Lütfen en az bir kayıt seçin.');return;}
if(a==='delete'&&!confirm('Seçili izin taleplerini silmek istediğinize emin misiniz?'))return;
f.querySelectorAll('input[name="selected[]"]').forEach(function(x){x.remove();});
sel.forEach(function(x){var h=document.createElement('input');h.type='hidden';h.name='selected[]';h.value=x.value;f.appendChild(h);});
i.value=a;f.submit();
});
})();
</script>
@endpush
