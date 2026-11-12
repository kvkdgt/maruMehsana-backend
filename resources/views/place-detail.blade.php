@extends('layouts.site')

@php
    $img = fn ($p) => \Illuminate\Support\Str::startsWith($p, ['http://', 'https://']) ? $p : asset('storage/' . ltrim($p, '/'));
    $thumb = $place->thumbnail ? $img($place->thumbnail) : null;
    $reviews = (int) $place->reviews_count;
    $avg = $place->reviews_avg_rating;
    // "location" may hold a pasted Google Maps link: never print it, use it for directions/coordinates.
    $locationText = (string) $place->location_text;
    $geo = $place->geoPoint();
    $hasGeo = $geo !== null;
    [$lat, $lng] = $geo ?? [null, null];
    $directionsUrl = $hasGeo
        ? 'https://www.google.com/maps/dir/?api=1&destination=' . $lat . ',' . $lng
        : $place->maps_link;
    // OpenStreetMap embed needs no API key; a small box around the point with a marker.
    $mapUrl = $hasGeo
        ? 'https://www.openstreetmap.org/export/embed.html?bbox=' . implode(',', [$lng - 0.012, $lat - 0.008, $lng + 0.012, $lat + 0.008]) . '&layer=mapnik&marker=' . $lat . ',' . $lng
        : null;
    $desc = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $place->description))), 150);
    $photos = [];
    if ($thumb) { $photos[] = $thumb; }
    foreach ($place->placeImages as $gi) {
        if (!$gi->image) { continue; }
        $u = $img($gi->image);
        if (!in_array($u, $photos, true)) { $photos[] = $u; }
    }
    $galleryPhotos = $thumb ? array_slice($photos, 1) : $photos;
    $shareUrl = url('/place/' . $place->id);
    $playUrl = 'https://play.google.com/store/apps/details?id=com.MaruMehsana';
    $canonicalUrl = route('places.show', $place->id) . ($lang === 'en' ? '?lang=en' : '');

    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'TouristAttraction',
        'name' => $place->name,
        'url' => $canonicalUrl,
        'inLanguage' => $lang,
    ];
    if ($desc) { $ld['description'] = $desc; }
    if (count($photos)) { $ld['image'] = $photos; }
    $ld['address'] = array_filter([
        '@type' => 'PostalAddress',
        'streetAddress' => $locationText ?: null,
        'addressRegion' => 'Gujarat',
        'addressCountry' => 'IN',
    ]);
    if ($hasGeo) { $ld['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => $lat, 'longitude' => $lng]; }
    if ($reviews > 0 && $avg) {
        $ld['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => round((float) $avg, 1),
            'reviewCount' => $reviews,
            'bestRating' => 5,
            'worstRating' => 1,
        ];
    }
@endphp

{{-- HtmlString: the layout escapes title/description itself, so don't let @section escape them a second time. --}}
@section('title', new \Illuminate\Support\HtmlString($place->name . ' - ' . __('home.pld_label') . ' | Maru Mehsana'))
@section('description', new \Illuminate\Support\HtmlString($desc ?: __('home.pld_meta_desc', ['name' => $place->name])))
@section('canonical', $canonicalUrl)

@if ($thumb)
    @section('og_image', new \Illuminate\Support\HtmlString($thumb))
@endif
@push('head')
    @if ($thumb)
        <meta property="og:image:alt" content="{{ $place->name }}">
    @endif
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="crumbs" aria-label="{{ __('home.bizp_crumbs') }}">
            <a href="{{ url('/') }}">{{ __('home.nav_home') }}</a>
            <svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg>
            <a href="{{ route('places') }}">{{ __('home.nav_places') }}</a>
            <svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg>
            <span aria-current="page" class="clamp-1">{{ $place->name }}</span>
        </nav>
        <h1>{{ $place->name }}</h1>
    </div>
</section>

<div class="container bd">
    <article class="bd-main">
        <div class="bd-head pld-head">
            <div class="bd-photo ph">
                <svg class="icon ph-ico" aria-hidden="true"><use href="#i-pin"/></svg>
                @if ($thumb)
                    <img class="bd-photo-bg" src="{{ $thumb }}" alt="" aria-hidden="true" loading="eager" decoding="async" onerror="this.remove()">
                    <button type="button" class="bd-photo-btn" data-lb="0" aria-label="{{ __('home.bizd_zoom') }}">
                        <img src="{{ $thumb }}" alt="{{ $place->name }}" loading="eager" decoding="async" onerror="this.closest('.bd-photo').querySelector('.bd-photo-bg')?.remove();this.closest('button').remove()">
                    </button>
                @endif
            </div>
            <div class="bd-info">
                <p class="dir-cat">{{ __('home.pld_label') }}</p>
                @if ($reviews > 0 && $avg)
                    <p class="rating">
                        <svg class="icon icon--fill star" aria-hidden="true"><use href="#i-star"/></svg>
                        <b>{{ number_format((float) $avg, 1) }}</b>
                        <a href="#reviews">({{ number_format($reviews) }} {{ __('home.bizd_reviews') }})</a>
                    </p>
                @else
                    <p class="rating"><a href="#reviews">{{ __('home.pld_no_rating') }}</a></p>
                @endif
                @if ($locationText !== '')
                    <p class="pld-where"><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg><span>{{ $locationText }}</span></p>
                @endif
                <div class="pld-actions">
                    @if ($directionsUrl)
                        <a class="bd-store" href="{{ $directionsUrl }}" target="_blank" rel="noopener"><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg>{{ __('home.pld_directions') }}</a>
                    @endif
                    <a class="bd-open" href="{{ $shareUrl }}" rel="noopener"><svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg>{{ __('home.bizd_app_open') }}</a>
                </div>
            </div>
        </div>

        @if (trim((string) $place->description) !== '')
            <section class="bd-sec"><h2>{{ __('home.bizd_about') }}</h2><p>{!! nl2br(e($place->description)) !!}</p></section>
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
        @if ($hasGeo || $directionsUrl || $locationText !== '')
            <section class="bd-card pld-loc">
                <h2>{{ __('home.pld_location') }}</h2>
                @if ($locationText !== '')
                    <p class="pld-where"><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg><span>{{ $locationText }}</span></p>
                @endif
                @if ($mapUrl)
                    <div class="pld-map">
                        <iframe src="{{ $mapUrl }}" title="{{ __('home.pld_map_title', ['name' => $place->name]) }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                @endif
                @if ($directionsUrl)
                    <a class="bd-store pld-dir" href="{{ $directionsUrl }}" target="_blank" rel="noopener"><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg>{{ __('home.pld_directions') }}</a>
                @endif
            </section>
        @endif
        <section class="bd-card bd-app">
            <div class="bd-app-top">
                <img class="bd-app-icon" src="{{ asset('assets/site/logo-128.png') }}" alt="" width="56" height="56" loading="lazy">
                <div>
                    <h2>{{ __('home.bizd_app_title') }}</h2>
                    <p>{{ __('home.pld_app_sub') }}</p>
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
        <a class="bd-back" href="{{ route('places') }}"><svg class="icon flip" aria-hidden="true"><use href="#i-chev-r"/></svg>{{ __('home.pld_back') }}</a>
    </aside>
</div>

@if ($morePlaces->count())
    <section class="container bd-similar">
        <h2>{{ __('home.pld_more') }}</h2>
        <ul class="place-grid">
            @foreach ($morePlaces as $item)
                @php
                    $mThumb = $item->thumbnail ? $img($item->thumbnail) : null;
                    $mReviews = (int) $item->reviews_count;
                    $mAvg = $item->reviews_avg_rating;
                @endphp
                <li>
                    <a class="place-card" href="{{ route('places.show', $item->id) }}">
                        <span class="media ph">
                            <svg class="icon ph-ico" aria-hidden="true"><use href="#i-pin"/></svg>
                            @if ($mThumb)
                                <img src="{{ $mThumb }}" alt="" width="400" height="240" loading="lazy" decoding="async" onerror="this.remove()">
                            @endif
                        </span>
                        <span class="place-foot">
                            <span class="place-text">
                                <span class="place-name clamp-2">{{ $item->name }}</span>
                                @if ($mReviews > 0 && $mAvg)
                                    <span class="rating" aria-label="{{ __('home.rating_aria', ['rating' => number_format((float) $mAvg, 1), 'count' => $mReviews]) }}">
                                        <svg class="icon icon--fill star" aria-hidden="true"><use href="#i-star"/></svg>
                                        <b>{{ number_format((float) $mAvg, 1) }}</b>
                                        <span>({{ number_format($mReviews) }})</span>
                                    </span>
                                @endif
                            </span>
                            <span class="go" aria-hidden="true"><svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></span>
                        </span>
                    </a>
                </li>
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
