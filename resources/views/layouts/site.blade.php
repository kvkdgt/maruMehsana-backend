<!DOCTYPE html>
<html lang="{{ $lang ?? 'gu' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $lang      = $lang ?? 'gu';
        $siteTitle = trim($__env->yieldContent('title')) ?: __('home.meta_title');
        $siteDesc  = trim($__env->yieldContent('description')) ?: __('home.meta_desc');
        $siteImage = asset('assets/site/logo-512.png');
        // Pages can set their own share image with @section('og_image', ...); the logo stays the default.
        $pageImage = trim($__env->yieldContent('og_image')) ?: $siteImage;
        $playStore = 'https://play.google.com/store/apps/details?id=com.MaruMehsana';
        $pageUrl   = url()->current();
        // Pages can set their own canonical (e.g. paginated or filtered lists) with @section('canonical', url).
        $canonical = trim($__env->yieldContent('canonical')) ?: ($lang === 'en' ? $pageUrl . '?lang=en' : $pageUrl);
        // Which header tab is active (each tab is its own page).
        $activeNav = $activeNav ?? 'home';
        $langUrl   = fn ($l) => request()->fullUrlWithQuery(['lang' => $l, 'page' => null]);
    @endphp

    <title>{{ $siteTitle }}</title>
    <meta name="description" content="{{ $siteDesc }}">
    <meta name="theme-color" content="#ffffff">
    <link rel="canonical" href="{{ $canonical }}">
    <link rel="alternate" hreflang="gu" href="{{ $pageUrl }}?lang=gu">
    <link rel="alternate" hreflang="en" href="{{ $pageUrl }}?lang=en">
    <link rel="alternate" hreflang="x-default" href="{{ $pageUrl }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/site/logo-128.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/site/logo-512.png') }}">

    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="Maru Mehsana">
    <meta property="og:locale" content="{{ $lang === 'en' ? 'en_IN' : 'gu_IN' }}">
    <meta property="og:title" content="{{ $siteTitle }}">
    <meta property="og:description" content="{{ $siteDesc }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta name="twitter:card" content="@yield('twitter_card', 'summary')">
    <meta name="twitter:title" content="{{ $siteTitle }}">
    <meta name="twitter:description" content="{{ $siteDesc }}">
    <meta name="twitter:image" content="{{ $pageImage }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/site/site.css') }}?v={{ filemtime(public_path('assets/site/site.css')) }}">

    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            ['@type' => 'Organization', 'name' => 'Maru Mehsana', 'alternateName' => 'મારું મહેસાણા', 'url' => url('/'), 'logo' => $siteImage, 'sameAs' => [$playStore]],
            ['@type' => 'WebSite', 'name' => 'Maru Mehsana', 'url' => url('/'), 'inLanguage' => ['gu', 'en']],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>
    <script>document.documentElement.classList.add('js');</script>
    @stack('head')
