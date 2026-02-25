@extends('layouts.app')

@section('title', 'Vardiyalar - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Vardiyalar</h2>
        <p class="text-secondary mb-0">Tüm vardiyaları görüntüleyin ve yönetin</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.shifts.planning') }}" class="btn btn-shifts d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">calendar_month</span>
            Planlama
        </a>
        <a href="{{ route('admin.shifts.templates') }}" class="btn btn-shifts d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">schedule</span>
            Şablonlar
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="schedule" color="primary" col="col-md-12" />
</div>

<div class="filter-area filter-area-shifts rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.shifts.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Personel</label>
            <select name="employee_id" class="form-select">
                <option value="">Tümü</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->first_name }} {{ $employee->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Tarih Başlangıç</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Tarih Bitiş</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-shifts w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="px-4 pt-3 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <select id="shifts-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="shifts-bulk-apply">Uygula</button>
        </div>
        <div class="small text-secondary"><span id="shifts-selected-count">0</span> kayıt seçili</div>
    </div>
    <div class="table-responsive">
        @php $currentSort = request('sort'); $currentDirection = request('direction', 'asc'); @endphp
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 text-center align-middle" style="width: 40px;"><input type="checkbox" id="select-all-shifts"></th>
                    <th class="border-0 fw-semibold text-secondary small">Personel</th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $d = $currentSort === 'shift_date' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.shifts.index', array_merge(request()->query(), ['sort' => 'shift_date', 'direction' => $d])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">Vardiya Tarihi @if($currentSort === 'shift_date')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $d = $currentSort === 'template' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.shifts.index', array_merge(request()->query(), ['sort' => 'template', 'direction' => $d])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">Şablon @if($currentSort === 'template')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $d = $currentSort === 'start_time' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.shifts.index', array_merge(request()->query(), ['sort' => 'start_time', 'direction' => $d])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">Başlangıç @if($currentSort === 'start_time')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">Bitiş</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shifts as $shift)
                <tr>
                    <td class="align-middle text-center"><input type="checkbox" class="shifts-row-checkbox" value="{{ $shift->id }}"></td>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">{{ $shift->employee->first_name ?? '-' }} {{ $shift->employee->last_name ?? '' }}</span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">
                            {{ $shift->shift_date ? $shift->shift_date->format('d.m.Y') : '-' }}
                        </small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $shift->schedule?->template?->name ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $shift->start_time ? $shift->start_time->format('H:i') : '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $shift->end_time ? $shift->end_time->format('H:i') : '-' }}</small>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">schedule</span>
                            <p class="text-secondary mb-0">Henüz vardiya bulunmuyor.</p>
                            <a href="{{ route('admin.shifts.planning') }}" class="btn btn-shifts btn-sm mt-2">Vardiya Planla</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($shifts->hasPages())
    <div class="p-4 border-top">
        {{ $shifts->links() }}
    </div>
    @endif
</div>
<form id="shifts-bulk-form" method="POST" action="{{ route('admin.shifts.bulk') }}" class="d-none">
    @csrf
    <input type="hidden" name="action" id="shifts-bulk-action-input">
</form>
@endsection
@push('scripts')
<script>
(function(){
var m=document.getElementById('select-all-shifts'),r=document.querySelectorAll('.shifts-row-checkbox'),c=document.getElementById('shifts-selected-count'),b=document.getElementById('shifts-bulk-apply'),s=document.getElementById('shifts-bulk-action'),f=document.getElementById('shifts-bulk-form'),i=document.getElementById('shifts-bulk-action-input');
function up(){var n=Array.from(r).filter(function(x){return x.checked;});if(c)c.textContent=n.length;if(m){m.checked=n.length&&n.length===r.length;m.indeterminate=n.length>0&&n.length<r.length;}}
if(m)m.addEventListener('change',function(){r.forEach(function(x){x.checked=m.checked;});up();});
r.forEach(function(x){x.addEventListener('change',up);});
if(b)b.addEventListener('click',function(){
var a=s.value,sel=Array.from(r).filter(function(x){return x.checked;});
if(!a){alert('Lütfen bir toplu işlem seçin.');return;}
if(sel.length===0){alert('Lütfen en az bir kayıt seçin.');return;}
if(a==='delete'&&!confirm('Seçili vardiyaları silmek istediğinize emin misiniz?'))return;
f.querySelectorAll('input[name="selected[]"]').forEach(function(x){x.remove();});
sel.forEach(function(x){var h=document.createElement('input');h.type='hidden';h.name='selected[]';h.value=x.value;f.appendChild(h);});
i.value=a;f.submit();
});
})();
</script>
@endpush
