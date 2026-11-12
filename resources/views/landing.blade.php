@extends('layouts.site')

@section('title', __('home.meta_title'))
@section('description', __('home.meta_desc'))

@php
    $playStore = 'https://play.google.com/store/apps/details?id=com.MaruMehsana';
    $logo      = asset('assets/site/logo-128.png');

    // Resolve an uploaded image (storage path) or an absolute URL; null when missing.
    $img = function ($path) {
        if (!$path) return null;
        return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://']) ? $path : asset('storage/' . ltrim($path, '/'));
    };

    // Hero photo: drop public/assets/site/hero.jpg (+ optional hero-mobile.jpg) and it is used automatically.
    $heroFile   = public_path('assets/site/hero.jpg');
    $heroMobile = public_path('assets/site/hero-mobile.jpg');
    $hasHero    = file_exists($heroFile);
    $hasHeroMob = file_exists($heroMobile);
    $heroUrl    = $hasHero ? asset('assets/site/hero.jpg') . '?v=' . filemtime($heroFile) : null;
    $heroMobUrl = $hasHeroMob ? asset('assets/site/hero-mobile.jpg') . '?v=' . filemtime($heroMobile) : null;

    $salary = function ($j) {
        if ($j->salary_type === 'not_disclosed' || (!$j->salary_min && !$j->salary_max)) return null;
        $per = ['monthly' => 'per_month', 'yearly' => 'per_year', 'hourly' => 'per_hour'][$j->salary_type] ?? null;
        $min = (float) $j->salary_min;
        $max = (float) $j->salary_max;
        $range = ($min && $max && $min != $max)
            ? '₹' . number_format($min) . ' – ' . number_format($max)
            : '₹' . number_format($max ?: $min);
        return $range . ($per ? ' / ' . __('home.' . $per) : '');
    };

    $searchI18n = [
        'api'     => url('/api'),
        'base'    => url('/'),
        'minHint' => __('home.search_hint'),
        'loading' => __('home.search_loading'),
        'none'    => __('home.search_none'),
        'error'   => __('home.search_error'),
        'found'   => __('home.search_found'),
        'catBack' => __('home.category_back'),
        'catTitle'=> __('home.category_title'),
        'catEmpty'=> __('home.category_empty'),
        'catLabel'=> __('home.category_open'),
        'groups'  => [
            'category' => __('home.group_category'),
            'business' => __('home.group_business'),
            'place'    => __('home.group_place'),
            'news'     => __('home.group_news'),
            'job'      => __('home.group_job'),
        ],
    ];
@endphp

@push('head')
    @if ($hasHero)
        <link rel="preload" as="image" href="{{ $heroMobUrl ?: $heroUrl }}" @if ($hasHeroMob) media="(max-width: 720px)" @endif fetchpriority="high">
        @if ($hasHeroMob)<link rel="preload" as="image" href="{{ $heroUrl }}" media="(min-width: 721px)" fetchpriority="high">@endif
    @endif
@endpush

@section('content')