</head>
<body class="lang-{{ $lang }}">
    {{-- Icon sprite: <svg class="icon"><use href="#i-name"/></svg> --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
        <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></symbol>
        <symbol id="i-chev-r" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></symbol>
        <symbol id="i-chev-d" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>
        <symbol id="i-pin" viewBox="0 0 24 24"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></symbol>
        <symbol id="i-store" viewBox="0 0 24 24"><path d="M3 9l1.6-5h14.8L21 9M3 9v11h18V9M3 9h18M9.5 20v-6h5v6"/></symbol>
        <symbol id="i-briefcase" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2M2 13h20"/></symbol>
        <symbol id="i-news" viewBox="0 0 24 24"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8V6Z"/></symbol>
        <symbol id="i-book" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2zM22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></symbol>
        <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
        <symbol id="i-bell" viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10.3 21a1.94 1.94 0 0 0 3.4 0"/></symbol>
        <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></symbol>
        <symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7.5"/><path d="m21 21-4.3-4.3"/></symbol>
        <symbol id="i-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10Z"/></symbol>
        <symbol id="i-star" viewBox="0 0 24 24"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.9L12 17.8 5.8 21l1.2-6.9-5-4.9 6.9-1z"/></symbol>
        <symbol id="i-check" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></symbol>
        <symbol id="i-phone" viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/></symbol>
        <symbol id="i-chat" viewBox="0 0 24 24"><path d="M21 11.5a8.4 8.4 0 0 1-12.4 7.4L3 21l2.1-5.6A8.4 8.4 0 1 1 21 11.5Z"/></symbol>
        <symbol id="i-trend" viewBox="0 0 24 24"><path d="m22 7-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/></symbol>
        <symbol id="i-refresh" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-3-6.7L21 8M21 3v5h-5"/></symbol>
        <symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></symbol>
        <symbol id="i-close" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></symbol>
        <symbol id="i-back" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></symbol>
        <symbol id="i-play" viewBox="0 0 24 24"><path d="M6 3.6v16.8a1 1 0 0 0 1.5.86l14-8.4a1 1 0 0 0 0-1.72l-14-8.4A1 1 0 0 0 6 3.6Z"/></symbol>
    </svg>

    <a class="skip-link" href="#main">{{ __('home.skip') }}</a>

    <header class="site-header">
        <div class="container nav">
            <a href="{{ url('/') }}" class="brand" aria-label="{{ __('home.home_aria') }}">
                <img src="{{ asset('assets/site/logo-128.png') }}" width="46" height="46" alt="">
                <span class="brand-name">Maru <b>Mehsana</b></span>
            </a>
            <span class="nav-sep" aria-hidden="true"></span>
            <p class="nav-tagline">{{ __('home.tagline') }}</p>

            <nav class="nav-panel" id="nav-panel" aria-label="{{ __('home.nav_aria') }}">
                <ul class="nav-links">
                    <li><a href="{{ url('/') }}" @if ($activeNav === 'home') class="is-active" aria-current="page" @endif>{{ __('home.nav_home') }}</a></li>
                    <li><a href="{{ route('businesses') }}" @if ($activeNav === 'businesses') class="is-active" aria-current="page" @endif>{{ __('home.nav_businesses') }}</a></li>
                    <li><a href="{{ route('places') }}" @if ($activeNav === 'places') class="is-active" aria-current="page" @endif>{{ __('home.nav_places') }}</a></li>
                    <li><a href="{{ route('news') }}" @if ($activeNav === 'news') class="is-active" aria-current="page" @endif>{{ __('home.nav_news') }}</a></li>
                    <li><a href="{{ route('jobs') }}" @if ($activeNav === 'jobs') class="is-active" aria-current="page" @endif>{{ __('home.nav_jobs') }}</a></li>
                </ul>
            </nav>

            <div class="nav-actions">
                <details class="lang" id="lang-menu">
                    <summary aria-label="{{ __('home.lang_label') }}">
                        <svg class="icon" aria-hidden="true"><use href="#i-globe"/></svg>
                        <span>{{ $lang === 'en' ? __('home.lang_en') : __('home.lang_gu') }}</span>
                        <svg class="icon icon--sm" aria-hidden="true"><use href="#i-chev-d"/></svg>
                    </summary>
                    <ul class="lang-list">
                        <li><a href="{{ $langUrl('gu') }}" hreflang="gu" lang="gu" @if ($lang === 'gu') aria-current="true" @endif>ગુજરાતી</a></li>
                        <li><a href="{{ $langUrl('en') }}" hreflang="en" lang="en" @if ($lang === 'en') aria-current="true" @endif>English</a></li>
                    </ul>
                </details>
                <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="nav-panel"
                        data-open="{{ __('home.menu_open') }}" data-close="{{ __('home.menu_close') }}" aria-label="{{ __('home.menu_open') }}">
                    <svg class="icon icon-open" aria-hidden="true"><use href="#i-menu"/></svg>
                    <svg class="icon icon-close" aria-hidden="true"><use href="#i-close"/></svg>
                </button>
            </div>
        </div>
    </header>

    <main id="main">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <div class="footer-brand">
                <a href="{{ url('/') }}" class="brand" aria-label="{{ __('home.home_aria') }}">
                    <img src="{{ asset('assets/site/logo-128.png') }}" width="40" height="40" alt="" loading="lazy">
                    <span class="brand-name">Maru <b>Mehsana</b></span>
                </a>
                <p class="footer-about">{{ __('home.footer_about') }}</p>
            </div>
            <nav class="footer-links" aria-label="{{ __('home.footer_explore') }}">
                <a href="{{ route('businesses') }}">{{ __('home.nav_businesses') }}</a>
                <a href="{{ route('places') }}">{{ __('home.nav_places') }}</a>
                <a href="{{ route('news') }}">{{ __('home.nav_news') }}</a>
                <a href="{{ route('jobs') }}">{{ __('home.nav_jobs') }}</a>
                <a href="{{ $playStore }}" target="_blank" rel="noopener">Google Play</a>
                <a href="{{ url('/privacy-policy') }}">{{ __('home.privacy') }}</a>
            </nav>
        </div>
        <div class="container footer-bottom">
            <span>&copy; {{ date('Y') }} Maru Mehsana. {{ __('home.rights') }}</span>
            <span>{{ __('home.made') }}</span>
        </div>
    </footer>

    <script src="{{ asset('assets/site/site.js') }}?v={{ filemtime(public_path('assets/site/site.js')) }}" defer></script>
    @stack('scripts')
</body>
</html>
