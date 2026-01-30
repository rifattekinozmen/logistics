<!DOCTYPE html>
<html class="light" lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Müşteri Portalı - Logistics')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,200;0,300;0,400;0,600;0,700;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @stack('styles')
</head>
<body class="min-vh-100">
    <div class="d-flex">
        <!-- Sidebar -->
        @include('layouts.customer-sidebar')

        <!-- Main Content -->
        <div class="grow d-flex flex-column sidebar-content">
            <!-- Navbar -->
            @include('layouts.customer-navbar')

            <!-- Page Content -->
            <main class="grow p-4 p-lg-5">
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

    @stack('scripts')
</body>
</html>
