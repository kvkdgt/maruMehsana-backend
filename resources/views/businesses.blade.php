@extends('layouts.site')

@section('title', __('home.bizp_meta_title'))
@section('description', __('home.bizp_meta_desc'))
@php
    // Page 1 is the clean URL (no ?page=1); used by the pager and the rel prev/next links below.
    $prevUrl = $businesses->onFirstPage() ? null
        : ($businesses->currentPage() === 2 ? request()->fullUrlWithoutQuery('page') : $businesses->previousPageUrl());
    $nextUrl = $businesses->nextPageUrl();

    // Canonical: each page of a category listing points to itself; search text and sort order
    // are just views of the same list, so they are left out.
    $canonicalUrl = route('businesses', array_filter([
        'category' => $activeCategory?->id,
        'page'     => $businesses->currentPage() > 1 ? $businesses->currentPage() : null,
        'lang'     => $lang === 'en' ? 'en' : null,
    ]));
@endphp
@section('canonical', $canonicalUrl)
@push('head')
    @if ($prevUrl)<link rel="prev" href="{{ $prevUrl }}">@endif
    @if ($nextUrl)<link rel="next" href="{{ $nextUrl }}">@endif
@endpush

@php
    $logo = asset('assets/site/logo-128.png');
    // Card markup (image URL, tel:/wa.me numbers) lives in partials/business-card.blade.php.

    // URL for this page with some filters changed (always back to page 1).
    $filterUrl = fn (array $changes) => route('businesses', array_filter(array_merge(
        ['q' => $q ?: null, 'category' => $activeCategory?->id, 'sort' => $sort !== 'popular' ? $sort : null],
        $changes
    ), fn ($v) => $v !== null && $v !== ''));

    $total = $businesses->total();
@endphp

@section('content')
<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="crumbs" aria-label="{{ __('home.bizp_crumbs') }}">
            <a href="{{ url('/') }}">{{ __('home.nav_home') }}</a>
            <svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg>
            <span aria-current="page">{{ __('home.nav_businesses') }}</span>
        </nav>
        <h1>{{ __('home.bizp_title') }}</h1>
        <p class="page-lead">{{ __('home.bizp_sub') }}</p>

        <form class="dir-search" method="GET" action="{{ route('businesses') }}" role="search">
            @if ($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory->id }}">@endif
            @if ($sort !== 'popular')<input type="hidden" name="sort" value="{{ $sort }}">@endif
            <svg class="icon" aria-hidden="true"><use href="#i-search"/></svg>
            <input type="search" name="q" value="{{ $q }}" maxlength="80" enterkeyhint="search"
                   placeholder="{{ __('home.bizp_search_ph') }}" aria-label="{{ __('home.bizp_search_ph') }}">
            <button type="submit">{{ __('home.search_btn') }}</button>
        </form>
    </div>
</section>

