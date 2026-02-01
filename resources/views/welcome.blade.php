<!DOCTYPE html>
<html class="light" lang="tr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Cemiloğlu Şirketler Grubu - Lojistik Yönetim Sistemi</title>

        <!-- Fonts: subset + non-blocking -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
        <noscript><link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet"></noscript>
        <noscript><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"></noscript>

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root {
                --cemiloglu-red: #c41e3a;
                --cemiloglu-red-hover: #a0182f;
                --cemiloglu-blue: #0d3b66;
                --cemiloglu-blue-light: #1a4d7a;
                --cemiloglu-silver: #64748b;
                --cemiloglu-bg: #f1f5f9;
                --cemiloglu-bg-blue-tint: #eef4fb;
            }
            body {
                background: linear-gradient(135deg, #f1f5f9 0%, #eef4fb 50%, #e2eaf3 100%);
                min-height: 100vh;
            }

            .hero-gradient {
                background: linear-gradient(135deg, rgba(13, 59, 102, 0.06) 0%, rgba(196, 30, 58, 0.04) 100%);
            }

            .feature-card {
                transition: all 0.3s ease;
                border-color: rgba(13, 59, 102, 0.12) !important;
            }

            .feature-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 8px 24px -4px rgba(13, 59, 102, 0.12) !important;
                border-color: rgba(13, 59, 102, 0.2) !important;
            }

            .glass-header {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                border-bottom: 1px solid rgba(13, 59, 102, 0.08);
            }

            .hero-title-gradient {
                background: linear-gradient(135deg, var(--cemiloglu-blue) 0%, var(--cemiloglu-blue-light) 50%, var(--cemiloglu-red) 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .cta-gradient {
                background: linear-gradient(135deg, var(--cemiloglu-blue) 0%, var(--cemiloglu-blue-light) 50%, rgba(196, 30, 58, 0.9) 100%);
            }

            .link-cemiloglu {
                color: var(--cemiloglu-blue) !important;
            }
            .link-cemiloglu:hover {
                color: var(--cemiloglu-red) !important;
            }

            .btn-cta-primary {
                background: var(--cemiloglu-red) !important;
                border: none;
                color: white !important;
            }
            .btn-cta-primary:hover {
                background: var(--cemiloglu-red-hover) !important;
                color: white !important;
            }
        </style>
    </head>
    <body class="min-vh-100 d-flex flex-column">
        <header class="glass-header px-4 py-3 sticky-top" style="z-index: 1050;">
            <div class="container-fluid" style="max-width: 1600px;">
                <div class="d-flex align-items-center justify-content-between">
                    <a href="/" class="text-decoration-none d-flex align-items-center gap-3">
                        <img src="{{ asset('images/cemiloglu.svg') }}" alt="Cemiloğlu Şirketler Grubu" width="144" height="48" loading="eager" decoding="async" style="max-height: 48px; width: auto; object-fit: contain;" />
                        <div>
                            <h1 class="h5 fw-bold mb-0" style="color: var(--cemiloglu-blue); letter-spacing: 0.02em;">Lojistik Yönetim</h1>
                            <p class="mb-0 fw-semibold" style="font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--cemiloglu-silver);">Cemiloğlu Şirketler Grubu</p>
                        </div>
                    </a>
                    <div class="d-flex align-items-center gap-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="text-decoration-none fw-semibold transition-all link-cemiloglu" style="font-size: 0.875rem;">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold transition-all link-cemiloglu" style="font-size: 0.875rem;">
                                    Giriş Yap
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn btn-cta-primary rounded-3xl px-4 py-2 fw-semibold shadow-sm transition-all" style="font-size: 0.875rem;">
                                        Kayıt Ol
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <main class="grow d-flex align-items-center justify-content-center p-4 p-lg-5">
            <div class="container-fluid" style="max-width: 1600px;">
                <!-- Hero Section -->
                <div class="text-center mb-5">
                    <div class="position-relative mx-auto mb-4 d-flex justify-content-center">
                        <img src="{{ asset('images/cemiloglu.svg') }}" alt="Cemiloğlu Şirketler Grubu" width="360" height="120" loading="eager" decoding="async" style="max-height: 120px; width: auto; object-fit: contain;" />
                    </div>
                    <p class="fs-4 text-secondary fw-medium mx-auto mb-4" style="max-width: 48rem; color: var(--cemiloglu-silver);">
                        Modern lojistik yönetim sistemi ile sevkiyatlarınızı kolayca takip edin, stoklarınızı yönetin ve işlerinizi optimize edin
                    </p>
                </div>

                <!-- Features Section -->
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="bg-white rounded-3xl shadow-sm border p-5 h-100 feature-card">
                            <div class="rounded-2xl d-flex align-items-center justify-content-center mb-4" style="width: 64px; height: 64px; background: rgba(13, 59, 102, 0.08);">
                                <span class="material-symbols-outlined" style="font-size: 2rem; color: var(--cemiloglu-blue);">local_shipping</span>
                            </div>
                            <h3 class="h4 fw-bold mb-3" style="color: var(--cemiloglu-blue);">Sevkiyat Yönetimi</h3>
                            <p class="text-secondary mb-0" style="color: var(--cemiloglu-silver); line-height: 1.6;">
                                Tüm sevkiyatlarınızı tek bir platformdan yönetin, takip edin ve gerçek zamanlı durum bilgilerini görüntüleyin.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="bg-white rounded-3xl shadow-sm border p-5 h-100 feature-card">
                            <div class="rounded-2xl d-flex align-items-center justify-content-center mb-4" style="width: 64px; height: 64px; background: rgba(13, 59, 102, 0.08);">
                                <span class="material-symbols-outlined" style="font-size: 2rem; color: var(--cemiloglu-blue);">warehouse</span>
                            </div>
                            <h3 class="h4 fw-bold mb-3" style="color: var(--cemiloglu-blue);">Depo Yönetimi</h3>
                            <p class="text-secondary mb-0" style="color: var(--cemiloglu-silver); line-height: 1.6;">
                                Stoklarınızı gerçek zamanlı olarak takip edin, envanter yönetimini optimize edin ve otomatik bildirimler alın.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="bg-white rounded-3xl shadow-sm border p-5 h-100 feature-card">
                            <div class="rounded-2xl d-flex align-items-center justify-content-center mb-4" style="width: 64px; height: 64px; background: rgba(196, 30, 58, 0.08);">
                                <span class="material-symbols-outlined" style="font-size: 2rem; color: var(--cemiloglu-red);">verified_user</span>
                            </div>
                            <h3 class="h4 fw-bold mb-3" style="color: var(--cemiloglu-blue);">Kimlik Doğrulama</h3>
                            <p class="text-secondary mb-0" style="color: var(--cemiloglu-silver); line-height: 1.6;">
                                MERNİS entegrasyonu ile güvenli kimlik doğrulama sistemi ve KVKK uyumlu veri yönetimi.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Additional Features -->
                <div class="row g-4 mb-5">
                    <div class="col-md-3">
                        <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 text-center feature-card">
                            <div class="rounded-2xl d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px; background: rgba(13, 59, 102, 0.08);">
                                <span class="material-symbols-outlined" style="font-size: 1.75rem; color: var(--cemiloglu-blue);">directions_car</span>
                            </div>
                            <h4 class="h6 fw-bold mb-2" style="color: var(--cemiloglu-blue);">Araç Yönetimi</h4>
                            <p class="small text-secondary mb-0" style="color: var(--cemiloglu-silver);">Filo yönetimi ve bakım takibi</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 text-center feature-card">
                            <div class="rounded-2xl d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px; background: rgba(13, 59, 102, 0.08);">
                                <span class="material-symbols-outlined" style="font-size: 1.75rem; color: var(--cemiloglu-blue);">groups</span>
                            </div>
                            <h4 class="h6 fw-bold mb-2" style="color: var(--cemiloglu-blue);">Personel Yönetimi</h4>
                            <p class="small text-secondary mb-0" style="color: var(--cemiloglu-silver);">Çalışan takibi ve vardiya yönetimi</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 text-center feature-card">
                            <div class="rounded-2xl d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px; background: rgba(196, 30, 58, 0.08);">
                                <span class="material-symbols-outlined" style="font-size: 1.75rem; color: var(--cemiloglu-red);">shopping_cart</span>
                            </div>
                            <h4 class="h6 fw-bold mb-2" style="color: var(--cemiloglu-blue);">Sipariş Takibi</h4>
                            <p class="small text-secondary mb-0" style="color: var(--cemiloglu-silver);">Sipariş yönetimi ve takibi</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 text-center feature-card">
                            <div class="rounded-2xl d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px; background: rgba(13, 59, 102, 0.08);">
                                <span class="material-symbols-outlined" style="font-size: 1.75rem; color: var(--cemiloglu-blue);">analytics</span>
                            </div>
                            <h4 class="h6 fw-bold mb-2" style="color: var(--cemiloglu-blue);">Raporlama</h4>
                            <p class="small text-secondary mb-0" style="color: var(--cemiloglu-silver);">Detaylı analiz ve raporlar</p>
                        </div>
                    </div>
                </div>

                <!-- CTA Section -->
                <div class="rounded-3xl p-5 text-white shadow-lg position-relative overflow-hidden cta-gradient">
                    <div class="position-absolute top-0 end-0 opacity-10" style="width: 300px; height: 300px; background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%); transform: translate(100px, -100px);"></div>
                    <div class="mx-auto text-center position-relative" style="max-width: 48rem;">
                        <h2 class="display-5 fw-bold mb-3">Hemen Başlayın</h2>
                        <p class="fs-5 mb-4" style="opacity: 0.95;">Modern lojistik yönetim sistemimiz ile işlerinizi kolaylaştırın ve verimliliğinizi artırın</p>
                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                            @if (Route::has('login'))
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="btn btn-light rounded-2xl px-5 py-3 fw-bold fs-5 shadow-lg transition-all" style="color: var(--cemiloglu-blue);">
                                        Dashboard'a Git
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-light rounded-2xl px-5 py-3 fw-bold fs-5 shadow-lg transition-all" style="color: var(--cemiloglu-blue);">
                                        Giriş Yap
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="btn rounded-2xl px-5 py-3 fw-bold fs-5 border border-white text-white transition-all" style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px);">
                                            Kayıt Ol
                                        </a>
                                    @endif
                                @endauth
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="bg-white border-top py-4 mt-5" style="border-color: rgba(13, 59, 102, 0.08) !important;">
            <div class="container-fluid px-4" style="max-width: 1600px;">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-4">
                    <a href="/" class="text-decoration-none d-flex align-items-center gap-3">
                        <img src="{{ asset('images/cemiloglu.svg') }}" alt="Cemiloğlu Şirketler Grubu" width="108" height="36" loading="eager" decoding="async" style="max-height: 36px; width: auto; object-fit: contain;" />
                        <p class="small fw-semibold mb-0" style="color: var(--cemiloglu-silver);">© {{ date('Y') }} Cemiloğlu Şirketler Grubu. Tüm hakları saklıdır.</p>
                    </a>
                    <div class="d-flex align-items-center gap-3 px-4 py-2 rounded-pill border" style="background: rgba(13, 59, 102, 0.04); border-color: rgba(13, 59, 102, 0.12) !important;">
                        <div class="d-flex align-items-center gap-2" style="color: var(--cemiloglu-blue);">
                            <span class="material-symbols-outlined">lock</span>
                            <span class="small fw-bold text-uppercase" style="letter-spacing: 0.05em; color: var(--cemiloglu-blue);">Güvenli Sistem</span>
                        </div>
                        <div class="vr" style="height: 16px; opacity: 0.3;"></div>
                        <p class="small fw-bold mb-0" style="color: var(--cemiloglu-silver);">KVKK Uyumlu</p>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>
