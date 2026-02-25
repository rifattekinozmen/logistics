@php
    $sidebarUser = Auth::user();
    $sidebarConfig = config('sidebar.admin', []);
    $unreadCount = $unreadCount ?? 0;
    $settingsUrl = ($activeCompanyForLayout ?? null)
        ? route('admin.companies.settings', $activeCompanyForLayout)
        : route('admin.companies.select');

    $canShowItem = function ($item) use ($sidebarUser) {
        if (empty($item['permission'])) {
            return true;
        }
        if (! $sidebarUser || ! method_exists($sidebarUser, 'hasPermission')) {
            return true;
        }
        return $sidebarUser->hasPermission($item['permission']);
    };

    $isActive = function ($pattern) {
        if (str_contains($pattern, '|')) {
            return collect(explode('|', $pattern))->contains(fn ($p) => request()->routeIs(trim($p)));
        }
        return request()->routeIs($pattern);
    };

    $itemUrl = function ($item) use ($settingsUrl) {
        if (isset($item['url_key']) && $item['url_key'] === 'settings') {
            return $settingsUrl;
        }
        return route($item['route']);
    };
@endphp
<aside class="position-fixed top-0 start-0 h-100 border-end shadow-sm sidebar-light" style="width: 280px; z-index: 1000;">
    <div class="d-flex flex-column h-100">
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

        <nav class="flex-grow-1 p-2 overflow-y-auto custom-scrollbar">
            <ul class="list-unstyled mb-0">
                @foreach ($sidebarConfig['top'] ?? [] as $item)
                    @if($canShowItem($item))
                        <li class="mb-2">
                            <a href="{{ $itemUrl($item) }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ $isActive($item['pattern']) ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                                <span class="material-symbols-outlined" style="font-size: 18px;">{{ $item['icon'] }}</span>
                                <span class="fw-semibold" style="font-size: 14px;">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endif
                @endforeach

                @foreach ($sidebarConfig['groups'] ?? [] as $group)
                    <li class="mb-2 mt-3">
                        <p class="small text-muted fw-bold mb-1 px-3 sidebar-group-heading" style="font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase;">{{ $group['heading'] }}</p>
                    </li>
                    @foreach ($group['items'] as $item)
                        @if($canShowItem($item))
                            <li class="mb-1">
                                <a href="{{ $itemUrl($item) }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ $isActive($item['pattern']) ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                                    <span class="material-symbols-outlined" style="font-size: 18px;">{{ $item['icon'] }}</span>
                                    <span class="fw-semibold" style="font-size: 14px;">{{ $item['label'] }}</span>
                                    @if(($item['badge'] ?? '') === 'unread' && $unreadCount > 0)
                                        <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 10px;">{{ $unreadCount }}</span>
                                    @endif
                                </a>
                            </li>
                            @if(! empty($item['children']))
                                <li class="mb-2 ps-4">
                                    <div class="d-flex flex-column gap-1">
                                        @foreach ($item['children'] as $child)
                                            @php
                                                $params = $child['params'] ?? [];
                                                $childActive = collect($params)->every(fn ($value, $key) => request($key) == $value);
                                            @endphp
                                            <a href="{{ route($item['route'], $params) }}" class="small text-decoration-none {{ $childActive ? 'text-primary fw-semibold' : 'text-secondary' }}">• {{ $child['label'] }}</a>
                                        @endforeach
                                    </div>
                                </li>
                            @endif
                        @endif
                    @endforeach
                @endforeach

                @if(! empty($sidebarConfig['customer_portal']) && $sidebarUser?->roles?->pluck('name')->contains($sidebarConfig['customer_portal']['role']))
                    <li class="mb-2 mt-3">
                        <p class="small text-muted fw-bold mb-1 px-3 sidebar-group-heading" style="font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase;">{{ $sidebarConfig['customer_portal']['heading'] }}</p>
                    </li>
                    <li class="mb-1">
                        @php $cp = $sidebarConfig['customer_portal']; @endphp
                        <a href="{{ route($cp['route']) }}" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3xl text-decoration-none transition-all sidebar-link {{ $isActive($cp['pattern']) ? 'bg-primary text-white shadow-sm' : 'text-secondary' }}">
                            <span class="material-symbols-outlined" style="font-size: 18px;">{{ $cp['icon'] }}</span>
                            <span class="fw-semibold" style="font-size: 14px;">{{ $cp['label'] }}</span>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>

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