<div class="container dir">
    {{-- Category chips (scroll sideways on phones) --}}
    <nav class="cat-bar" aria-label="{{ __('home.bizp_categories') }}">
        <a href="{{ $filterUrl(['category' => null]) }}" class="cat-chip {{ $activeCategory ? '' : 'is-on' }}" @if (!$activeCategory) aria-current="true" @endif>
            {{ __('home.bizp_all') }} <span>{{ number_format($totalAll) }}</span>
        </a>
        @foreach ($categories as $cat)
            <a href="{{ $filterUrl(['category' => $cat->id]) }}" class="cat-chip {{ $activeCategory?->id === $cat->id ? 'is-on' : '' }}" @if ($activeCategory?->id === $cat->id) aria-current="true" @endif>
                {{ $cat->name }} <span>{{ $cat->businesses_count }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Result count + sort --}}
    <div class="dir-bar">
        <p class="dir-count" role="status">
            <span><b>{{ number_format($total) }}</b> {{ __('home.bizp_count') }}
            @if ($q !== '') {{ __('home.bizp_for') }} “{{ $q }}”@endif
            @if ($activeCategory) · {{ $activeCategory->name }}@endif</span>
            @if ($q !== '' || $activeCategory)
                <a class="dir-clear" href="{{ route('businesses') }}">{{ __('home.bizp_clear') }}</a>
            @endif
        </p>
        <form class="dir-sort" method="GET" action="{{ route('businesses') }}">
            @if ($q !== '')<input type="hidden" name="q" value="{{ $q }}">@endif
            @if ($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory->id }}">@endif
            <label for="dir-sort">{{ __('home.bizp_sort') }}</label>
            <select id="dir-sort" name="sort" onchange="this.form.submit()">
                @foreach ($sorts as $s)
                    <option value="{{ $s }}" @selected($sort === $s)>{{ __('home.bizp_sort_' . $s) }}</option>
                @endforeach
            </select>
            <noscript><button type="submit">{{ __('home.search_btn') }}</button></noscript>
        </form>
    </div>

    @if ($businesses->count())
        @php
            // Infinite scroll (progressive enhancement) only when starting from page 1; a shared ?page=N link keeps the pager.
            $infinite = $businesses->onFirstPage() && $businesses->hasMorePages();
        @endphp
        <ul class="dir-grid" @if ($infinite) data-next="{{ $nextUrl }}" @endif>
            @foreach ($businesses as $business)
                @include('partials.business-card', ['business' => $business])
            @endforeach
        </ul>

        @if ($businesses->hasPages())
            <nav class="pager" aria-label="{{ __('home.bizp_pages') }}">
                @if ($businesses->onFirstPage())
                    <span class="pager-btn is-off"><svg class="icon flip" aria-hidden="true"><use href="#i-chev-r"/></svg>{{ __('home.bizp_prev') }}</span>
                @else
                    <a class="pager-btn" href="{{ $prevUrl }}" rel="prev"><svg class="icon flip" aria-hidden="true"><use href="#i-chev-r"/></svg>{{ __('home.bizp_prev') }}</a>
                @endif
                <span class="pager-info">{{ __('home.bizp_page', ['page' => $businesses->currentPage(), 'last' => $businesses->lastPage()]) }}</span>
                @if ($businesses->hasMorePages())
                    <a class="pager-btn" href="{{ $nextUrl }}" rel="next">{{ __('home.bizp_next') }}<svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg></a>
                @else
                    <span class="pager-btn is-off">{{ __('home.bizp_next') }}<svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg></span>
                @endif
            </nav>
        @endif

        @if ($infinite)
            {{-- Infinite scroll: site.js shows this and hides the pager above; without JS the pager stays. --}}
            <div class="dir-more" data-dir-more hidden
                 data-showing="{{ __('home.bizp_showing', ['shown' => '__SHOWN__', 'total' => number_format($total)]) }}"
                 data-retry="{{ __('home.bizp_retry') }}" data-load="{{ __('home.bizp_load_more') }}">
                <span class="dir-sentinel" aria-hidden="true"></span>
                <p class="dir-loading" hidden><span class="dir-spin" aria-hidden="true"></span>{{ __('home.bizp_loading') }}</p>
                <button type="button" class="pager-btn dir-more-btn" hidden>{{ __('home.bizp_load_more') }}</button>
                <p class="dir-end" hidden>{{ __('home.bizp_end', ['count' => number_format($total)]) }}</p>
                <p class="sr-only" role="status" aria-live="polite" data-dir-status></p>
            </div>
        @endif
    @else
        <div class="empty">
            <img src="{{ $logo }}" alt="" width="48" height="48" loading="lazy">
            <p>{{ __('home.bizp_none') }}</p>
            @if ($q !== '' || $activeCategory)
                <a class="dir-clear" href="{{ route('businesses') }}">{{ __('home.bizp_clear') }}</a>
            @endif
        </div>
    @endif

    {{-- Invite owners to list their business: opens the "Add your business" popup on the home page --}}
    <aside class="dir-cta">
        <span class="dir-cta-badge" aria-hidden="true"><svg class="icon"><use href="#i-store"/></svg></span>
        <div class="dir-cta-text">
            <h2>{{ __('home.bizp_cta_title') }}</h2>
            <p>{{ __('home.bizp_cta_sub') }}</p>
            <ul class="dir-cta-points">
                <li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>{{ __('home.bizp_cta_p1') }}</li>
                <li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>{{ __('home.bizp_cta_p2') }}</li>
                <li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>{{ __('home.bizp_cta_p3') }}</li>
            </ul>
        </div>
        <a class="dir-cta-btn" href="{{ url('/') }}#add-business"><svg class="icon" aria-hidden="true"><use href="#i-plus"/></svg>{{ __('home.tile_add') }}</a>
    </aside>
</div>
@endsection
