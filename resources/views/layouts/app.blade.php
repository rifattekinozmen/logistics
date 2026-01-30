<!DOCTYPE html>
<html class="light" lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Logistics')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,200;0,300;0,400;0,600;0,700;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @stack('styles')
</head>
<body class="min-vh-100">
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>
    
    <div class="d-flex">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <div class="grow d-flex flex-column sidebar-content">
            <!-- Navbar -->
            @include('layouts.navbar')

            <!-- Page Content -->
            <main class="grow p-4 p-lg-5">
                @yield('content')
            </main>
        </div>
    </div>

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
