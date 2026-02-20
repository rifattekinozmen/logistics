<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cemiloğlu Lojistik - Çimento ve Nakliye Çözümleri</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --cemiloglu-red: #c41e3a;
            --cemiloglu-red-dark: #a0182f;
            --cemiloglu-blue: #0d3b66;
            --cemiloglu-blue-light: #1a4d7a;
            --cemiloglu-navy: #0f172a;
            --cemiloglu-silver: #64748b;
            --cemiloglu-bg: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--cemiloglu-bg);
        }

        html {
            scroll-behavior: smooth;
        }

        .hero-overlay {
            background: linear-gradient(to right, rgba(15, 23, 42, 0.95) 0%, rgba(15, 23, 42, 0.7) 50%, rgba(15, 23, 42, 0.3) 100%);
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(13, 59, 102, 0.1);
        }

        .service-card {
            background: white;
            border: 1px solid rgba(13, 59, 102, 0.12);
            border-radius: 1rem;
            transition: border-color 0.3s ease;
        }

        .service-card:hover {
            border-color: var(--cemiloglu-blue);
        }

        .stats-section {
            background: var(--cemiloglu-navy);
        }

        .link-cemiloglu {
            color: var(--cemiloglu-blue) !important;
            transition: color 0.2s ease;
            text-decoration: none;
        }
        
        .link-cemiloglu:hover {
            color: var(--cemiloglu-red) !important;
        }

        .btn-cta-primary {
            background: var(--cemiloglu-red) !important;
            color: white !important;
            border: none;
            transition: all 0.2s ease;
        }
        
        .btn-cta-primary:hover {
            background: var(--cemiloglu-red-dark) !important;
            transform: translateY(-2px);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: rgba(13, 59, 102, 0.08);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--cemiloglu-blue);
        }

        .hero-badge {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dropdown-menu {
            border: 1px solid rgba(13, 59, 102, 0.1);
            border-radius: 0.5rem;
            padding: 0.5rem;
            margin-top: 0.5rem;
        }

        .dropdown-item {
            border-radius: 0.375rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: rgba(13, 59, 102, 0.08);
            color: var(--cemiloglu-blue) !important;
        }

        .nav-dropdown {
            position: relative;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        @keyframes bounce {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(-10px); }
        }

        .hero-image-placeholder {
            background: linear-gradient(135deg, #1a4d7a 0%, #0d3b66 100%);
        }

        .service-image-placeholder {
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="glass-header position-fixed w-100 top-0" style="z-index: 1050;">
        <div class="container-fluid" style="max-width: 1400px;">
            <div class="d-flex align-items-center justify-content-between py-4 px-3">
                <a href="/" class="text-decoration-none">
                    <img src="{{ asset('images/cemiloglu.svg') }}" alt="Cemiloğlu Lojistik" height="48" class="d-block" style="max-height: 48px; width: auto;" />
                </a>
                
                <nav class="d-none d-md-flex gap-4 align-items-center">
                    <a href="#" class="text-decoration-none fw-bold text-uppercase link-cemiloglu" style="font-size: 0.75rem; letter-spacing: 0.1em;">Ana Sayfa</a>
                    
                    <div class="dropdown">
                        <button class="btn btn-link text-decoration-none fw-bold text-uppercase link-cemiloglu dropdown-toggle p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.75rem; letter-spacing: 0.1em;">
                            Kurumsal
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#hakkimizda">Hakkımızda</a></li>
                            <li><a class="dropdown-item" href="#misyon-vizyon">Misyon ve Vizyon</a></li>
                            <li><a class="dropdown-item" href="#gizlilik">Gizlilik Politikası</a></li>
                        </ul>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-link text-decoration-none fw-bold text-uppercase link-cemiloglu dropdown-toggle p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.75rem; letter-spacing: 0.1em;">
                            Hizmet Alanları
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#tasimacilik">Taşımacılık</a></li>
                            <li><a class="dropdown-item" href="#depolama">Depolama</a></li>
                            <li><a class="dropdown-item" href="#danismanlik">Planlama ve Danışmanlık</a></li>
                        </ul>
                    </div>

                    <a href="#insan-kaynaklari" class="text-decoration-none fw-bold text-uppercase link-cemiloglu" style="font-size: 0.75rem; letter-spacing: 0.1em;">İnsan Kaynakları</a>
                    <a href="#iletisim" class="text-decoration-none fw-bold text-uppercase link-cemiloglu" style="font-size: 0.75rem; letter-spacing: 0.1em;">İletişim</a>
                </nav>

                <div class="d-flex align-items-center gap-3">
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn btn-cta-primary px-4 py-2 rounded fw-bold text-uppercase d-flex align-items-center gap-2" style="font-size: 0.75rem; letter-spacing: 0.08em;">
                            Müşteri Paneli
                            <span class="material-symbols-outlined" style="font-size: 1.125rem;">login</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="position-relative d-flex align-items-center" style="min-height: 100vh; overflow: hidden;">
        <!-- Background -->
        <div class="position-absolute top-0 start-0 w-100 h-100 hero-image-placeholder" style="z-index: 0;">
            <svg class="position-absolute w-100 h-100" style="opacity: 0.1;">
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)" />
            </svg>
        </div>
        <div class="position-absolute top-0 start-0 w-100 h-100 hero-overlay" style="z-index: 1;"></div>

        <!-- Content -->
        <div class="container-fluid position-relative" style="max-width: 1400px; z-index: 10; padding-top: 100px;">
            <div class="row align-items-center px-3">
                <div class="col-lg-8">
                    <!-- Badge -->
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill hero-badge mb-4">
                        <span class="d-inline-block bg-danger rounded-circle" style="width: 8px; height: 8px; animation: pulse 2s infinite;"></span>
                        <span class="text-white fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.2em;">Türkiye'nin Güvenilir Lojistik Ortağı</span>
                    </div>

                    <!-- Main Heading -->
                    <h1 class="display-1 fw-black text-white mb-4" style="line-height: 0.95; letter-spacing: -0.02em;">
                        YÜKÜNÜZÜ<br/>
                        <span class="text-white" style="opacity: 0.7;">GÜVENİLE</span><br/>
                        <span style="color: var(--cemiloglu-red);">TESLİM EDİN</span>
                    </h1>

                    <!-- Description -->
                    <p class="fs-4 text-white mb-5" style="max-width: 600px; opacity: 0.9; line-height: 1.6;">
                        Çimento, kömür ve endüstriyel malzeme taşımacılığında 25 yıllık tecrübe. Modern filomuz ve güçlü altyapımızla zamanında, güvenli teslimat garantisi.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="d-flex flex-column flex-sm-row gap-3 mb-5">
                        <a href="{{ route('login') }}" class="btn btn-cta-primary px-5 py-3 rounded fw-bold text-uppercase d-flex align-items-center justify-content-center gap-2" style="font-size: 0.875rem; letter-spacing: 0.08em;">
                            Hemen Başlayın
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </a>
                        <a href="#hizmetler" class="btn btn-outline-light px-5 py-3 rounded fw-bold text-uppercase" style="font-size: 0.875rem; letter-spacing: 0.08em; border-width: 2px;">
                            Hizmetlerimiz
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="position-absolute bottom-0 start-50 translate-middle-x pb-5" style="z-index: 10; animation: bounce 2s infinite;">
            <span class="material-symbols-outlined text-white fs-1" style="opacity: 0.5;">keyboard_double_arrow_down</span>
        </div>
    </section>

    <!-- Hizmetler Section -->
    <section class="py-5" id="hizmetler" style="background: #f8f9fa;">
        <div class="container-fluid" style="max-width: 1400px; padding: 80px 24px;">
            <!-- Section Header -->
            <div class="text-center mb-5">
                <span class="text-uppercase fw-bold d-block mb-2" style="color: var(--cemiloglu-red); font-size: 0.75rem; letter-spacing: 0.2em;">Operasyonel Mükemmeliyet</span>
                <h2 class="display-4 fw-black mb-3" style="color: var(--cemiloglu-navy);">Endüstriyel Lojistik Çözümleri</h2>
                <p class="fs-5 mx-auto" style="max-width: 700px; color: var(--cemiloglu-silver);">
                    Çimento, kömür ve ağır yük taşımacılığında uzman kadromuz ve modern filomuzla sektörün önündeyiz
                </p>
            </div>

            <!-- Services Grid -->
            <div class="row g-4">
                <!-- Çimento Taşımacılığı -->
                <div class="col-md-6 col-lg-4">
                    <div class="service-card p-4 h-100">
                        <div class="position-relative mb-4 service-image-placeholder d-flex align-items-center justify-content-center" style="height: 200px; border-radius: 0.75rem;">
                            <span class="material-symbols-outlined" style="font-size: 5rem; color: var(--cemiloglu-blue); opacity: 0.3;">local_shipping</span>
                        </div>
                        <div class="feature-icon mb-3">
                            <span class="material-symbols-outlined fs-1">water_drop</span>
                        </div>
                        <h3 class="h4 fw-bold mb-3" style="color: var(--cemiloglu-navy);">Dökme Çimento Lojistiği</h3>
                        <p class="mb-4" style="color: var(--cemiloglu-silver); line-height: 1.7;">
                            Silo bas araçlarımızla şantiyelerinize kesintisiz dökme çimento tedariği. Toz sızdırmazlık garantili, basınçlı boşaltım sistemli modern filomuz.
                        </p>
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <div>
                                <small class="text-muted d-block">Aylık Kapasite</small>
                                <strong class="fs-5" style="color: var(--cemiloglu-navy);">
                                    <span class="counter" data-target="50000">0</span>+ Ton
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kömür Taşımacılığı -->
                <div class="col-md-6 col-lg-4">
                    <div class="service-card p-4 h-100" style="border-top: 4px solid var(--cemiloglu-red);">
                        <div class="position-relative mb-4 d-flex align-items-center justify-content-center" style="height: 200px; border-radius: 0.75rem; background: var(--cemiloglu-navy);">
                            <span class="material-symbols-outlined text-white" style="font-size: 5rem; opacity: 0.3;">local_shipping</span>
                        </div>
                        <div class="feature-icon mb-3" style="background: var(--cemiloglu-navy); color: white;">
                            <span class="material-symbols-outlined fs-1">diamond</span>
                        </div>
                        <h3 class="h4 fw-bold mb-3" style="color: var(--cemiloglu-navy);">Kömür ve Maden Lojistiği</h3>
                        <p class="mb-4" style="color: var(--cemiloglu-silver); line-height: 1.7;">
                            Enerji santralleri ve sanayi tesisleri için yüksek tonajlı kömür sevkiyatı. Hardox damperli araç filomuzla hızlı ve güvenli taşıma.
                        </p>
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <div>
                                <small class="text-muted d-block">Filo Büyüklüğü</small>
                                <strong class="fs-5" style="color: var(--cemiloglu-navy);">
                                    <span class="counter" data-target="500">0</span>+ Araç
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ağır Yük Taşımacılığı -->
                <div class="col-md-6 col-lg-4">
                    <div class="service-card p-4 h-100">
                        <div class="position-relative mb-4 service-image-placeholder d-flex align-items-center justify-content-center" style="height: 200px; border-radius: 0.75rem;">
                            <span class="material-symbols-outlined" style="font-size: 5rem; color: var(--cemiloglu-red); opacity: 0.3;">precision_manufacturing</span>
                        </div>
                        <div class="feature-icon mb-3" style="background: rgba(196, 30, 58, 0.1); color: var(--cemiloglu-red);">
                            <span class="material-symbols-outlined fs-1">foundation</span>
                        </div>
                        <h3 class="h4 fw-bold mb-3" style="color: var(--cemiloglu-navy);">Ağır Yük ve Proje Taşımacılığı</h3>
                        <p class="mb-4" style="color: var(--cemiloglu-silver); line-height: 1.7;">
                            İnşaat ve yapı malzemeleri, demir-çelik ürünlerinin proje sahalarına tam zamanında (JIT) teslimatı. Özel ekipman ve uzman kadro.
                        </p>
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <div>
                                <small class="text-muted d-block">Teslimat Başarı</small>
                                <strong class="fs-5" style="color: var(--cemiloglu-navy);">
                                    %<span class="counter" data-target="99.8" data-decimals="1">0</span>
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section py-5" id="filo">
        <div class="container-fluid" style="max-width: 1400px; padding: 80px 24px;">
            <div class="row g-5">
                <div class="col-6 col-lg-3 text-center">
                    <div class="display-2 fw-black text-white mb-2">
                        <span class="counter" data-target="500">0</span><span style="color: var(--cemiloglu-red);">+</span>
                    </div>
                    <p class="text-uppercase fw-bold text-white-50" style="font-size: 0.75rem; letter-spacing: 0.15em;">Mercedes-Benz Filo</p>
                </div>
                <div class="col-6 col-lg-3 text-center">
                    <div class="display-2 fw-black text-white mb-2">
                        <span class="counter" data-target="3.5" data-decimals="1">0</span><span style="color: var(--cemiloglu-red);">M</span>
                    </div>
                    <p class="text-uppercase fw-bold text-white-50" style="font-size: 0.75rem; letter-spacing: 0.15em;">Ton Yıllık Taşıma</p>
                </div>
                <div class="col-6 col-lg-3 text-center">
                    <div class="display-2 fw-black text-white mb-2">
                        <span class="counter" data-target="81">0</span>
                    </div>
                    <p class="text-uppercase fw-bold text-white-50" style="font-size: 0.75rem; letter-spacing: 0.15em;">İlde Hizmet</p>
                </div>
                <div class="col-6 col-lg-3 text-center">
                    <div class="display-2 fw-black text-white mb-2">
                        <span class="counter" data-target="25">0</span><span style="color: var(--cemiloglu-red);">+</span>
                    </div>
                    <p class="text-uppercase fw-bold text-white-50" style="font-size: 0.75rem; letter-spacing: 0.15em;">Yıl Tecrübe</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Hakkımızda -->
    <section class="py-5" id="hakkimizda" style="background: white;">
        <div class="container-fluid" style="max-width: 1400px; padding: 80px 24px;">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="position-relative service-image-placeholder d-flex align-items-center justify-content-center" style="height: 400px; border-radius: 1.5rem;">
                        <span class="material-symbols-outlined" style="font-size: 8rem; color: var(--cemiloglu-blue); opacity: 0.2;">local_shipping</span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="d-inline-block px-3 py-1 rounded mb-3" style="background: rgba(13, 59, 102, 0.1);">
                        <span class="text-uppercase fw-bold" style="color: var(--cemiloglu-blue); font-size: 0.65rem; letter-spacing: 0.2em;">Cemiloğlu Şirketler Grubu</span>
                    </div>
                    <h2 class="display-4 fw-black mb-4" style="color: var(--cemiloglu-navy); line-height: 1.1;">
                        Türkiye'nin Güvenilir<br/>Lojistik Ortağı
                    </h2>
                    <p class="fs-5 mb-4" style="color: var(--cemiloglu-silver); line-height: 1.7;">
                        1999 yılından bu yana çimento, kömür ve endüstriyel malzeme taşımacılığında sektörün öncü kuruluşuyuz. Mercedes-Benz Actros filomuz ve deneyimli kadromuzla Türkiye'nin 81 iline kesintisiz hizmet sunuyoruz.
                    </p>
                    <p class="fs-6 mb-4" style="color: var(--cemiloglu-silver); line-height: 1.7;">
                        Modern teknoloji altyapımız, uydu takip sistemlerimiz ve kalite standartlarımızla müşterilerimize zamanında teslimat, sıfır hasar ve operasyonel mükemmeliyet garantisi veriyoruz.
                    </p>
                    <div class="row g-4">
                        <div class="col-6">
                            <div class="p-3 rounded" style="background: rgba(13, 59, 102, 0.05); border-left: 3px solid var(--cemiloglu-blue);">
                                <div class="fw-bold fs-4" style="color: var(--cemiloglu-navy);">ISO 9001:2015</div>
                                <small class="text-muted">Kalite Yönetimi</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded" style="background: rgba(196, 30, 58, 0.05); border-left: 3px solid var(--cemiloglu-red);">
                                <div class="fw-bold fs-4" style="color: var(--cemiloglu-navy);">ADR Belgeli</div>
                                <small class="text-muted">Tehlikeli Madde</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Misyon & Vizyon -->
    <section class="py-5" id="misyon-vizyon" style="background: #f8f9fa;">
        <div class="container-fluid" style="max-width: 1400px; padding: 80px 24px;">
            <div class="text-center mb-5">
                <span class="text-uppercase fw-bold d-block mb-2" style="color: var(--cemiloglu-red); font-size: 0.75rem; letter-spacing: 0.2em;">Değerlerimiz</span>
                <h2 class="display-4 fw-black mb-3" style="color: var(--cemiloglu-navy);">Misyon ve Vizyon</h2>
            </div>
            
            <div class="row g-4 mb-5">
                <div class="col-lg-6">
                    <div class="service-card p-5 h-100">
                        <div class="d-flex align-items-center mb-4">
                            <div class="d-flex align-items-center justify-content-center rounded-circle me-3" style="width: 56px; height: 56px; background: rgba(13, 59, 102, 0.1);">
                                <span class="material-symbols-outlined fs-1" style="color: var(--cemiloglu-blue);">flag</span>
                            </div>
                            <h3 class="h3 fw-bold mb-0" style="color: var(--cemiloglu-navy);">Misyonumuz</h3>
                        </div>
                        <p class="fs-5 mb-0" style="color: var(--cemiloglu-silver); line-height: 1.7;">
                            Çimento, kömür ve endüstriyel malzeme lojistiğinde <strong>güvenilir, hızlı ve kaliteli hizmet</strong> sunarak müşterilerimizin tedarik zincirini güçlendirmek. Modern teknoloji ve deneyimli kadromuzla sektörde <strong>operasyonel mükemmeliyet standardı</strong> oluşturmak.
                        </p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="service-card p-5 h-100" style="border-top: 4px solid var(--cemiloglu-red);">
                        <div class="d-flex align-items-center mb-4">
                            <div class="d-flex align-items-center justify-content-center rounded-circle me-3" style="width: 56px; height: 56px; background: rgba(196, 30, 58, 0.1);">
                                <span class="material-symbols-outlined fs-1" style="color: var(--cemiloglu-red);">visibility</span>
                            </div>
                            <h3 class="h3 fw-bold mb-0" style="color: var(--cemiloglu-navy);">Vizyonumuz</h3>
                        </div>
                        <p class="fs-5 mb-0" style="color: var(--cemiloglu-silver); line-height: 1.7;">
                            Türkiye'nin <strong>en güvenilir ve teknolojik altyapıya sahip</strong> lojistik şirketi olmak. Dijital dönüşüm ve sürdürülebilirlik odaklı yaklaşımımızla sektörde <strong>öncü ve örnek</strong> bir kuruluş olarak global pazarda da söz sahibi olmak.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Değerlerimiz -->
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="text-center p-4">
                        <div class="d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px; background: rgba(13, 59, 102, 0.1); border-radius: 1rem;">
                            <span class="material-symbols-outlined fs-1" style="color: var(--cemiloglu-blue);">verified</span>
                        </div>
                        <h4 class="h6 fw-bold mb-2" style="color: var(--cemiloglu-navy);">Güvenilirlik</h4>
                        <p class="small text-muted mb-0">Sözleşmeli teslimat süreleri ve %99.8 başarı oranı</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="text-center p-4">
                        <div class="d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px; background: rgba(196, 30, 58, 0.1); border-radius: 1rem;">
                            <span class="material-symbols-outlined fs-1" style="color: var(--cemiloglu-red);">speed</span>
                        </div>
                        <h4 class="h6 fw-bold mb-2" style="color: var(--cemiloglu-navy);">Operasyonel Hız</h4>
                        <p class="small text-muted mb-0">Gerçek zamanlı takip ve hızlı müdahale ekipleri</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="text-center p-4">
                        <div class="d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px; background: rgba(13, 59, 102, 0.1); border-radius: 1rem;">
                            <span class="material-symbols-outlined fs-1" style="color: var(--cemiloglu-blue);">eco</span>
                        </div>
                        <h4 class="h6 fw-bold mb-2" style="color: var(--cemiloglu-navy);">Sürdürülebilirlik</h4>
                        <p class="small text-muted mb-0">Euro 6 motorlar ve çevre dostu operasyonlar</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="text-center p-4">
                        <div class="d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px; background: rgba(196, 30, 58, 0.1); border-radius: 1rem;">
                            <span class="material-symbols-outlined fs-1" style="color: var(--cemiloglu-red);">workspace_premium</span>
                        </div>
                        <h4 class="h6 fw-bold mb-2" style="color: var(--cemiloglu-navy);">Kalite Odaklılık</h4>
                        <p class="small text-muted mb-0">ISO sertifikaları ve sürekli iyileştirme anlayışı</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Taşımacılık Detay -->
    <section class="py-5" id="tasimacilik" style="background: white;">
        <div class="container-fluid" style="max-width: 1400px; padding: 80px 24px;">
            <div class="text-center mb-5">
                <span class="text-uppercase fw-bold d-block mb-2" style="color: var(--cemiloglu-red); font-size: 0.75rem; letter-spacing: 0.2em;">Uzmanlık Alanlarımız</span>
                <h2 class="display-4 fw-black mb-3" style="color: var(--cemiloglu-navy);">Taşımacılık Hizmetleri</h2>
            </div>

            <div class="row g-4">
                <!-- Silobas Çimento -->
                <div class="col-lg-4">
                    <div class="service-card p-4 h-100">
                        <div class="feature-icon mb-3">
                            <span class="material-symbols-outlined fs-1">water_drop</span>
                        </div>
                        <h3 class="h5 fw-bold mb-3" style="color: var(--cemiloglu-navy);">Silobas Dökme Çimento</h3>
                        <ul class="list-unstyled mb-0" style="color: var(--cemiloglu-silver);">
                            <li class="mb-2">• CEM I, CEM II, CEM III, CEM IV, CEM V</li>
                            <li class="mb-2">• Beyaz çimento ve alüminat çimentosu</li>
                            <li class="mb-2">• Öğütülmüş cüruf ve uçucu kül</li>
                            <li class="mb-2">• Toz sızdırmazlık garantili taşıma</li>
                            <li class="mb-2">• Basınçlı boşaltım sistemleri</li>
                        </ul>
                    </div>
                </div>

                <!-- Damperli Kömür -->
                <div class="col-lg-4">
                    <div class="service-card p-4 h-100">
                        <div class="feature-icon mb-3" style="background: var(--cemiloglu-navy); color: white;">
                            <span class="material-symbols-outlined fs-1">diamond</span>
                        </div>
                        <h3 class="h5 fw-bold mb-3" style="color: var(--cemiloglu-navy);">Damperli Kömür Taşımacılığı</h3>
                        <ul class="list-unstyled mb-0" style="color: var(--cemiloglu-silver);">
                            <li class="mb-2">• Termik santral tedariği</li>
                            <li class="mb-2">• Sanayi tesisleri için kömür sevkiyatı</li>
                            <li class="mb-2">• Hardox damper teknolojisi</li>
                            <li class="mb-2">• Yüksek tonaj kapasiteli araçlar</li>
                            <li class="mb-2">• Hızlı boşaltım sistemleri</li>
                        </ul>
                    </div>
                </div>

                <!-- Proje Lojistiği -->
                <div class="col-lg-4">
                    <div class="service-card p-4 h-100">
                        <div class="feature-icon mb-3" style="background: rgba(196, 30, 58, 0.1); color: var(--cemiloglu-red);">
                            <span class="material-symbols-outlined fs-1">precision_manufacturing</span>
                        </div>
                        <h3 class="h5 fw-bold mb-3" style="color: var(--cemiloglu-navy);">Ağır Yük ve Proje Lojistiği</h3>
                        <ul class="list-unstyled mb-0" style="color: var(--cemiloglu-silver);">
                            <li class="mb-2">• Demir-çelik ve yapı malzemeleri</li>
                            <li class="mb-2">• Özel ekipman ve dorseler</li>
                            <li class="mb-2">• Proje sahaları için JIT teslimat</li>
                            <li class="mb-2">• Rota planlama ve analiz</li>
                            <li class="mb-2">• Profesyonel montaj ekipleri</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Depolama -->
    <section class="py-5" id="depolama" style="background: #f8f9fa;">
        <div class="container-fluid" style="max-width: 1400px; padding: 80px 24px;">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 order-lg-2">
                    <div class="position-relative service-image-placeholder d-flex align-items-center justify-content-center" style="height: 400px; border-radius: 1.5rem;">
                        <span class="material-symbols-outlined" style="font-size: 8rem; color: var(--cemiloglu-blue); opacity: 0.2;">warehouse</span>
                    </div>
                </div>
                <div class="col-lg-6 order-lg-1">
                    <div class="d-inline-block px-3 py-1 rounded mb-3" style="background: rgba(13, 59, 102, 0.1);">
                        <span class="text-uppercase fw-bold" style="color: var(--cemiloglu-blue); font-size: 0.65rem; letter-spacing: 0.2em;">Akıllı Depolama Sistemleri</span>
                    </div>
                    <h2 class="display-4 fw-black mb-4" style="color: var(--cemiloglu-navy); line-height: 1.1;">
                        Güvenli ve Modern<br/>Depolama Tesisleri
                    </h2>
                    <p class="fs-5 mb-4" style="color: var(--cemiloglu-silver); line-height: 1.7;">
                        Stratejik noktalarda konumlanmış depo ağımızla yüklerinizi güvenle saklıyor, stok yönetimi ve dağıtım süreçlerinizi optimize ediyoruz.
                    </p>
                    <ul class="list-unstyled mb-4">
                        <li class="d-flex align-items-center mb-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle me-3" style="width: 32px; height: 32px; background: var(--cemiloglu-blue); color: white;">
                                <span class="material-symbols-outlined" style="font-size: 1.25rem;">check</span>
                            </div>
                            <span class="fw-semibold" style="color: var(--cemiloglu-navy);">7/24 Güvenlik ve Kamera Sistemi</span>
                        </li>
                        <li class="d-flex align-items-center mb-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle me-3" style="width: 32px; height: 32px; background: var(--cemiloglu-blue); color: white;">
                                <span class="material-symbols-outlined" style="font-size: 1.25rem;">check</span>
                            </div>
                            <span class="fw-semibold" style="color: var(--cemiloglu-navy);">WMS (Warehouse Management System)</span>
                        </li>
                        <li class="d-flex align-items-center">
                            <div class="d-flex align-items-center justify-content-center rounded-circle me-3" style="width: 32px; height: 32px; background: var(--cemiloglu-blue); color: white;">
                                <span class="material-symbols-outlined" style="font-size: 1.25rem;">check</span>
                            </div>
                            <span class="fw-semibold" style="color: var(--cemiloglu-navy);">Cross-Docking ve Konsolidasyon</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Planlama ve Danışmanlık -->
    <section class="py-5" id="danismanlik" style="background: white;">
        <div class="container-fluid" style="max-width: 1400px; padding: 80px 24px;">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-black mb-4" style="color: var(--cemiloglu-navy);">
                        Lojistik Planlama ve<br/>Danışmanlık Hizmetleri
                    </h2>
                    <p class="fs-5 mb-4" style="color: var(--cemiloglu-silver); line-height: 1.7;">
                        25 yıllık sektör deneyimimizle tedarik zinciri optimizasyonu, rota planlama ve maliyet analizi konularında profesyonel danışmanlık hizmeti sunuyoruz.
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex gap-3 p-3 rounded" style="background: rgba(13, 59, 102, 0.05);">
                                <span class="material-symbols-outlined fs-3" style="color: var(--cemiloglu-blue);">route</span>
                                <div>
                                    <h5 class="fw-bold mb-1" style="color: var(--cemiloglu-navy);">Rota Optimizasyonu</h5>
                                    <p class="small text-muted mb-0">GPS verisi ve AI destekli rota planlama ile yakıt tasarrufu</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-3 p-3 rounded" style="background: rgba(196, 30, 58, 0.05);">
                                <span class="material-symbols-outlined fs-3" style="color: var(--cemiloglu-red);">account_tree</span>
                                <div>
                                    <h5 class="fw-bold mb-1" style="color: var(--cemiloglu-navy);">Tedarik Zinciri Analizi</h5>
                                    <p class="small text-muted mb-0">Operasyonel verimliliği artıran detaylı süreç analizi</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-3 p-3 rounded" style="background: rgba(13, 59, 102, 0.05);">
                                <span class="material-symbols-outlined fs-3" style="color: var(--cemiloglu-blue);">insights</span>
                                <div>
                                    <h5 class="fw-bold mb-1" style="color: var(--cemiloglu-navy);">Maliyet Optimizasyonu</h5>
                                    <p class="small text-muted mb-0">Taşıma maliyetlerini düşüren stratejik çözümler</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- İnsan Kaynakları -->
    <section class="py-5" id="insan-kaynaklari" style="background: var(--cemiloglu-navy);">
        <div class="container-fluid" style="max-width: 1400px; padding: 80px 24px;">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <h2 class="display-4 fw-black text-white mb-4">
                        Ekibimize<br/>Katılın
                    </h2>
                    <p class="fs-5 text-white mb-4" style="opacity: 0.8; line-height: 1.7;">
                        Cemiloğlu Lojistik ailesi olarak, sektörün en iyi yeteneklerini aramaktayız. Profesyonel gelişim fırsatları, rekabetçi maaş ve sosyal haklar ile kariyer hedeflerinize ulaşın.
                    </p>
                    <div class="row g-4 mb-4">
                        <div class="col-6">
                            <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.1); border-left: 3px solid var(--cemiloglu-red);">
                                <div class="fw-bold fs-3 text-white"><span class="counter" data-target="1200">0</span>+</div>
                                <small class="text-white-50">Çalışan Sayısı</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.1); border-left: 3px solid var(--cemiloglu-red);">
                                <div class="fw-bold fs-3 text-white"><span class="counter" data-target="850">0</span>+</div>
                                <small class="text-white-50">Profesyonel Sürücü</small>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('login') }}" class="btn btn-light px-5 py-3 rounded fw-bold text-uppercase" style="color: var(--cemiloglu-navy); font-size: 0.875rem; letter-spacing: 0.08em;">
                        Açık Pozisyonlar
                    </a>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="p-4 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <span class="material-symbols-outlined" style="color: var(--cemiloglu-red); font-size: 2rem;">school</span>
                                    <h4 class="h5 fw-bold text-white mb-0">Eğitim ve Gelişim</h4>
                                </div>
                                <p class="text-white-50 mb-0">Sürekli eğitim programları ve sertifikasyon desteği</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-4 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <span class="material-symbols-outlined" style="color: var(--cemiloglu-red); font-size: 2rem;">health_and_safety</span>
                                    <h4 class="h5 fw-bold text-white mb-0">Sağlık ve Güvenlik</h4>
                                </div>
                                <p class="text-white-50 mb-0">Kapsamlı sağlık sigortası ve iş güvenliği önlemleri</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-4 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <span class="material-symbols-outlined" style="color: var(--cemiloglu-red); font-size: 2rem;">trending_up</span>
                                    <h4 class="h5 fw-bold text-white mb-0">Kariyer Fırsatları</h4>
                                </div>
                                <p class="text-white-50 mb-0">Terfi programları ve yatay geçiş imkanları</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5" style="background: var(--cemiloglu-red);">
        <div class="container-fluid" style="max-width: 1400px; padding: 60px 24px;">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <h2 class="display-5 fw-black text-white mb-3">İşletmenizi Geleceğe Taşımaya Hazır Mısınız?</h2>
                    <p class="fs-5 text-white mb-0" style="opacity: 0.9;">
                        Operasyonel maliyetlerinizi düşürmek ve lojistik süreçlerinizi profesyonel ellere teslim etmek için uzmanlarımızla görüşün.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-column flex-sm-row flex-lg-column gap-3">
                        <a href="{{ route('login') }}" class="btn btn-light px-5 py-3 rounded fw-bold text-uppercase" style="color: var(--cemiloglu-navy); font-size: 0.875rem; letter-spacing: 0.08em;">
                            Sisteme Giriş
                        </a>
                        <a href="#iletisim" class="btn btn-outline-light px-5 py-3 rounded fw-bold text-uppercase" style="font-size: 0.875rem; letter-spacing: 0.08em; border-width: 2px;">
                            İletişime Geç
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-5" id="iletisim" style="background: var(--cemiloglu-navy);">
        <div class="container-fluid" style="max-width: 1400px; padding: 80px 24px 40px;">
            <div class="row g-5 mb-5">
                <!-- Logo & About -->
                <div class="col-lg-4">
                    <img src="{{ asset('images/cemiloglu.svg') }}" alt="Cemiloğlu Lojistik" height="40" class="mb-4 d-block" style="filter: brightness(0) invert(1);">
                    <p class="text-white-50 mb-4" style="line-height: 1.7;">
                        Cemiloğlu Lojistik, çimento ve kömür taşımacılığında Türkiye'nin lider çözüm ortağıdır. Mercedes-Benz Actros filosuyla güvenli ve zamanında teslimat garantisi.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="d-flex align-items-center justify-content-center rounded-circle border text-white-50" style="width: 40px; height: 40px; border-color: rgba(255, 255, 255, 0.2) !important; transition: all 0.2s;">
                            <span class="material-symbols-outlined">share</span>
                        </a>
                        <a href="#" class="d-flex align-items-center justify-content-center rounded-circle border text-white-50" style="width: 40px; height: 40px; border-color: rgba(255, 255, 255, 0.2) !important; transition: all 0.2s;">
                            <span class="material-symbols-outlined">groups</span>
                        </a>
                        <a href="#" class="d-flex align-items-center justify-content-center rounded-circle border text-white-50" style="width: 40px; height: 40px; border-color: rgba(255, 255, 255, 0.2) !important; transition: all 0.2s;">
                            <span class="material-symbols-outlined">language</span>
                        </a>
                    </div>
                </div>

                <!-- Kurumsal -->
                <div class="col-lg-2 col-6">
                    <h4 class="text-white fw-bold mb-4 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.15em;">Kurumsal</h4>
                    <ul class="list-unstyled">
                        <li class="mb-3"><a href="#hakkimizda" class="text-white-50 text-decoration-none">Hakkımızda</a></li>
                        <li class="mb-3"><a href="#misyon-vizyon" class="text-white-50 text-decoration-none">Misyon ve Vizyon</a></li>
                        <li class="mb-3"><a href="#gizlilik" class="text-white-50 text-decoration-none">Gizlilik Politikası</a></li>
                    </ul>
                </div>

                <!-- Hizmetler -->
                <div class="col-lg-2 col-6">
                    <h4 class="text-white fw-bold mb-4 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.15em;">Hizmet Alanları</h4>
                    <ul class="list-unstyled">
                        <li class="mb-3"><a href="#tasimacilik" class="text-white-50 text-decoration-none">Taşımacılık</a></li>
                        <li class="mb-3"><a href="#depolama" class="text-white-50 text-decoration-none">Depolama</a></li>
                        <li class="mb-3"><a href="#danismanlik" class="text-white-50 text-decoration-none">Planlama ve Danışmanlık</a></li>
                    </ul>
                </div>

                <!-- İletişim -->
                <div class="col-lg-4">
                    <h4 class="text-white fw-bold mb-4 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.15em;">İletişim Bilgileri</h4>
                    <ul class="list-unstyled">
                        <li class="d-flex mb-4">
                            <span class="material-symbols-outlined me-3" style="color: var(--cemiloglu-red);">location_on</span>
                            <span class="text-white-50">Genel Merkez: Lojistik Vadisi No:123/A<br>34300, İstanbul, Türkiye</span>
                        </li>
                        <li class="d-flex mb-4">
                            <span class="material-symbols-outlined me-3" style="color: var(--cemiloglu-red);">call</span>
                            <span class="text-white-50 fw-semibold">+90 (212) 444 0 444</span>
                        </li>
                        <li class="d-flex">
                            <span class="material-symbols-outlined me-3" style="color: var(--cemiloglu-red);">mail</span>
                            <span class="text-white-50">info@cemiloglu.com.tr</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="pt-4 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-3" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                <p class="text-white-50 mb-0 small">© {{ date('Y') }} Cemiloğlu Lojistik - Tüm hakları saklıdır.</p>
                <div class="d-flex gap-4">
                    <a href="#gizlilik" class="text-white-50 text-decoration-none small">KVKK</a>
                    <a href="#" class="text-white-50 text-decoration-none small">Gizlilik Politikası</a>
                    <a href="#" class="text-white-50 text-decoration-none small">Kullanım Koşulları</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Counter Animation Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.counter');
            const options = {
                root: null,
                rootMargin: '0px',
                threshold: 0.3
            };

            const animateCounter = (counter) => {
                const target = parseFloat(counter.getAttribute('data-target'));
                const decimals = parseInt(counter.getAttribute('data-decimals')) || 0;
                const duration = 2000;
                const increment = target / (duration / 16);
                let current = 0;

                const updateCounter = () => {
                    current += increment;
                    if (current < target) {
                        counter.textContent = decimals > 0 
                            ? current.toFixed(decimals) 
                            : Math.floor(current).toLocaleString('tr-TR');
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = decimals > 0 
                            ? target.toFixed(decimals) 
                            : Math.floor(target).toLocaleString('tr-TR');
                    }
                };

                updateCounter();
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                        entry.target.classList.add('animated');
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, options);

            counters.forEach(counter => {
                observer.observe(counter);
            });
        });
    </script>
</body>
</html>
