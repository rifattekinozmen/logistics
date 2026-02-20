<!DOCTYPE html>
<html class="light" lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Müşteri Portalı - Logistics')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet"></noscript>
    <noscript><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"></noscript>
    
    <!-- Custom Styles -->
    <style>
        :root {
            --bs-primary: #3D69CE;
            --bs-primary-200: #DCE8FC;
            --bs-primary-red: #C41E5A;
            --bs-primary-red-200: #FAD7E4;
            --bs-primary-red-200-text: #C45A7A;
            --bs-soft-bg: #F0F4FA;
            --bs-gray-blue: #6B7A99;
        }
        body { font-family: "Source Sans Pro", sans-serif; background-color: var(--bs-soft-bg); }
        .bg-primary { background-color: var(--bs-primary) !important; }
        .bg-primary-200 { background-color: var(--bs-primary-200) !important; }
        .text-primary-red { color: var(--bs-primary-red) !important; }
        .rounded-3xl { border-radius: 1.5rem; }
        .rounded-2xl { border-radius: 1rem; }
        .gap-2 { gap: 0.5rem; }
        .gap-3 { gap: 0.75rem; }
        .transition-all { transition: all 0.3s ease; }
        .sidebar-content { margin-left: 280px; min-width: 0; width: 100%; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--bs-gray-blue); border-radius: 10px; }
        
        .sidebar-nav-active { background-color: var(--bs-primary) !important; color: #ffffff !important; }
        .sidebar-nav-inactive { color: #64748b !important; }
        .sidebar-nav-inactive:hover { background-color: var(--bs-primary-200) !important; color: var(--bs-primary) !important; }
        @media (max-width: 991.98px) {
            .sidebar-content { margin-left: 0; }
            aside { transform: translateX(-100%); transition: transform 0.3s ease; }
            aside.show { transform: translateX(0); }
        }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-vh-100">
    <div class="d-flex">
        <!-- Sidebar -->
        @include('layouts.customer-sidebar')

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column sidebar-content">
            <!-- Navbar -->
            @include('layouts.customer-navbar')

            <!-- Page Content -->
            <main class="flex-grow-1 p-4 p-lg-5">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3xl shadow-sm border-0 mb-4" role="alert">
                        <span class="material-symbols-outlined align-middle me-2">check_circle</span>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show rounded-3xl shadow-sm border-0 mb-4" role="alert">
                        <span class="material-symbols-outlined align-middle me-2">error</span>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
