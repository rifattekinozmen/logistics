<!DOCTYPE html>
<html class="light" lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giriş Yap - Cemiloğlu Şirketler Grubu</title>

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
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        .login-bg-video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        .login-bg-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.55) 0%, rgba(13, 59, 102, 0.65) 100%);
            z-index: 1;
        }

        .login-content {
            position: relative;
            z-index: 3;
        }

        .login-transition-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.9));
            z-index: 2;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.08s ease-in-out;
        }

        .login-transition-overlay.active {
            opacity: 1;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(13, 59, 102, 0.12);
            box-shadow: 0 4px 24px -4px rgba(13, 59, 102, 0.08);
        }

        .login-card .input-field {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(13, 59, 102, 0.2) !important;
            background-color: rgb(232, 240, 254) !important;
            border-radius: 16px;
            outline: none;
        }
        .login-card .input-field:focus {
            border-color: var(--cemiloglu-blue) !important;
            box-shadow: 0 0 0 3px rgba(13, 59, 102, 0.15) !important;
        }
        .login-card .input-field.error {
            border-color: rgba(196, 30, 58, 0.5) !important;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--cemiloglu-red), var(--cemiloglu-red-hover));
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px -2px rgba(196, 30, 58, 0.35);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -4px rgba(196, 30, 58, 0.4) !important;
            background: linear-gradient(135deg, var(--cemiloglu-red-hover), #8a1328) !important;
        }

        .logo-container {
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-container img {
            max-height: 72px;
            width: auto;
            object-fit: contain;
        }

        .status-dot {
            position: absolute;
            top: 0;
            right: 0;
            width: 12px;
            height: 12px;
            background: rgba(34, 197, 94, 0.9);
            border: 2px solid white;
            border-radius: 50%;
            transform: translate(4px, -4px);
        }

        .link-cemiloglu {
            color: var(--cemiloglu-blue) !important;
        }
        .link-cemiloglu:hover {
            color: var(--cemiloglu-red) !important;
        }

        .btn-portal-active {
            background: var(--cemiloglu-blue) !important;
            border-color: var(--cemiloglu-blue) !important;
            color: white !important;
        }
        .btn-portal-outline {
            border-color: rgba(13, 59, 102, 0.4) !important;
            color: var(--cemiloglu-blue) !important;
        }
        .btn-portal-outline:hover {
            background: rgba(13, 59, 102, 0.08) !important;
            border-color: var(--cemiloglu-blue) !important;
        }
    </style>
</head>
<body class="min-vh-100 d-flex align-items-center justify-content-center p-4">
    @php
        $loginBackgroundVideos = [
            'images/login-bg1.mp4',
            'images/login-bg2.mp4',
            'images/login-bg3.mp4',
        ];

        // Sayfa açılışında rastgele bir video ile başla
        $bgVideo = $loginBackgroundVideos[array_rand($loginBackgroundVideos)];
    @endphp

    <video id="loginBgVideo" class="login-bg-video" autoplay muted playsinline>
        <source src="{{ asset($bgVideo) }}" type="video/mp4">
    </video>
    <div class="login-bg-overlay" aria-hidden="true"></div>
    <div class="login-transition-overlay" aria-hidden="true"></div>
    <div class="login-content w-100" style="max-width: 420px;">
        <div class="login-card rounded-3xl shadow-lg p-5">
            <!-- Logo & Header -->
            <div class="text-center mb-5">
                <div class="logo-container position-relative mx-auto mb-4 d-flex justify-content-center">
                    <img src="{{ asset('images/cemiloglu.svg') }}" alt="Cemiloğlu Şirketler Grubu" width="180" height="60" loading="eager" decoding="async" />
                    <div class="status-dot"></div>
                </div>
                @if(request('portal') === 'customer')
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                        <span class="material-symbols-outlined" style="font-size: 2rem; color: var(--cemiloglu-blue);">store</span>
                        <h1 class="h3 fw-bold mb-0" style="color: var(--cemiloglu-blue);">Müşteri Portalı</h1>
                    </div>
                    <p class="text-secondary mb-0 text-center" style="color: var(--cemiloglu-silver);">Siparişlerinizi takip edin ve yeni sipariş oluşturun</p>
                @else
                    <h1 class="h3 fw-bold mb-2" style="color: var(--cemiloglu-blue);">Giriş Yap</h1>
                    <p class="text-secondary mb-0" style="color: var(--cemiloglu-silver);">Lojistik hesabınıza giriş yapın</p>
                @endif

                <!-- Portal Seçimi -->
                <div class="mt-4 d-flex gap-2 justify-content-center">
                    <a href="{{ route('login') }}" class="btn btn-sm rounded-pill px-3 py-2 {{ !request()->has('portal') || request('portal') !== 'customer' ? 'btn-portal-active' : 'btn-portal-outline btn-outline-primary' }}" style="font-size: 0.875rem;">
                        <span class="material-symbols-outlined align-middle" style="font-size: 0.875rem;">admin_panel_settings</span>
                        Admin Panel
                    </a>
                    <a href="{{ route('login', ['portal' => 'customer']) }}" class="btn btn-sm rounded-pill px-3 py-2 {{ request('portal') === 'customer' ? 'btn-portal-active' : 'btn-portal-outline btn-outline-primary' }}" style="font-size: 0.875rem;">
                        <span class="material-symbols-outlined align-middle" style="font-size: 0.875rem;">store</span>
                        Müşteri Portalı
                    </a>
                </div>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf
                @if(request('portal') === 'customer')
                    <input type="hidden" name="portal" value="customer">
                @else
                    <input type="hidden" name="portal" value="admin">
                @endif

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="form-label fw-semibold mb-2" style="color: var(--cemiloglu-blue);">E-posta Adresi</label>
                    <div class="position-relative">
                        <div class="position-absolute top-50 start-0 translate-middle-y ps-3 d-flex align-items-center" style="pointer-events: none;">
                            <span class="material-symbols-outlined" style="font-size: 1.25rem; color: var(--cemiloglu-silver);">email</span>
                        </div>
                        <input
                            type="email"
                            class="form-control input-field ps-5 py-3 @error('email') error @enderror"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            placeholder="ornek@email.com"
                            style="height: 58px; padding-left: 48px; border-radius: 16px;"
                        >
                    </div>
                    @error('email')
                        <div class="mt-2">
                            <p class="small mb-0" style="color: var(--cemiloglu-red);">
                                <span class="material-symbols-outlined align-middle" style="font-size: 0.875rem; vertical-align: middle;">error</span>
                                {{ $message }}
                            </p>
                        </div>
                    @enderror
                    @if(session('error'))
                        <div class="mt-2">
                            <p class="small mb-0" style="color: var(--cemiloglu-red);">
                                <span class="material-symbols-outlined align-middle" style="font-size: 0.875rem; vertical-align: middle;">error</span>
                                {{ session('error') }}
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold mb-2" style="color: var(--cemiloglu-blue);">Şifre</label>
                    <div class="position-relative">
                        <div class="position-absolute top-50 start-0 translate-middle-y ps-3 d-flex align-items-center" style="pointer-events: none;">
                            <span class="material-symbols-outlined" style="font-size: 1.25rem; color: var(--cemiloglu-silver);">lock</span>
                        </div>
                        <input
                            type="password"
                            class="form-control input-field ps-5 py-3 @error('password') error @enderror"
                            id="password"
                            name="password"
                            required
                            placeholder="••••••••"
                            style="height: 58px; padding-left: 48px; border-radius: 16px;"
                        >
                    </div>
                    @error('password')
                        <div class="mt-2">
                            <p class="small mb-0" style="color: var(--cemiloglu-red);">
                                <span class="material-symbols-outlined align-middle" style="font-size: 0.875rem; vertical-align: middle;">error</span>
                                {{ $message }}
                            </p>
                        </div>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center">
                        <input
                            class="form-check-input me-2"
                            type="checkbox"
                            name="remember"
                            id="remember"
                            style="width: 18px; height: 18px; border-color: rgba(13, 59, 102, 0.4); cursor: pointer;"
                        >
                        <label class="form-check-label small" for="remember" style="color: var(--cemiloglu-silver); cursor: pointer;">
                            Beni hatırla
                        </label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small fw-semibold text-decoration-none transition-all link-cemiloglu">
                            Şifremi Unuttum
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-login text-white rounded-2xl py-3 fw-bold w-100 mb-4 d-flex align-items-center justify-content-center gap-2">
                    <span class="material-symbols-outlined">login</span>
                    Giriş Yap
                </button>

                <!-- Back to Home -->
                <div class="text-center">
                    <a href="{{ route('welcome') }}" class="d-inline-flex align-items-center gap-2 small fw-semibold text-decoration-none transition-all link-cemiloglu">
                        <span class="material-symbols-outlined">arrow_back</span>
                        Ana Sayfaya Dön
                    </a>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-4">
            <p class="small mb-0" style="color: var(--cemiloglu-silver);">
                © {{ date('Y') }} Cemiloğlu Şirketler Grubu. Tüm hakları saklıdır.
            </p>
        </div>
    </div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const videoElement = document.getElementById('loginBgVideo');
        const transitionOverlay = document.querySelector('.login-transition-overlay');

        if (!videoElement) {
            return;
        }

        const videos = [
            @foreach($loginBackgroundVideos as $video)
                "{{ asset($video) }}",
            @endforeach
        ];

        // Mevcut kaynağın index'ini bul
        let currentIndex = videos.findIndex(function (src) {
            return videoElement.currentSrc.includes(src);
        });

        if (currentIndex === -1) {
            currentIndex = 0;
        }

        videoElement.addEventListener('ended', function () {
            if (!videos.length) {
                return;
            }

            // Önce hızlı kararma efekti
            if (transitionOverlay) {
                transitionOverlay.classList.add('active');
            }

            // Biraz bekleyip videoyu değiştir, sonra tekrar aç
            setTimeout(function () {
                currentIndex = (currentIndex + 1) % videos.length;
                const nextSrc = videos[currentIndex];

                const source = videoElement.querySelector('source');
                if (source) {
                    source.src = nextSrc;
                } else {
                    videoElement.src = nextSrc;
                }

                videoElement.load();
                videoElement.play().catch(function () {
                    // Otomatik oynatma tarayıcı tarafından engellenirse sessizce geç
                });

                if (transitionOverlay) {
                    setTimeout(function () {
                        transitionOverlay.classList.remove('active');
                    }, 80);
                }
            }, 50);
        });
    });
</script>
</body>
</html>
