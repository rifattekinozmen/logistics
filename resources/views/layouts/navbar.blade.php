<nav class="glass-card border-bottom px-4 py-3 sticky-top bg-primary-200" style="z-index: 999; background: linear-gradient(135deg, rgba(232, 235, 255, 0.9), rgba(255, 255, 255, 0.95)) !important;">
    <div class="d-flex align-items-center justify-content-between">
        <!-- Mobile Sidebar Toggle -->
        <button class="btn btn-link text-secondary d-lg-none p-2 me-3 hover:bg-white rounded-circle transition-all" type="button" id="sidebarToggle">
            <span class="material-symbols-outlined">menu</span>
        </button>
        
        <!-- Sayfa yolu (breadcrumb) -->
        <div>
            <nav aria-label="breadcrumb" class="mb-0">
                <ol class="breadcrumb mb-0 small fw-medium flex-wrap navbar-breadcrumb" style="--bs-breadcrumb-divider: '›'; --bs-breadcrumb-divider-color: var(--bs-primary); background: transparent; padding: 0;">
                    @foreach ($navBreadcrumbs ?? [['label' => 'Ana Sayfa', 'url' => route('admin.dashboard')]] as $crumb)
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                            @if ($crumb['url'] && !$loop->last)
                                <a href="{{ $crumb['url'] }}" class="text-primary text-decoration-none">{{ $crumb['label'] }}</a>
                            @else
                                <span class="text-primary">{{ $crumb['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>

        <!-- Actions -->
        <div class="d-flex align-items-center gap-3">
            @yield('navbar-actions')
            
            <!-- Company Switch (activeCompanyForLayout ve userCompaniesForLayout middleware'de paylaşılıyor; tekrarlanan sorgu yok) -->
            @auth
            @php
                $activeCompany = $activeCompanyForLayout ?? null;
                $userCompanies = $userCompaniesForLayout ?? collect();
            @endphp
            <div class="dropdown">
                <button class="btn btn-link text-secondary p-2 rounded-3xl hover:bg-white transition-all d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="material-symbols-outlined">business</span>
                    <span class="fw-semibold d-none d-md-inline">
                        @if($activeCompany)
                            {{ $activeCompany->short_name ?? $activeCompany->name }}
                        @elseif($userCompanies->count() > 0)
                            {{ $userCompanies->first()->short_name ?? $userCompanies->first()->name }}
                        @else
                            Firma Seç
                        @endif
                    </span>
                    @if($userCompanies->count() > 1)
                    <span class="badge bg-primary text-white rounded-pill ms-1">{{ $userCompanies->count() }}</span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3xl p-2 bg-white" style="min-width: 250px;">
                    @php
                        try {
                            $settingsUrl = $activeCompany && $activeCompany->id
                                ? route('admin.companies.settings', $activeCompany->id) 
                                : route('admin.companies.select');
                        } catch (\Exception $e) {
                            $settingsUrl = route('admin.companies.select');
                        }
                    @endphp
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-3 p-3 rounded-2xl hover:bg-primary-200 transition-all bg-primary-50" href="{{ $settingsUrl }}" target="_self">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px;">
                                <span class="material-symbols-outlined" style="font-size: 18px;">settings</span>
                            </div>
                            <div class="grow text-start">
                                <p class="small fw-bold text-dark mb-0">Firma Ayarları</p>
                                <p class="small text-secondary mb-0">
                                    @if($activeCompany)
                                        {{ $activeCompany->name }}
                                    @else
                                        Firma seçin veya oluşturun
                                    @endif
                                </p>
                            </div>
                            <span class="material-symbols-outlined text-primary" style="font-size: 18px;">arrow_forward</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <h6 class="dropdown-header fw-bold bg-primary-200 rounded-2xl mb-2">Firma{{ $userCompanies->count() > 1 ? ' Değiştir' : '' }}</h6>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    @if($userCompanies->count() > 0)
                    @foreach($userCompanies as $company)
                    <li>
                        @if($userCompanies->count() > 1)
                        <form action="{{ route('admin.companies.switch') }}" method="POST" class="company-switch-form">
                            @csrf
                            <input type="hidden" name="company_id" value="{{ $company->id }}">
                            <button type="submit" class="dropdown-item d-flex align-items-center gap-3 p-3 rounded-2xl hover:bg-primary-200 transition-all w-100 {{ $activeCompany && $company->id === $activeCompany->id ? 'bg-primary-200' : '' }}">
                        @else
                        <div class="dropdown-item d-flex align-items-center gap-3 p-3 rounded-2xl bg-primary-200">
                        @endif
                            <div class="bg-primary-200 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <span class="material-symbols-outlined text-primary" style="font-size: 18px;">business</span>
                            </div>
                            <div class="grow text-start">
                                <p class="small fw-bold text-dark mb-0">{{ $company->name }}</p>
                                @if($company->short_name)
                                <p class="small text-secondary mb-0">{{ $company->short_name }}</p>
                                @endif
                            </div>
                            @if($activeCompany && $company->id === $activeCompany->id)
                            <span class="material-symbols-outlined text-primary" style="font-size: 18px;">check_circle</span>
                            @endif
                        @if($userCompanies->count() > 1)
                            </button>
                        </form>
                        @else
                        </div>
                        @endif
                    </li>
                    @endforeach
                    @else
                    <li>
                        <div class="dropdown-item text-center py-3">
                            <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 32px;">business_center</span>
                            <p class="small text-secondary mb-2">Henüz bir firmaya atanmamışsınız</p>
                            <a href="{{ route('admin.companies.select') }}" class="btn btn-primary btn-sm rounded-2xl">
                                Firma Seç
                            </a>
                        </div>
                    </li>
                    @endif
                </ul>
            </div>
            @endauth
            
            <!-- Notifications -->
            <div class="dropdown">
                <button class="btn btn-link text-secondary p-2 rounded-circle position-relative hover:bg-white transition-all" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.5rem;">
                        3
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3xl p-2 bg-white" style="min-width: 300px;">
                    <li>
                        <h6 class="dropdown-header fw-bold bg-primary-200 rounded-2xl mb-2">Bildirimler</h6>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-start gap-3 p-3 rounded-2xl hover:bg-primary-200 transition-all" href="#">
                            <div class="bg-primary-200 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <span class="material-symbols-outlined text-primary">local_shipping</span>
                            </div>
                            <div class="grow">
                                <p class="small fw-bold text-dark mb-1">Yeni sevkiyat</p>
                                <p class="small text-secondary mb-0">2 dakika önce</p>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-start gap-3 p-3 rounded-2xl hover:bg-success-200 transition-all" href="#">
                            <div class="bg-success-200 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <span class="material-symbols-outlined text-success">check_circle</span>
                            </div>
                            <div class="grow">
                                <p class="small fw-bold text-dark mb-1">Sipariş tamamlandı</p>
                                <p class="small text-secondary mb-0">15 dakika önce</p>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-center text-primary fw-semibold rounded-2xl hover:bg-primary-200 transition-all" href="#">Tümünü Gör</a>
                    </li>
                </ul>
            </div>

            <!-- User Menu -->
            <div class="dropdown">
                <button class="btn btn-link text-secondary p-0 hover:opacity-80 transition-all" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @if(Auth::user()->avatar)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                    @else
                        <div class="rounded-circle border d-flex align-items-center justify-content-center bg-white text-secondary" style="width: 40px; height: 40px;">
                            <span class="material-symbols-outlined" style="font-size: 22px;">person</span>
                        </div>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3xl p-2 bg-white" style="min-width: 200px;">
                    <li>
                        <div class="px-3 py-2 bg-primary-200 rounded-2xl mb-2">
                            <p class="small fw-bold text-dark mb-0">{{ Auth::user()->name ?? 'Kullanıcı' }}</p>
                            <p class="small text-secondary mb-0">{{ Auth::user()->email ?? '' }}</p>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 rounded-2xl hover:bg-primary-200 transition-all" href="{{ route('admin.profile.show') }}">
                            <span class="material-symbols-outlined">person</span>
                            <span>Profil</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 rounded-2xl hover:bg-primary-200 transition-all" href="{{ route('admin.settings.show') }}">
                            <span class="material-symbols-outlined">settings</span>
                            <span>Ayarlar</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2 rounded-2xl text-danger hover:bg-danger-200 transition-all w-100">
                                <span class="material-symbols-outlined">logout</span>
                                <span>Çıkış Yap</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
