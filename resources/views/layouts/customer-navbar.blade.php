<nav class="bg-white border-bottom shadow-sm p-3 p-lg-4">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">store</span>
                <div>
                    <h4 class="h5 fw-bold text-dark mb-0">Müşteri Portalı</h4>
                    <p class="small text-secondary mb-0" style="font-size: 0.75rem;">Siparişlerinizi takip edin</p>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @php
                $user = Auth::user();
                $hasAdminAccess = $user && method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('company_admin'));
                $unreadNotificationsCount = $user?->unreadNotificationsCount() ?? 0;
            @endphp
            
            @if($user && method_exists($user, 'hasPermission') && $user->hasPermission('customer.portal.notifications.view'))
                <a href="{{ route('customer.notifications.index') }}" class="btn btn-sm btn-outline-secondary position-relative d-flex align-items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size: 1.25rem;">notifications</span>
                    @if($unreadNotificationsCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>
            @endif
            
            @if($hasAdminAccess)
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size: 1rem;">admin_panel_settings</span>
                    Admin Paneli
                </a>
            @endif
        </div>
    </div>
</nav>
