@extends('layouts.app')

@section('title', 'Yeni Personel - Logistics')

@section('styles')
@include('admin.personnel._personnel_styles')
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Personel Ekle</h2>
        <p class="text-secondary mb-0">Yeni bir personel kaydı oluşturun</p>
    </div>
    <a href="{{ route('admin.personnel.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
    <form action="{{ route('admin.personnel.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        @include('admin.personnel._header', ['personnel' => null, 'editable' => true])
        @include('admin.personnel._form')

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-primary-200);">
            <a href="{{ route('admin.personnel.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Personel Ekle</button>
        </div>
    </form>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var photoInput = document.getElementById('personnel-photo-input-header');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(ev) {
                    var placeholder = document.getElementById('personnel-header-avatar-placeholder');
                    var img = document.getElementById('personnel-header-avatar-img');
                    if (img) {
                        img.src = ev.target.result;
                    } else if (placeholder) {
                        placeholder.outerHTML = '<img src="' + ev.target.result + '" alt="" class="rounded-circle object-fit-cover personnel-avatar-preview" style="width: 80px; height: 80px;" id="personnel-header-avatar-img">';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
@endpush
@endsection
