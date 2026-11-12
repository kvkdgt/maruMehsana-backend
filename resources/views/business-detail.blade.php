@extends('layouts.site')

@php
    $img = fn ($p) => \Illuminate\Support\Str::startsWith($p, ['http://', 'https://']) ? $p : asset('storage/' . ltrim($p, '/'));
    $thumb = $business->thumbnail ? $img($business->thumbnail) : null;
    $reviews = (int) $business->reviews_count;
    $avg = $business->reviews_avg_rating;
    $phone = preg_replace('/\D+/', '', (string) $business->mobile_no);
    $wa = $business->whatsapp_no ?: $business->mobile_no;
    $waDigits = preg_replace('/\D+/', '', (string) $wa);
    $waDigits = strlen($waDigits) === 10 ? '91' . $waDigits : $waDigits;
    $website = $business->website_url;
    if ($website && !\Illuminate\Support\Str::startsWith($website, ['http://', 'https://'])) {
        $website = 'https://' . $website;
    }
    $catName = optional($business->category)->name ?: __('home.business_default');
    $desc = \Illuminate\Support\Str::limit(trim(strip_tags((string) ($business->description ?: $business->services))), 150);
    $photos = [];
    if ($thumb) { $photos[] = $thumb; }
    foreach ($business->businessImages as $gi) {
        $u = $img($gi->image);
        if (!in_array($u, $photos, true)) { $photos[] = $u; }
    }
    $galleryPhotos = $thumb ? array_slice($photos, 1) : $photos;
    $shareUrl = url('/business/' . $business->id);
    $playUrl = 'https://play.google.com/store/apps/details?id=com.MaruMehsana';
@endphp

@section('title', $business->name . ' - ' . $catName . ' | Maru Mehsana')
@section('description', $desc ?: __('home.bizp_meta_desc'))
@section('canonical', route('businesses.show', $business->id) . ($lang === 'en' ? '?lang=en' : ''))

@section('content')
<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="crumbs" aria-label="{{ __('home.bizp_crumbs') }}">
            <a href="{{ url('/') }}">{{ __('home.nav_home') }}</a>
            <svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg>
            <a href="{{ route('businesses') }}">{{ __('home.nav_businesses') }}</a>
            <svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg>
            <span aria-current="page" class="clamp-1">{{ $business->name }}</span>
        </nav>
        <h1>{{ $business->name }}</h1>
    </div>
</section>

