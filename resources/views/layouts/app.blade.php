<!DOCTYPE html>
<html class="light" lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Logistics')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Material Symbols: display=block ile ikon adları (shopping_cart vb.) gizlenir; font yüklenene kadar boş alan --}}
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    {{-- Source Sans Pro: non-blocking yükleme --}}
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet"></noscript>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Critical CSS - ilk paint öncesi layout sabitliği -->
    <style>
        html, body { min-height: 100vh; }
        /* Layout shell - app.css gelmeden önce sayfa yapısı sabit */
        .d-flex { display: flex !important; }
        .flex-grow-1 { flex: 1 1 0% !important; }
        .flex-column { flex-direction: column !important; }
        .min-vh-100 { min-height: 100vh !important; }
        .position-fixed { position: fixed !important; }
        .top-0 { top: 0 !important; }
        .start-0 { left: 0 !important; }
        .h-100 { height: 100% !important; }
        .overflow-y-auto { overflow-y: auto !important; }
        .overflow-hidden { overflow: hidden !important; }
        .list-unstyled { list-style: none !important; padding-left: 0 !important; margin-bottom: 0 !important; }
        .material-symbols-outlined { min-width: 1em; min-height: 1em; flex-shrink: 0; }
        /* Sidebar ikonları (18px) */
        .material-symbols-outlined[style*="18px"] { min-width: 18px; min-height: 18px; }
        /* Kart ikonları için (dashboard vb.) */
        .material-symbols-outlined[style*="1.75rem"] { min-width: 1.75rem; min-height: 1.75rem; }
        /* 1.25rem ikonlar (butonlar vb.) */
        .material-symbols-outlined[style*="1.25rem"] { min-width: 1.25rem; min-height: 1.25rem; }
        :root {
            --bs-primary: #3D69CE;
            --bs-primary-200: #DCE8FC;
            --bs-primary-red: #C41E5A;
            --bs-primary-red-200: #FAD7E4;
            --bs-primary-red-200-text: #C45A7A;
            --bs-accent-cyan: #3775A8;
            --bs-mernis: #2D8B6F;
            --bs-dark-slate: #274A9B;
            --bs-soft-bg: #F0F4FA;
            --bs-gray-blue: #6B7A99;
            --bs-black-blue: #0F1A2E;
            --bs-success-200: #E0EDE8;
            --bs-danger-200: #FCE8F0;
            --bs-warning-200: #F5F2E8;
            --bs-info-200: #E5EEFA;
            --bs-secondary-200: #EEF1F6;
            --bs-info: #3775A8;
            --bs-danger: #C41E5A;
            --bs-success: #2D8B6F;
            --bs-secondary: #6B7A99;
        }
        
        body {
            font-family: "Source Sans Pro", sans-serif;
            background-color: var(--bs-soft-bg);
        }
        
        .bg-primary { background-color: var(--bs-primary) !important; }
        .text-primary { color: var(--bs-primary) !important; }
        .bg-primary-200 { background-color: var(--bs-primary-200) !important; }
        .text-primary-200 { color: var(--bs-primary-200) !important; }
        .border-primary-200 { border-color: var(--bs-primary-200) !important; }
        
        .bg-primary-red { background-color: var(--bs-primary-red) !important; }
        .text-primary-red { color: var(--bs-primary-red) !important; }
        .bg-primary-red-200 { background-color: var(--bs-primary-red-200) !important; }
        .text-primary-red-200-text { color: var(--bs-primary-red-200-text) !important; }
        
        .bg-success-200 { background-color: var(--bs-success-200) !important; }
        .bg-danger-200 { background-color: var(--bs-danger-200) !important; }
        .bg-warning-200 { background-color: var(--bs-warning-200) !important; }
        .bg-info-200 { background-color: var(--bs-info-200) !important; }
        .bg-secondary-200 { background-color: var(--bs-secondary-200) !important; }
        
        .btn-primary {
            background-color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
            color: white !important;
        }
        .btn-primary:hover {
            background-color: #335bb5 !important;
            border-color: #335bb5 !important;
        }
        
        .btn-orders, .btn-dashboard, .btn-customers, .btn-shipments, 
        .btn-warehouses, .btn-vehicles, .btn-work-orders, .btn-employees, 
        .btn-shifts, .btn-payments, .btn-documents {
            background-color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
            color: white !important;
        }
        
        .rounded-3xl { border-radius: 1.5rem; }
        .rounded-2xl { border-radius: 1rem; }
        
        .gap-2 { gap: 0.5rem; }
        .gap-3 { gap: 0.75rem; }
        
        .transition-all { transition: all 0.3s ease; }
        
        .sidebar-content { margin-left: 280px; min-width: 0; width: 100%; }
        
        aside.sidebar-light { background-color: #ffffff !important; border-color: rgba(0, 0, 0, 0.08) !important; }
        aside.sidebar-light .sidebar-logo-wrap { border-color: rgba(0, 0, 0, 0.08) !important; }
        aside.sidebar-light .sidebar-group-heading { color: var(--bs-primary-red-200-text) !important; }
        aside.sidebar-light nav a.sidebar-link { color: #334155 !important; }
        aside.sidebar-light nav a.sidebar-link:not(.bg-primary):hover {
            background-color: var(--bs-primary-200) !important;
            color: var(--bs-primary) !important;
        }
        aside.sidebar-light nav a.bg-primary {
            background-color: var(--bs-primary) !important;
            color: #ffffff !important;
        }
        
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--bs-gray-blue); border-radius: 10px; }
        
        .navbar-btn-hover:hover { background-color: #ffffff !important; }
        .navbar-user-btn:hover { opacity: 0.8; }
        .dropdown-item-hover:hover { background-color: var(--bs-primary-200) !important; }
        .dropdown-item-hover-success:hover { background-color: var(--bs-success-200) !important; }
        .dropdown-item-hover-danger:hover { background-color: var(--bs-danger-200) !important; }
        
        .btn-logout-sidebar:hover {
            background-color: var(--bs-primary-red) !important;
            color: white !important;
            border-color: var(--bs-primary-red) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(196, 30, 90, 0.3);
        }
        
        .hover\:shadow-md:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        }
        
        .hover\:shadow-sm:hover {
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        
        .quick-access-link:hover { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important; }
        .quick-access-primary:hover { background-color: var(--bs-primary) !important; color: #ffffff !important; }
        .quick-access-primary:hover .text-dark,
        .quick-access-primary:hover .text-secondary { color: #ffffff !important; }
        .quick-access-info:hover { background-color: var(--bs-info) !important; color: #ffffff !important; }
        .quick-access-info:hover .text-dark,
        .quick-access-info:hover .text-secondary { color: #ffffff !important; }
        .quick-access-success:hover { background-color: var(--bs-success) !important; color: #ffffff !important; }
        .quick-access-success:hover .text-dark,
        .quick-access-success:hover .text-secondary { color: #ffffff !important; }
        
        .filter-area {
            background-color: var(--bs-primary-200) !important;
            border: 1px solid var(--bs-primary-200) !important;
        }
        .filter-area .form-control,
        .filter-area .form-select {
            background-color: #fff !important;
            border-color: var(--bs-primary-200) !important;
        }
        
        @media (max-width: 991.98px) {
            .sidebar-content { margin-left: 0; }
            aside { transform: translateX(-100%); transition: transform 0.3s ease; }
            aside.show { transform: translateX(0); }
        }
    </style>

    @yield('styles')
    @stack('styles')
</head>
<body class="min-vh-100">
    @if($showCompanySelectModal ?? false)
        <style>
            body {
                background-color: var(--bs-soft-bg);
            }
            aside,
            .sidebar-content {
                display: none !important;
            }
        </style>
    @endif
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>
    
    <div class="d-flex">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column sidebar-content">
            <!-- Navbar -->
            @include('layouts.navbar')

            <!-- Page Content -->
            <main class="flex-grow-1 p-4 p-lg-5">
                @yield('content')
            </main>
        </div>
    </div>

    @include('components.delete-modal')
    @include('components.toast')

    @if(($showCompanySelectModal ?? false) && auth()->check())
        @php
            $companiesForModal = $userCompaniesForLayout ?? collect();
        @endphp

        @if($companiesForModal->isNotEmpty())
            <x-modal id="companySelectModal" title="Firma Seçimi" size="xl" :centered="true" :close-button="false">
                <form id="companySelectForm" action="{{ route('admin.companies.switch') }}" method="POST">
                    @csrf

                    <p class="text-secondary mb-3">
                        Lütfen işlem yapmak istediğiniz firmayı seçin.
                    </p>

                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">VKN/TCKN</label>
                            <input type="text" id="filterTaxNumber" class="form-control form-control-sm" placeholder="VKN/TCKN">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Firma Kodu</label>
                            <input type="text" id="filterCode" class="form-control form-control-sm" placeholder="Firma Kodu">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Firma Adı</label>
                            <input type="text" id="filterName" class="form-control form-control-sm" placeholder="Firma Adı">
                        </div>
                        <div class="col-md-3 d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-3xl px-3" id="companyFilterClearButton">
                                Temizle
                            </button>
                            <button type="button" class="btn btn-primary btn-sm rounded-3xl px-3" id="companyFilterButton">
                                Filtrele
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive border rounded-3xl" style="border-color: var(--bs-primary-200) !important;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th class="small text-secondary">VKN/TCKN</th>
                                    <th class="small text-secondary">Firma Kodu</th>
                                    <th class="small text-secondary">Firma Adı</th>
                                </tr>
                            </thead>
                            <tbody id="companySelectTableBody">
                                @foreach($companiesForModal as $company)
                                    <tr
                                        data-tax="{{ Str::lower($company->tax_number ?? '') }}"
                                        data-code="{{ Str::lower((string) $company->id) }}"
                                        data-name="{{ Str::lower($company->name ?? '') }}"
                                    >
                                        <td class="text-center">
                                            <input type="radio" name="company_id" value="{{ $company->id }}" class="form-check-input company-radio">
                                        </td>
                                        <td class="small">{{ $company->tax_number ?? '-' }}</td>
                                        <td class="small">{{ $company->id }}</td>
                                        <td class="small">{{ $company->name }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <p class="small text-secondary mb-0">
                            Toplam Kayıt: {{ $companiesForModal->count() }}
                        </p>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary rounded-3xl px-4" data-bs-dismiss="modal">
                                Vazgeç
                            </button>
                            <button type="submit" class="btn btn-primary rounded-3xl px-4" id="companySelectSubmit" disabled>
                                Devam
                            </button>
                        </div>
                    </div>
                </form>
            </x-modal>

            @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var modalEl = document.getElementById('companySelectModal');
                        if (modalEl && window.bootstrap) {
                            var modal = new bootstrap.Modal(modalEl, {
                                backdrop: 'static',
                                keyboard: false
                            });
                            modal.show();
                        }

                        var submitBtn = document.getElementById('companySelectSubmit');
                        var radios = modalEl.querySelectorAll('.company-radio');
                        radios.forEach(function (radio) {
                            radio.addEventListener('change', function () {
                                if (submitBtn) {
                                    submitBtn.disabled = !modalEl.querySelector('.company-radio:checked');
                                }
                            });
                        });

                        var filterTax = document.getElementById('filterTaxNumber');
                        var filterCode = document.getElementById('filterCode');
                        var filterName = document.getElementById('filterName');
                        var filterButton = document.getElementById('companyFilterButton');
                        var clearButton = document.getElementById('companyFilterClearButton');
                        var rows = Array.from(document.querySelectorAll('#companySelectTableBody tr'));

                        function applyFilter() {
                            var taxVal = (filterTax.value || '').toLowerCase();
                            var codeVal = (filterCode.value || '').toLowerCase();
                            var nameVal = (filterName.value || '').toLowerCase();

                            rows.forEach(function (row) {
                                var rowTax = row.dataset.tax || '';
                                var rowCode = row.dataset.code || '';
                                var rowName = row.dataset.name || '';

                                var matches =
                                    (!taxVal || rowTax.indexOf(taxVal) !== -1) &&
                                    (!codeVal || rowCode.indexOf(codeVal) !== -1) &&
                                    (!nameVal || rowName.indexOf(nameVal) !== -1);

                                row.style.display = matches ? '' : 'none';
                            });
                        }

                        if (filterButton) {
                            filterButton.addEventListener('click', function (e) {
                                e.preventDefault();
                                applyFilter();
                            });
                        }

                        if (clearButton) {
                            clearButton.addEventListener('click', function (e) {
                                e.preventDefault();
                                if (filterTax) filterTax.value = '';
                                if (filterCode) filterCode.value = '';
                                if (filterName) filterName.value = '';
                                rows.forEach(function (row) {
                                    row.style.display = '';
                                });
                            });
                        }
                    });
                </script>
            @endpush
        @endif
    @endif

    @stack('scripts')
    <script>
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('aside');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        function toggleSidebar() {
            sidebar?.classList.toggle('show');
            sidebarOverlay?.classList.toggle('show');
        }
        
        sidebarToggle?.addEventListener('click', toggleSidebar);
        sidebarOverlay?.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>