{{-- ============================ HERO ============================ --}}
<section class="hero" id="home" aria-labelledby="hero-title">
    <div class="hero-bg" aria-hidden="{{ $hasHero ? 'false' : 'true' }}">
        @if ($hasHero)
            <picture>
                @if ($hasHeroMob)<source media="(max-width: 720px)" srcset="{{ $heroMobUrl }}">@endif
                <img src="{{ $heroUrl }}" alt="" width="1920" height="640" fetchpriority="high" decoding="async">
            </picture>
        @else
            {{-- Brand-coloured illustrated fallback, shown until hero.jpg is added --}}
            <svg class="hero-art" viewBox="0 0 1440 640" preserveAspectRatio="xMaxYMax slice" focusable="false">
                <defs>
                    <linearGradient id="g-sky" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#9fd0f5"/><stop offset=".55" stop-color="#d8ecfb"/><stop offset="1" stop-color="#fdf1dc"/>
                    </linearGradient>
                    <radialGradient id="g-sun" cx=".5" cy=".5" r=".5">
                        <stop offset="0" stop-color="#fff6d6" stop-opacity="1"/><stop offset=".45" stop-color="#ffe9b0" stop-opacity=".55"/><stop offset="1" stop-color="#ffe9b0" stop-opacity="0"/>
                    </radialGradient>
                    <linearGradient id="g-tower" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0" stop-color="#e3c389"/><stop offset=".55" stop-color="#d4a967"/><stop offset="1" stop-color="#b88a4a"/>
                    </linearGradient>
                    <linearGradient id="g-ground" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#8cc37a"/><stop offset="1" stop-color="#cfe6c2"/>
                    </linearGradient>
                </defs>
                <rect width="1440" height="640" fill="url(#g-sky)"/>
                <circle cx="1010" cy="170" r="230" fill="url(#g-sun)"/>
                <g fill="#fff" opacity=".85">
                    <ellipse cx="260" cy="110" rx="110" ry="26"/><ellipse cx="330" cy="92" rx="70" ry="26"/>
                    <ellipse cx="900" cy="70" rx="120" ry="24"/><ellipse cx="970" cy="54" rx="64" ry="22"/>
                    <ellipse cx="1330" cy="120" rx="100" ry="22"/>
                </g>
                {{-- distant skyline --}}
                <g fill="#b9cfe3" opacity=".75">
                    <rect x="470" y="470" width="60" height="90"/><rect x="535" y="450" width="44" height="110"/><rect x="585" y="482" width="80" height="78"/>
                    <rect x="680" y="462" width="52" height="98"/><rect x="740" y="488" width="90" height="72"/><rect x="840" y="470" width="50" height="90"/>
                    <rect x="1250" y="440" width="70" height="120"/><rect x="1330" y="460" width="60" height="100"/>
                </g>
                {{-- clock tower --}}
                <g>
                    <rect x="1050" y="520" width="170" height="60" fill="#c79d5d"/>
                    <rect x="1066" y="470" width="138" height="56" fill="url(#g-tower)"/>
                    <rect x="1082" y="250" width="106" height="226" fill="url(#g-tower)"/>
                    <rect x="1074" y="236" width="122" height="22" fill="#c49a55"/>
                    <rect x="1090" y="150" width="90" height="90" fill="url(#g-tower)"/>
                    <rect x="1084" y="140" width="102" height="14" fill="#c49a55"/>
                    <path d="M1096 140 q39 -70 78 0z" fill="#b88a4a"/>
                    <rect x="1133" y="64" width="4" height="30" fill="#8a6430"/>
                    <circle cx="1135" cy="104" r="12" fill="#c49a55"/>
                    <circle cx="1135" cy="196" r="30" fill="#fff"/><circle cx="1135" cy="196" r="30" fill="none" stroke="#7a5524" stroke-width="5"/>
                    <path d="M1135 176v22l14 8" fill="none" stroke="#1d2b44" stroke-width="3" stroke-linecap="round"/>
                    <circle cx="1135" cy="312" r="24" fill="#fff"/><circle cx="1135" cy="312" r="24" fill="none" stroke="#7a5524" stroke-width="5"/>
                    <path d="M1135 296v17l11 6" fill="none" stroke="#1d2b44" stroke-width="3" stroke-linecap="round"/>
                    <path d="M1112 560v-60q23 -26 46 0v60z" fill="#7a5524" opacity=".75"/>
                    <path d="M1090 400h90M1090 430h90" stroke="#b88a4a" stroke-width="4"/>
                </g>
                {{-- ground --}}
                <rect y="552" width="1440" height="88" fill="url(#g-ground)"/>
                {{-- trees --}}
                <g>
                    <rect x="1304" y="360" width="16" height="200" fill="#6b4a2b"/>
                    <circle cx="1310" cy="330" r="86" fill="#3f8f4a"/><circle cx="1370" cy="290" r="70" fill="#2f7a3e"/><circle cx="1250" cy="380" r="58" fill="#56a653"/><circle cx="1400" cy="390" r="64" fill="#3f8f4a"/>
                    <circle cx="1300" cy="270" r="52" fill="#56a653"/>
                    <rect x="64" y="400" width="16" height="170" fill="#6b4a2b"/>
                    <circle cx="70" cy="390" r="82" fill="#3f8f4a"/><circle cx="-10" cy="420" r="70" fill="#2f7a3e"/><circle cx="150" cy="430" r="60" fill="#56a653"/><circle cx="60" cy="330" r="54" fill="#56a653"/>
                    <circle cx="960" cy="520" r="50" fill="#3f8f4a"/><circle cx="1010" cy="540" r="42" fill="#56a653"/>
                    <circle cx="1255" cy="540" r="48" fill="#2f7a3e"/>
                    <circle cx="620" cy="548" r="30" fill="#56a653"/><circle cx="660" cy="552" r="26" fill="#3f8f4a"/>
                </g>
            </svg>
        @endif
        <span class="hero-scrim"></span>
    </div>

    <div class="container hero-inner">
        <p class="hello">
            <span>{{ __('home.hello') }}</span>
            <svg class="spark" viewBox="0 0 34 34" aria-hidden="true"><path d="M4 30 17 17M18 8l6-6M22 16h10" stroke="#f59e0b" stroke-width="3.4" stroke-linecap="round" fill="none"/></svg>
        </p>

        <h1 id="hero-title">
            <span class="h1-line">{{ __('home.h1_a') }}</span>
            <span class="h1-line">{{ __('home.h1_b') }}
                <span class="u">{{ __('home.h1_u') }}<svg viewBox="0 0 200 14" preserveAspectRatio="none" aria-hidden="true"><path d="M3 9C40 2 90 2 130 5c24 2 44 3 67 0" fill="none" stroke="#f59e0b" stroke-width="4" stroke-linecap="round"/></svg></span>
            </span>
        </h1>

        <p class="hero-lead">{{ __('home.lead') }}</p>

        <form class="search" id="search" role="search" autocomplete="off" onsubmit="return false">
            <div class="search-box">
                <svg class="icon search-ico" aria-hidden="true"><use href="#i-search"/></svg>
                <input id="search-input" class="search-input" type="search" name="q" maxlength="80" enterkeyhint="search"
                       role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="search-results" aria-haspopup="listbox"
                       aria-label="{{ __('home.search_label') }}" placeholder="{{ __('home.search_placeholder') }}"
                       autocomplete="off" autocapitalize="off" spellcheck="false">
                <button type="button" class="search-clear" id="search-clear" hidden aria-label="{{ __('home.search_clear') }}">
                    <svg class="icon" aria-hidden="true"><use href="#i-close"/></svg>
                </button>
                <button type="submit" class="search-btn" id="search-go">{{ __('home.search_btn') }}</button>
            </div>
            <div class="search-panel" id="search-panel" hidden>
                <p class="search-msg" id="search-msg" hidden></p>
                <ul class="search-results" id="search-results" role="listbox" aria-label="{{ __('home.search_label') }}"></ul>
            </div>
            <p class="sr-only" id="search-status" role="status" aria-live="polite"></p>
        </form>
        <script type="application/json" id="search-i18n">{!! json_encode($searchI18n, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>

        @if ($chipCategories->count())
            <div class="chips">
                <span class="chips-label">{{ __('home.popular') }}</span>
                <ul class="chips-list">
                    @foreach ($chipCategories as $c)
                        <li><button type="button" class="chip" data-q="{{ $c->name }}" data-category="{{ $c->id }}">{{ $c->name }}</button></li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</section>

{{-- ========================= QUICK TILES ========================= --}}
<div class="container quick-wrap">
    <nav class="quick" aria-label="{{ __('home.tiles_aria') }}">
        <ul class="quick-grid">
            <li><a href="{{ route('businesses') }}" class="tile is-active"><span class="tile-ic"><svg class="icon" aria-hidden="true"><use href="#i-store"/></svg></span><span class="tile-label">{{ __('home.tile_business') }}</span></a></li>
            <li><a href="#places" class="tile tile--orange"><span class="tile-ic"><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg></span><span class="tile-label">{{ __('home.tile_places') }}</span></a></li>
            <li><a href="#news" class="tile"><span class="tile-ic"><svg class="icon" aria-hidden="true"><use href="#i-news"/></svg></span><span class="tile-label">{{ __('home.tile_news') }}</span></a></li>
            <li><a href="#jobs" class="tile"><span class="tile-ic"><svg class="icon" aria-hidden="true"><use href="#i-briefcase"/></svg></span><span class="tile-label">{{ __('home.tile_jobs') }}</span></a></li>
            <li><button type="button" class="tile tile--add" data-open-enquiry aria-haspopup="dialog" aria-controls="enquiry"><span class="tile-ic"><svg class="icon" aria-hidden="true"><use href="#i-plus"/></svg></span><span class="tile-label">{{ __('home.tile_add') }}</span></button></li>
        </ul>
    </nav>
</div>

{{-- ============ ADD YOUR BUSINESS: enquiry popup (saved with source = web) ============ --}}
<dialog class="enq" id="enquiry" aria-labelledby="enq-title">
  <div class="enq-wrap">
    {{-- Left: brand + mock app cards + real stats (becomes a slim banner on phones) --}}
    <aside class="enq-side" aria-hidden="true">
        <div class="enq-brand">
            <img src="{{ $logo }}" alt="" width="34" height="34">
            <span>Maru <b>Mehsana</b></span>
            <span class="enq-pill">{{ __('home.enq_pill') }}</span>
        </div>

        <div class="enq-stage">
            {{-- Card 1: what their listing will look like --}}
            <div class="mk mk-listing">
                <div class="mk-row">
                    <span class="mk-thumb"><svg class="icon"><use href="#i-store"/></svg></span>
                    <div class="mk-info">
                        <p class="mk-title">{{ __('home.enq_mk_name') }}</p>
                        <p class="mk-meta">
                            <span class="mk-chip">{{ __('home.enq_mk_cat') }}</span>
                            <span class="mk-stars"><svg class="icon"><use href="#i-star"/></svg>4.8</span>
                        </p>
                        <p class="mk-place"><svg class="icon"><use href="#i-pin"/></svg>{{ __('home.enq_mk_place') }}</p>
                    </div>
                    <span class="mk-live"><i></i>{{ __('home.enq_mk_live_short') }}</span>
                </div>
                <div class="mk-actions">
                    <span class="mk-btn mk-btn--call"><svg class="icon"><use href="#i-phone"/></svg>{{ __('home.enq_mk_call') }}</span>
                    <span class="mk-btn mk-btn--wa"><svg class="icon"><use href="#i-chat"/></svg>WhatsApp</span>
                </div>
            </div>

            {{-- Card 2: incoming customer message --}}
            <div class="mk mk-msg">
                <p class="mk-card-head"><span class="mk-dot mk-dot--green"><svg class="icon"><use href="#i-chat"/></svg></span>{{ __('home.enq_mk_new') }}</p>
                <p class="mk-bubble">{{ __('home.enq_mk_msg') }}</p>
                <p class="mk-time">2 {{ __('home.enq_mk_min') }}</p>
            </div>

            {{-- Card 3: profile views --}}
            <div class="mk mk-views">
                <p class="mk-card-head"><span class="mk-dot"><svg class="icon"><use href="#i-trend"/></svg></span>{{ __('home.enq_mk_views') }}</p>
                <p class="mk-views-num">1,284 <b class="mk-up">+24%</b></p>
                <div class="mk-bars"><i style="height:35%"></i><i style="height:50%"></i><i style="height:42%"></i><i style="height:64%"></i><i style="height:58%"></i><i style="height:80%"></i><i style="height:100%"></i></div>
            </div>
        </div>

        <div class="enq-trust">
            <p class="enq-trust-title">{{ __('home.enq_trust') }}</p>
            <div class="enq-stats">
                <div><b>{{ number_format($enqStats['businesses']) }}+</b><span>{{ __('home.enq_stat_biz') }}</span></div>
                <div><b>{{ number_format($enqStats['categories']) }}+</b><span>{{ __('home.enq_stat_cat') }}</span></div>
                <div><b>{{ $enqStats['views'] >= 1000 ? number_format($enqStats['views'] / 1000, 1) . 'K' : number_format($enqStats['views']) }}+</b><span>{{ __('home.enq_stat_views') }}</span></div>
            </div>
        </div>
    </aside>

    {{-- Right: form --}}
    <form class="enq-form" id="enq-form" method="POST" action="{{ route('business-enquiry.web') }}" novalidate
          data-sending="{{ __('home.enq_sending') }}" data-err-generic="{{ __('home.enq_err_generic') }}" data-err-many="{{ __('home.enq_err_many') }}">
        @csrf
        <button type="button" class="enq-x" data-close-enquiry aria-label="{{ __('home.enq_close') }}"><svg class="icon" aria-hidden="true"><use href="#i-close"/></svg></button>
        <div class="enq-head">
            <p class="enq-eyebrow">{{ __('home.enq_eyebrow') }}</p>
            <h2 id="enq-title">{{ __('home.enq_h_a') }} <span class="enq-accent">{{ __('home.enq_h_u') }}<svg viewBox="0 0 200 14" preserveAspectRatio="none" aria-hidden="true"><path d="M3 9C40 2 90 2 130 5c24 2 44 3 67 0" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg></span> {{ __('home.enq_h_b') }}</h2>
        </div>
        <div class="enq-done" id="enq-done" hidden>
            <span class="enq-done-ic"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg></span>
            <p class="enq-done-text" id="enq-done-text"></p>
            <button type="button" class="enq-submit" data-close-enquiry>{{ __('home.enq_close') }}</button>
        </div>
        <div class="enq-body" id="enq-fields">
            <p class="enq-sub">{{ __('home.enq_sub') }}</p>

            <div class="enq-grid">
                <div class="enq-field">
                    <label for="enq-business">{{ __('home.enq_business') }}</label>
                    <input id="enq-business" name="business_name" type="text" maxlength="255" required autocomplete="organization">
                    <p class="enq-err" data-err="business_name"></p>
                </div>
                <div class="enq-field">
                    <label for="enq-owner">{{ __('home.enq_owner') }}</label>
                    <input id="enq-owner" name="owner_name" type="text" maxlength="255" required autocomplete="name">
                    <p class="enq-err" data-err="owner_name"></p>
                </div>
                <div class="enq-field">
                    <label for="enq-mobile">{{ __('home.enq_mobile') }}</label>
                    <input id="enq-mobile" name="mobile_no" type="tel" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" required autocomplete="tel-national">
                    <p class="enq-err" data-err="mobile_no"></p>
                </div>
                <div class="enq-field">
                    <label for="enq-whatsapp">{{ __('home.enq_whatsapp') }}</label>
                    <input id="enq-whatsapp" name="whatsapp_no" type="tel" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" required>
                    <p class="enq-err" data-err="whatsapp_no"></p>
                </div>
            </div>
            <label class="enq-check"><input type="checkbox" id="enq-same" checked> {{ __('home.enq_same') }}</label>

            {{-- Honeypot for bots: hidden from people and screen readers --}}
            <input type="text" name="website" tabindex="-1" autocomplete="off" class="enq-hp" aria-hidden="true">

            <p class="enq-msg" id="enq-msg" role="status" aria-live="polite"></p>
            <button type="submit" class="enq-submit" id="enq-submit">{{ __('home.enq_submit') }}</button>
        </div>
    </form>
  </div>
</dialog>

{{-- ======================= CONTENT: ROW 1 ======================= --}}
<div class="container home-rows">
    <div class="duo">

        {{-- ---- Top businesses ---- --}}
        <section class="block" id="businesses" aria-labelledby="biz-title">
            <header class="block-head">
                <span class="badge badge--blue"><svg class="icon" aria-hidden="true"><use href="#i-store"/></svg></span>
                <div class="block-titles">
                    <h2 id="biz-title">{{ __('home.biz_title') }}</h2>
                    <p>{{ __('home.biz_sub') }}</p>
                </div>
                <a class="view-all" href="{{ route('businesses') }}">{{ __('home.view_all') }} <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
            </header>

            @if ($topBusinesses->count())
                <ul class="biz-grid">
                    @foreach ($topBusinesses as $business)
                        @php $reviews = (int) $business->reviews_count; $avg = $business->reviews_avg_rating; @endphp
                        <li class="biz-card">
                            <div class="media ph">
                                <svg class="icon ph-ico" aria-hidden="true"><use href="#i-store"/></svg>
                                @if ($business->thumbnail)
                                    <img src="{{ $img($business->thumbnail) }}" alt="" width="400" height="260" loading="lazy" decoding="async" onerror="this.remove()">
                                @endif
                            </div>
                            <div class="biz-body">
                                <h3 class="clamp-1"><a class="stretched" href="{{ route('businesses.show', $business->id) }}">{{ $business->name }}</a></h3>
                                <p class="biz-cat clamp-1">{{ optional($business->category)->name ?: __('home.business_default') }}</p>
                                @if ($reviews > 0 && $avg)
                                    <p class="rating" aria-label="{{ __('home.rating_aria', ['rating' => number_format((float) $avg, 1), 'count' => $reviews]) }}">
                                        <svg class="icon icon--fill star" aria-hidden="true"><use href="#i-star"/></svg>
                                        <b>{{ number_format((float) $avg, 1) }}</b>
                                        <span>({{ number_format($reviews) }})</span>
                                    </p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="empty"><img src="{{ $logo }}" alt="" width="48" height="48" loading="lazy"><p>{{ __('home.empty_biz') }}</p></div>
            @endif
        </section>

        {{-- ---- Latest news ---- --}}
        <section class="block block--side" id="news" aria-labelledby="news-title">
            <header class="block-head">
                <span class="badge badge--saffron"><svg class="icon" aria-hidden="true"><use href="#i-news"/></svg></span>
                <div class="block-titles">
                    <h2 id="news-title">{{ __('home.news_title') }}</h2>
                    <p>{{ __('home.news_sub') }}</p>
                </div>
                <a class="view-all" href="{{ $playStore }}" target="_blank" rel="noopener" aria-label="{{ __('home.view_all') }} - {{ __('home.view_all_hint') }}">{{ __('home.view_all') }} <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
            </header>

            @if ($latestNews->count())
                <ul class="news-list">
                    @foreach ($latestNews as $article)
                        <li>
                            <a class="news-row" href="{{ url('/news/' . $article->id) }}">
                                <span class="news-thumb ph">
                                    <svg class="icon ph-ico" aria-hidden="true"><use href="#i-news"/></svg>
                                    @if ($article->imageUrl)
                                        <img src="{{ $article->imageUrl }}" alt="" width="240" height="160" loading="lazy" decoding="async" onerror="this.remove()">
                                    @endif
                                </span>
                                <span class="news-text">
                                    <span class="news-title clamp-2">{{ $article->title }}</span>
                                    <span class="news-meta">
                                        <svg class="icon" aria-hidden="true"><use href="#i-calendar"/></svg>
                                        <time datetime="{{ $article->created_at->toDateString() }}">{{ $article->created_at->translatedFormat('d M Y') }}</time>
                                        @if ($article->agency)<span class="news-agency clamp-1">{{ $article->agency->name }}</span>@endif
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="empty"><img src="{{ $logo }}" alt="" width="48" height="48" loading="lazy"><p>{{ __('home.empty_news') }}</p></div>
            @endif
        </section>
    </div>

    {{-- ======================= CONTENT: ROW 2 ======================= --}}
    <div class="duo duo--second">

        {{-- ---- Popular places ---- --}}
        <section class="block" id="places" aria-labelledby="places-title">
            <header class="block-head">
                <span class="badge badge--blue badge--round"><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg></span>
                <div class="block-titles">
                    <h2 id="places-title">{{ __('home.places_title') }}</h2>
                    <p>{{ __('home.places_sub') }}</p>
                </div>
                <a class="view-all" href="{{ $playStore }}" target="_blank" rel="noopener" aria-label="{{ __('home.view_all') }} - {{ __('home.view_all_hint') }}">{{ __('home.view_all') }} <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
            </header>

            @if ($popularPlaces->count())
                <ul class="place-grid">
                    @foreach ($popularPlaces as $place)
                        <li>
                            <a class="place-card" href="{{ url('/place/' . $place->id) }}">
                                <span class="media ph">
                                    <svg class="icon ph-ico" aria-hidden="true"><use href="#i-pin"/></svg>
                                    @if ($place->thumbnail)
                                        <img src="{{ $img($place->thumbnail) }}" alt="" width="400" height="240" loading="lazy" decoding="async" onerror="this.remove()">
                                    @endif
                                </span>
                                <span class="place-foot">
                                    <span class="place-text">
                                        <span class="place-name clamp-2">{{ $place->name }}</span>
                                    </span>
                                    <span class="go" aria-hidden="true"><svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="empty"><img src="{{ $logo }}" alt="" width="48" height="48" loading="lazy"><p>{{ __('home.empty_places') }}</p></div>
            @endif
        </section>

        {{-- ---- Latest jobs ---- --}}
        <section class="block block--side" id="jobs" aria-labelledby="jobs-title">
            <header class="block-head">
                <span class="badge badge--blue"><svg class="icon" aria-hidden="true"><use href="#i-briefcase"/></svg></span>
                <div class="block-titles">
                    <h2 id="jobs-title">{{ __('home.jobs_title') }}</h2>
                    <p>{{ __('home.jobs_sub') }}</p>
                </div>
                <a class="view-all" href="{{ $playStore }}" target="_blank" rel="noopener" aria-label="{{ __('home.view_all') }} - {{ __('home.view_all_hint') }}">{{ __('home.view_all') }} <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
            </header>

            @if ($latestJobs->count())
                <ul class="job-list">
                    @foreach ($latestJobs as $job)
                        @php $pay = $salary($job); @endphp
                        <li>
                            <a class="job-row" href="{{ url('/job/' . $job->id) }}">
                                <span class="job-ic"><svg class="icon" aria-hidden="true"><use href="#i-briefcase"/></svg></span>
                                <span class="job-text">
                                    <span class="job-title clamp-1">{{ $job->title }}</span>
                                    @if ($job->location || $job->company_name)
                                        <span class="job-meta clamp-1"><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg>{{ $job->location }}@if ($job->location && $job->company_name) &middot; @endif{{ $job->company_name }}</span>
                                    @endif
                                    @if ($pay)<span class="job-pay clamp-1">{{ $pay }}</span>@endif
                                </span>
                                <svg class="icon job-go" aria-hidden="true"><use href="#i-chev-r"/></svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="empty"><img src="{{ $logo }}" alt="" width="48" height="48" loading="lazy"><p>{{ __('home.empty_jobs') }}</p></div>
            @endif
        </section>
    </div>
</div>

{{-- ============================ FACTS ============================ --}}
@if ($facts->count())
<section class="container facts" id="facts" aria-labelledby="facts-title">
    <div class="facts-card">
        <span class="badge badge--saffron"><svg class="icon" aria-hidden="true"><use href="#i-book"/></svg></span>
        <div class="facts-body">
            <h2 id="facts-title">{{ __('home.facts_title') }}</h2>
            <p class="facts-text" id="fact-text" aria-live="polite">{{ $facts->first()->fact }}</p>
        </div>
        <button type="button" class="facts-next" id="fact-next" @if ($facts->count() < 2) hidden @endif>
            <svg class="icon" aria-hidden="true"><use href="#i-refresh"/></svg> {{ __('home.facts_next') }}
        </button>
    </div>
    <script type="application/json" id="fact-data">{!! json_encode($facts->pluck('fact')->values(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
</section>
@endif

{{-- ============================ APP BANNER ============================ --}}
<section class="app" id="app" aria-labelledby="app-title">
    <div class="app-bg" aria-hidden="true">
        <svg viewBox="0 0 1440 300" preserveAspectRatio="none" focusable="false">
            <defs>
                <linearGradient id="g-wave1" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#1769b5"/><stop offset="1" stop-color="#3b8be0"/></linearGradient>
            </defs>
            <path d="M0 150C120 110 230 100 330 140C420 175 470 270 640 300H0Z" fill="url(#g-wave1)" opacity=".95"/>
            <path d="M0 200C100 175 190 190 260 230C330 268 400 292 470 300H0Z" fill="#0e3f7a" opacity=".55"/>
            <path d="M420 300C700 270 1000 300 1440 230V300Z" fill="#dcebfa" opacity=".8"/>
        </svg>
    </div>
    <div class="container app-inner">
        <div class="phone" aria-hidden="true">
            <div class="phone-screen">
                <img src="{{ asset('assets/site/logo-128.png') }}" width="76" height="76" alt="" loading="lazy">
                <span>Maru <b>Mehsana</b></span>
            </div>
        </div>

        <div class="app-copy">
            <h2 id="app-title">{{ __('home.app_title') }}</h2>
            <p class="app-sub">{{ __('home.app_sub') }}</p>
            <ul class="app-feats">
                <li><span class="fi fi--orange"><svg class="icon" aria-hidden="true"><use href="#i-store"/></svg></span>{{ __('home.feat_business') }}</li>
                <li><span class="fi fi--blue"><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg></span>{{ __('home.feat_places') }}</li>
                <li><span class="fi fi--orange"><svg class="icon" aria-hidden="true"><use href="#i-news"/></svg></span>{{ __('home.feat_news') }}</li>
                <li><span class="fi fi--orange"><svg class="icon" aria-hidden="true"><use href="#i-bell"/></svg></span>{{ __('home.feat_updates') }}</li>
            </ul>
        </div>

        <div class="app-get">
            <a href="{{ $playStore }}" class="gplay" target="_blank" rel="noopener" aria-label="{{ __('home.play_aria') }}">
                <svg class="gplay-logo" viewBox="0 0 20 22" aria-hidden="true">
                    <path d="M1 1 11 11 1 21Z" fill="#00a0ff"/>
                    <path d="M1 1 14.5 8.5 11 11Z" fill="#00e575"/>
                    <path d="M1 21 14.5 13.5 11 11Z" fill="#ff3a44"/>
                    <path d="M14.5 8.5 19 11 14.5 13.5 11 11Z" fill="#ffd500"/>
                </svg>
                <span class="gplay-text"><small>{{ __('home.get_it_on') }}</small><b>Google Play</b></span>
            </a>
            <div class="qr" id="qr" data-url="{{ $playStore }}" hidden>
                <div class="qr-code" id="qr-code" role="img" aria-label="{{ __('home.qr_aria') }}"></div>
            </div>
        </div>
    </div>
</section>

@endsection
