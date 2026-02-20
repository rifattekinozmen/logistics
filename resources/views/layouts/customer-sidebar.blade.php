<aside class="position-fixed top-0 start-0 h-100 bg-white border-end shadow-sm" style="width: 280px; z-index: 1000;">
    <div class="d-flex flex-column h-100">
        <!-- Logo -->
        <div class="px-2 py-3 border-bottom">
            <div class="d-flex align-items-center justify-content-center w-100 flex-column">
                <div class="rounded-3xl border d-flex align-items-center justify-content-center bg-white text-secondary mb-2" style="width: 56px; height: 56px; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="font-size: 28px;">store</span>
                </div>
                <p class="small fw-bold text-dark mb-0" style="font-size: 11px; letter-spacing: 0.05em;">MÜŞTERİ PORTALI</p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-grow-1 p-2 overflow-y-auto custom-scrollbar">
            <ul class="list-unstyled mb-0">
                @php
                    $sidebarUser = Auth::user();
                @endphp

                <!-- Sipariş & Teslimat -->
                <li class="mb-2 mt-3">
                    <p class="small text-muted fw-bold mb-1 px-3" style="font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase;">Sipariş & Teslimat</p>
                </li>
                <li class="mb-1">
                    <a href="{{ route('customer.dashboard') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all {{ request()->routeIs('customer.dashboard') ? 'sidebar-nav-active shadow-sm' : 'sidebar-nav-inactive' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">dashboard</span>
                        <span class="fw-semibold" style="font-size: 14px;">Dashboard</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('customer.orders.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all {{ request()->routeIs('customer.orders.*') ? 'sidebar-nav-active shadow-sm' : 'sidebar-nav-inactive' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">shopping_cart</span>
                        <span class="fw-semibold" style="font-size: 14px;">Siparişlerim</span>
                    </a>
                </li>

                <!-- Belgeler & Ödemeler -->
                <li class="mb-2 mt-3">
                    <p class="small text-muted fw-bold mb-1 px-3" style="font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase;">Belgeler & Ödemeler</p>
                </li>
                <li class="mb-1">
                    <a href="{{ route('customer.documents.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all {{ request()->routeIs('customer.documents.*') ? 'sidebar-nav-active shadow-sm' : 'sidebar-nav-inactive' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">description</span>
                        <span class="fw-semibold" style="font-size: 14px;">Belgelerim</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('customer.invoices.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all {{ request()->routeIs('customer.invoices.*') ? 'sidebar-nav-active shadow-sm' : 'sidebar-nav-inactive' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">receipt_long</span>
                        <span class="fw-semibold" style="font-size: 14px;">Faturalarım</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('customer.payments.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all {{ request()->routeIs('customer.payments.*') ? 'sidebar-nav-active shadow-sm' : 'sidebar-nav-inactive' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">payments</span>
                        <span class="fw-semibold" style="font-size: 14px;">Ödemelerim</span>
                    </a>
                </li>

                <!-- Bildirimler -->
                <li class="mb-1">
                    <a href="{{ route('customer.notifications.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all {{ request()->routeIs('customer.notifications.*') ? 'sidebar-nav-active shadow-sm' : 'sidebar-nav-inactive' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">notifications</span>
                        <span class="fw-semibold" style="font-size: 14px;">Bildirimler</span>
                        @php
                            $unreadCount = $sidebarUser?->unreadNotificationsCount() ?? 0;
                        @endphp
                        @if($unreadCount > 0)
                            <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.7rem; min-width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; padding: 0 4px;">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </a>
                </li>

                <!-- Hesap & Tercihler -->
                <li class="mb-2 mt-3">
                    <p class="small text-muted fw-bold mb-1 px-3" style="font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase;">Hesap & Tercihler</p>
                </li>
                <li class="mb-1">
                    <a href="{{ route('customer.favorite-addresses.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all {{ request()->routeIs('customer.favorite-addresses.*') ? 'sidebar-nav-active shadow-sm' : 'sidebar-nav-inactive' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">location_on</span>
                        <span class="fw-semibold" style="font-size: 14px;">Favori Adresler</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('customer.order-templates.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all {{ request()->routeIs('customer.order-templates.*') ? 'sidebar-nav-active shadow-sm' : 'sidebar-nav-inactive' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">content_copy</span>
                        <span class="fw-semibold" style="font-size: 14px;">Sipariş Şablonları</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('customer.profile') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all {{ request()->routeIs('customer.profile') ? 'sidebar-nav-active shadow-sm' : 'sidebar-nav-inactive' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">person</span>
                        <span class="fw-semibold" style="font-size: 14px;">Profil</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- User Info -->
        <div class="px-3 py-2 border-top">
            <div class="d-flex align-items-center gap-2 mb-2">
                @if(Auth::user()->avatar)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="rounded-3xl" style="width: 36px; height: 36px; object-fit: cover; flex-shrink: 0;">
                @else
                    <div class="rounded-3xl border d-flex align-items-center justify-content-center bg-white text-secondary" style="width: 36px; height: 36px; flex-shrink: 0;">
                        <span class="material-symbols-outlined" style="font-size: 20px;">person</span>
                    </div>
                @endif
                <div class="flex-grow-1" style="min-width: 0;">
                    <p class="small fw-bold text-dark mb-0" style="font-size: 13px;">{{ Auth::user()->name ?? 'Kullanıcı' }}</p>
                    <p class="small text-secondary mb-0" style="font-size: 10px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ Auth::user()->email ?? '' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-logout-sidebar w-100 rounded-3xl fw-semibold d-flex align-items-center justify-content-center gap-2 transition-all" style="padding: 8px; border-width: 1.5px; border-color: #F87171; color: #F87171; background: transparent; font-size: 13px;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">logout</span>
                    <span>Çıkış Yap</span>
                </button>
            </form>
        </div>
    </div>
</aside>
