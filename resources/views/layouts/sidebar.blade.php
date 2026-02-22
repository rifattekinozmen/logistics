<aside class="position-fixed top-0 start-0 h-100 border-end shadow-sm sidebar-light" style="width: 280px; z-index: 1000;">
    <div class="d-flex flex-column h-100">
        <!-- Logo - sabit yükseklik ile yükleme sırasında zıplama önlenir -->
        <div class="px-2 py-2 border-bottom sidebar-logo-wrap">
            <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center justify-content-center w-100 sidebar-logo-inner text-decoration-none" title="Ana sayfaya git">
                @php
                    $activeCompany = $activeCompanyForLayout ?? null;
                    $logoUrl = $activeCompany && $activeCompany->logo_path ? $activeCompany->logo_url : null;
                @endphp
                @if($logoUrl && $activeCompany)
                    <img src="{{ $logoUrl }}" alt="{{ $activeCompany->name }}" class="w-100 sidebar-logo-img" width="248" height="67" loading="eager" decoding="async" style="height: auto; max-height: 67px; object-fit: contain; display: block;">
                @else
                    <img src="{{ asset('images/cemiloglu.svg') }}" alt="Cemiloğlu Şirketler Grubu" class="w-100 sidebar-logo-img" width="248" height="67" loading="eager" decoding="async" style="height: auto; max-height: 67px; object-fit: contain; display: block;">
                @endif
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-grow-1 p-2 overflow-y-auto custom-scrollbar">
            <ul class="list-unstyled mb-0">
                <li class="mb-2">
                    <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">dashboard</span>
                        <span class="fw-semibold" style="font-size: 14px;">Dashboard</span>
                    </a>
                </li>
                
                @php
                    $sidebarUser = Auth::user();
                @endphp

                <!-- 1. Müşteri & Sipariş (iş akışı başı) -->
                <li class="mb-2 mt-3">
                    <p class="small text-muted fw-bold mb-1 px-3 sidebar-group-heading" style="font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase;">Müşteri & Sipariş</p>
                </li>
                @if(!$sidebarUser || !method_exists($sidebarUser, 'hasPermission') || $sidebarUser->hasPermission('customer.view'))
                <li class="mb-1">
                    <a href="{{ route('admin.customers.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.customers.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">people</span>
                        <span class="fw-semibold" style="font-size: 14px;">Müşteriler</span>
                    </a>
                </li>
                @endif
                @if(!$sidebarUser || !method_exists($sidebarUser, 'hasPermission') || $sidebarUser->hasPermission('order.view'))
                <li class="mb-1">
                    <a href="{{ route('admin.orders.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.orders.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">shopping_cart</span>
                        <span class="fw-semibold" style="font-size: 14px;">Siparişler</span>
                    </a>
                </li>
                @endif
                @if(!$sidebarUser || !method_exists($sidebarUser, 'hasPermission') || $sidebarUser->hasPermission('customer.view'))
                <li class="mb-1">
                    <a href="{{ route('admin.business-partners.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.business-partners.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">handshake</span>
                        <span class="fw-semibold" style="font-size: 14px;">İş Ortakları</span>
                    </a>
                </li>
                @endif
                @if(!$sidebarUser || !method_exists($sidebarUser, 'hasPermission') || $sidebarUser->hasPermission('order.view'))
                <li class="mb-1">
                    <a href="{{ route('admin.pricing-conditions.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.pricing-conditions.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">price_check</span>
                        <span class="fw-semibold" style="font-size: 14px;">Fiyatlandırma</span>
                    </a>
                </li>
                @endif

                <!-- 2. Depo & Sevkiyat -->
                <li class="mb-2 mt-3">
                    <p class="small text-muted fw-bold mb-1 px-3 sidebar-group-heading" style="font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase;">Depo & Sevkiyat</p>
                </li>
                @if(!$sidebarUser || !method_exists($sidebarUser, 'hasPermission') || $sidebarUser->hasPermission('warehouse.view'))
                <li class="mb-1">
                    <a href="{{ route('admin.warehouses.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.warehouses.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">warehouse</span>
                        <span class="fw-semibold" style="font-size: 14px;">Depo & Stok</span>
                    </a>
                </li>
                @endif
                @if(!$sidebarUser || !method_exists($sidebarUser, 'hasPermission') || $sidebarUser->hasPermission('shipment.view'))
                <li class="mb-1">
                    <a href="{{ route('admin.shipments.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.shipments.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">inventory_2</span>
                        <span class="fw-semibold" style="font-size: 14px;">Sevkiyatlar</span>
                    </a>
                </li>
                @endif
                <li class="mb-1">
                    <a href="{{ route('admin.delivery-imports.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.delivery-imports.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">upload_file</span>
                        <span class="fw-semibold" style="font-size: 14px;">Teslimat Raporları</span>
                    </a>
                </li>

                <!-- 3. Filo Yönetimi -->
                <li class="mb-2 mt-3">
                    <p class="small text-muted fw-bold mb-1 px-3 sidebar-group-heading" style="font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase;">Filo Yönetimi</p>
                </li>
                @if(!$sidebarUser || !method_exists($sidebarUser, 'hasPermission') || $sidebarUser->hasPermission('vehicle.view'))
                <li class="mb-1">
                    <a href="{{ route('admin.vehicles.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.vehicles.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">local_shipping</span>
                        <span class="fw-semibold" style="font-size: 14px;">Araçlar</span>
                    </a>
                </li>
                @endif
                <li class="mb-1">
                    <a href="{{ route('admin.work-orders.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.work-orders.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">build</span>
                        <span class="fw-semibold" style="font-size: 14px;">İş Emirleri & Bakım</span>
                    </a>
                </li>
                @if(!$sidebarUser || !method_exists($sidebarUser, 'hasPermission') || $sidebarUser->hasPermission('fuel_price.view'))
                <li class="mb-1">
                    <a href="{{ route('admin.fuel-prices.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.fuel-prices.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">local_gas_station</span>
                        <span class="fw-semibold" style="font-size: 14px;">Motorin Fiyat</span>
                    </a>
                </li>
                @endif

                <!-- 4. Personel & İK -->
                <li class="mb-2 mt-3">
                    <p class="small text-muted fw-bold mb-1 px-3 sidebar-group-heading" style="font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase;">Personel & İK</p>
                </li>
                @if(!$sidebarUser || !method_exists($sidebarUser, 'hasPermission') || $sidebarUser->hasPermission('employee.view'))
                <li class="mb-1">
                    <a href="{{ route('admin.personnel.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.personnel.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">groups</span>
                        <span class="fw-semibold" style="font-size: 14px;">Personel</span>
                    </a>
                </li>
                @endif
                <li class="mb-1">
                    <a href="{{ route('admin.shifts.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.shifts.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">schedule</span>
                        <span class="fw-semibold" style="font-size: 14px;">Vardiyalar</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('admin.leaves.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.leaves.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">event_available</span>
                        <span class="fw-semibold" style="font-size: 14px;">İzinler</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('admin.personnel_attendance.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.personnel_attendance.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">calendar_month</span>
                        <span class="fw-semibold" style="font-size: 14px;">Puantaj</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('admin.advances.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.advances.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">account_balance_wallet</span>
                        <span class="fw-semibold" style="font-size: 14px;">Avanslar</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('admin.payrolls.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.payrolls.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">receipt_long</span>
                        <span class="fw-semibold" style="font-size: 14px;">Bordrolar</span>
                    </a>
                </li>

                <!-- 5. Finans & Belgeler -->
                <li class="mb-2 mt-3">
                    <p class="small text-muted fw-bold mb-1 px-3 sidebar-group-heading" style="font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase;">Finans & Belgeler</p>
                </li>
                @if(!$sidebarUser || !method_exists($sidebarUser, 'hasPermission') || $sidebarUser->hasPermission('payment.view'))
                <li class="mb-1">
                    <a href="{{ route('admin.payments.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.payments.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">payments</span>
                        <span class="fw-semibold" style="font-size: 14px;">Finans</span>
                    </a>
                </li>
                @endif
                @if(!$sidebarUser || !method_exists($sidebarUser, 'hasPermission') || $sidebarUser->hasPermission('document.view'))
                <li class="mb-1">
                    <a href="{{ route('admin.documents.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.documents.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">description</span>
                        <span class="fw-semibold" style="font-size: 14px;">Belgeler</span>
                    </a>
                </li>
                @endif

                <!-- 6. Analitik & Raporlama -->
                <li class="mb-2 mt-3">
                    <p class="small text-muted fw-bold mb-1 px-3 sidebar-group-heading" style="font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase;">Analitik & Raporlama</p>
                </li>
                <li class="mb-1">
                    <a href="{{ route('admin.analytics.finance') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.analytics.finance') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">analytics</span>
                        <span class="fw-semibold" style="font-size: 14px;">Finans Analitik</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('admin.analytics.operations') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.analytics.operations') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">donut_small</span>
                        <span class="fw-semibold" style="font-size: 14px;">Operasyon Analitik</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('admin.analytics.fleet') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.analytics.fleet') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">speed</span>
                        <span class="fw-semibold" style="font-size: 14px;">Filo Analitik</span>
                    </a>
                </li>

                <!-- 7. Sistem & Yönetimi -->
                <li class="mb-2 mt-3">
                    <p class="small text-muted fw-bold mb-1 px-3 sidebar-group-heading" style="font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase;">Sistem & Yönetimi</p>
                </li>
                <li class="mb-1">
                    <a href="{{ route('admin.calendar.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.calendar.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">calendar_today</span>
                        <span class="fw-semibold" style="font-size: 14px;">Takvim</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('admin.notifications.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.notifications.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">notifications</span>
                        <span class="fw-semibold" style="font-size: 14px;">Bildirimler</span>
                        @if(($unreadCount ?? 0) > 0)
                            <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 10px;">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </li>
                @php
                    // $activeCompanyForLayout middleware (active.company) üzerinden geliyor
                    $settingsUrl = ($activeCompanyForLayout ?? null)
                        ? route('admin.companies.settings', $activeCompanyForLayout)
                        : route('admin.companies.select');
                @endphp
                <li class="mb-1">
                    <a href="{{ route('admin.companies.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.companies.index') || request()->routeIs('admin.companies.create') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">business</span>
                        <span class="fw-semibold" style="font-size: 14px;">Firmalar</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ $settingsUrl }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.companies.settings') || request()->routeIs('admin.companies.select') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">settings</span>
                        <span class="fw-semibold" style="font-size: 14px;">Firma Ayarları</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('admin.profile.show') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.profile.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">person</span>
                        <span class="fw-semibold" style="font-size: 14px;">Profil</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('admin.settings.show') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.settings.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">settings</span>
                        <span class="fw-semibold" style="font-size: 14px;">Ayarlar</span>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('admin.users.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('admin.users.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">group</span>
                        <span class="fw-semibold" style="font-size: 14px;">Kullanıcılar</span>
                    </a>
                </li>
                
                <!-- Müşteri -->
                @if($sidebarUser && $sidebarUser->roles->pluck('name')->contains('customer'))
                <li class="mb-2 mt-3">
                    <p class="small text-muted fw-bold mb-1 px-3 sidebar-group-heading" style="font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase;">Müşteri</p>
                </li>
                <li class="mb-1">
                    <a href="{{ route('customer.dashboard') }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ request()->routeIs('customer.*') ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                        <span class="material-symbols-outlined" style="font-size: 18px;">store</span>
                        <span class="fw-semibold" style="font-size: 14px;">Müşteri Portalı</span>
                    </a>
                </li>
                @endif
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