<div class="container bd">
    <article class="bd-main">
        <div class="bd-head">
            <div class="bd-photo ph">
                <svg class="icon ph-ico" aria-hidden="true"><use href="#i-store"/></svg>
                @if ($thumb)
                    <img class="bd-photo-bg" src="{{ $thumb }}" alt="" aria-hidden="true" loading="eager" decoding="async" onerror="this.remove()">
                    <button type="button" class="bd-photo-btn" data-lb="0" aria-label="{{ __('home.bizd_zoom') }}">
                        <img src="{{ $thumb }}" alt="{{ $business->name }}" loading="eager" decoding="async" onerror="this.closest('.bd-photo').querySelector('.bd-photo-bg')?.remove();this.closest('button').remove()">
                    </button>
                @endif
            </div>
            <div class="bd-info">
                <p class="dir-cat">{{ $catName }}</p>
                @if ($reviews > 0 && $avg)
                    <p class="rating">
                        <svg class="icon icon--fill star" aria-hidden="true"><use href="#i-star"/></svg>
                        <b>{{ number_format((float) $avg, 1) }}</b>
                        <span>({{ number_format($reviews) }} {{ __('home.bizd_reviews') }})</span>
                    </p>
                @endif
                @if ($phone || $wa)
                    <div class="dir-actions bd-actions">
                        @if ($phone)
                            <a class="dir-btn dir-btn--call" href="tel:{{ $phone }}"><svg class="icon" aria-hidden="true"><use href="#i-phone"/></svg>{{ __('home.bizp_call') }}</a>
                        @endif
                        @if ($wa)
                            <a class="dir-btn dir-btn--wa" href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener"><svg class="icon" aria-hidden="true"><use href="#i-chat"/></svg>WhatsApp</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if ($business->description)
            <section class="bd-sec"><h2>{{ __('home.bizd_about') }}</h2><p>{!! nl2br(e($business->description)) !!}</p></section>
        @endif
        @if ($business->services)
            <section class="bd-sec"><h2>{{ __('home.bizd_services') }}</h2><p>{!! nl2br(e($business->services)) !!}</p></section>
        @endif
        @if ($business->products)
            <section class="bd-sec"><h2>{{ __('home.bizd_products') }}</h2><p>{!! nl2br(e($business->products)) !!}</p></section>
        @endif

        @if (count($galleryPhotos))
            <section class="bd-sec">
                <h2>{{ __('home.bizd_gallery') }}</h2>
                <ul class="bd-gallery">
                    @foreach ($galleryPhotos as $i => $gu)
                        <li><button type="button" data-lb="{{ $i + ($thumb ? 1 : 0) }}" aria-label="{{ __('home.bizd_zoom') }} {{ $i + 1 }}"><img src="{{ $gu }}" alt="" loading="lazy" decoding="async" onerror="this.closest('li').remove()"></button></li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="bd-sec bd-rev" id="reviews" aria-labelledby="bd-rev-title">
            <div class="bd-rev-head">
                <h2 id="bd-rev-title">{{ __('home.bizd_rev_title') }}</h2>
                @if ($reviews > 0)
                    <a class="bd-rev-add" href="{{ $shareUrl }}" rel="nofollow"><svg class="icon" aria-hidden="true"><use href="#i-star"/></svg>{{ __('home.bizd_rev_add') }}</a>
                @endif
            </div>

            @if ($reviews > 0)
                @php $avgRounded = (int) round((float) $avg); @endphp
                <div class="bd-rev-sum">
                    <div class="bd-rev-score">
                        <b>{{ number_format((float) $avg, 1) }}</b>
                        <span class="bd-stars" role="img" aria-label="{{ __('home.bizd_rev_stars', ['n' => number_format((float) $avg, 1)]) }}">
                            @for ($s = 1; $s <= 5; $s++)<svg class="icon {{ $s <= $avgRounded ? 'icon--fill on' : '' }}" aria-hidden="true"><use href="#i-star"/></svg>@endfor
                        </span>
                        <span class="bd-rev-total">{{ number_format($reviews) }} {{ __('home.bizd_reviews') }}</span>
                    </div>
                    <ul class="bd-rev-bars">
                        @foreach ($ratingBreakdown as $star => $count)
                            <li>
                                <span class="bd-rev-n">{{ $star }}<svg class="icon icon--fill" aria-hidden="true"><use href="#i-star"/></svg></span>
                                <span class="bd-rev-bar" aria-hidden="true"><i style="width: {{ $reviews ? round($count / $reviews * 100) : 0 }}%"></i></span>
                                <span class="bd-rev-c">{{ number_format($count) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <ul class="bd-rev-list">
                    @foreach ($latestReviews as $rv)
                        @php
                            $rvName = trim((string) optional($rv->user)->name) ?: __('home.bizd_rev_anon');
                            $rvStars = max(0, min(5, (int) round((float) $rv->rating)));
                            $rvDate = $rv->created_at;
                        @endphp
                        <li class="bd-rev-item">
                            <span class="bd-rev-av" aria-hidden="true">{{ mb_strtoupper(mb_substr($rvName, 0, 1)) }}</span>
                            <div class="bd-rev-body">
                                <div class="bd-rev-meta">
                                    <b class="clamp-1">{{ $rvName }}</b>
                                    @if ($rvDate)
                                        <time datetime="{{ $rvDate->toIso8601String() }}">{{ $rvDate->diffInDays(now()) < 30 ? $rvDate->locale($lang)->diffForHumans() : $rvDate->locale($lang)->isoFormat('D MMM YYYY') }}</time>
                                    @endif
                                </div>
                                <span class="bd-stars bd-stars--sm" role="img" aria-label="{{ __('home.bizd_rev_stars', ['n' => $rvStars]) }}">
                                    @for ($s = 1; $s <= 5; $s++)<svg class="icon {{ $s <= $rvStars ? 'icon--fill on' : '' }}" aria-hidden="true"><use href="#i-star"/></svg>@endfor
                                </span>
                                @if (trim((string) $rv->comment) !== '')
                                    <p>{!! nl2br(e($rv->comment)) !!}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if ($reviews > $latestReviews->count())
                    <p class="bd-rev-more">{{ __('home.bizd_rev_more', ['count' => number_format($reviews)]) }}</p>
                @endif
            @else
                <div class="bd-rev-empty">
                    <svg class="icon" aria-hidden="true"><use href="#i-star"/></svg>
                    <p>{{ __('home.bizd_rev_empty') }}</p>
                    <a class="bd-rev-add" href="{{ $shareUrl }}" rel="nofollow"><svg class="icon" aria-hidden="true"><use href="#i-star"/></svg>{{ __('home.bizd_rev_add') }}</a>
                </div>
            @endif
        </section>
    </article>

    <aside class="bd-side">
        <section class="bd-card">
            <h2>{{ __('home.bizd_contact') }}</h2>
            <ul class="bd-list">
                @if ($phone)<li><svg class="icon" aria-hidden="true"><use href="#i-phone"/></svg><a href="tel:{{ $phone }}">{{ $business->mobile_no }}</a></li>@endif
                @if ($business->email_id)<li><svg class="icon" aria-hidden="true"><use href="#i-chat"/></svg><a href="mailto:{{ $business->email_id }}">{{ $business->email_id }}</a></li>@endif
                @if ($website)<li><svg class="icon" aria-hidden="true"><use href="#i-globe"/></svg><a href="{{ $website }}" target="_blank" rel="noopener nofollow">{{ $business->website_url }}</a></li>@endif
            </ul>
        </section>
        <section class="bd-card bd-app">
            <div class="bd-app-top">
                <img class="bd-app-icon" src="{{ asset('assets/site/logo-128.png') }}" alt="" width="56" height="56" loading="lazy">
                <div>
                    <h2>{{ __('home.bizd_app_title') }}</h2>
                    <p>{{ __('home.bizd_app_sub') }}</p>
                </div>
            </div>
            <div class="bd-app-btns">
                <a class="bd-store" href="{{ $playUrl }}" target="_blank" rel="noopener">
                    <svg class="icon icon--fill" aria-hidden="true"><use href="#i-play"/></svg>{{ __('home.bizd_app_play') }}
                </a>
                <a class="bd-open" href="{{ $shareUrl }}" rel="noopener">
                    <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg>{{ __('home.bizd_app_open') }}
                </a>
            </div>
        </section>
        <a class="bd-back" href="{{ route('businesses') }}"><svg class="icon flip" aria-hidden="true"><use href="#i-chev-r"/></svg>{{ __('home.bizd_back') }}</a>
    </aside>
</div>

@if ($similar->count())
    <section class="container bd-similar">
        <h2>{{ __('home.bizd_similar') }}</h2>
        <ul class="dir-grid">
            @foreach ($similar as $item)
                @include('partials.business-card', ['business' => $item])
            @endforeach
        </ul>
    </section>
@endif

@if (count($photos))
    <dialog class="lb" id="lb" aria-label="{{ __('home.bizd_photo') }}" data-photos="{{ json_encode($photos) }}">
        <button type="button" class="lb-x" data-lb-close aria-label="{{ __('home.bizd_close') }}"><svg class="icon" aria-hidden="true"><use href="#i-close"/></svg></button>
        <button type="button" class="lb-nav lb-prev" data-lb-prev aria-label="{{ __('home.bizd_prev') }}"><svg class="icon flip" aria-hidden="true"><use href="#i-chev-r"/></svg></button>
        <img class="lb-img" alt="">
        <button type="button" class="lb-nav lb-next" data-lb-next aria-label="{{ __('home.bizd_next') }}"><svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg></button>
        <p class="lb-count" aria-live="polite"></p>
    </dialog>
@endif
@endsection
