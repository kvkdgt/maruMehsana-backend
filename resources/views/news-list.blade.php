@extends('layouts.site')

@section('title', __('home.nwl_meta_title'))
@section('description', __('home.nwl_meta_desc'))
@php
    // Page 1 is the clean URL (no ?page=1); used by the pager and the rel prev/next links below.
    $prevUrl = $articles->onFirstPage() ? null
        : ($articles->currentPage() === 2 ? request()->fullUrlWithoutQuery('page') : $articles->previousPageUrl());
    $nextUrl = $articles->nextPageUrl();

    // Canonical: each page of a filter (Mehsana / featured) points to itself; search text and sort
    // order are just views of the same list, so they are left out.
    $canonicalUrl = route('news', array_filter([
        'filter' => $filter !== 'all' ? $filter : null,
        'page'   => $articles->currentPage() > 1 ? $articles->currentPage() : null,
        'lang'   => $lang === 'en' ? 'en' : null,
    ]));
@endphp
@section('canonical', $canonicalUrl)
@push('head')
    <link rel="alternate" type="application/rss+xml" title="{{ __('home.nwl_rss_title') }}" href="{{ route('news.feed') }}">
    @if ($prevUrl)<link rel="prev" href="{{ $prevUrl }}">@endif
    @if ($nextUrl)<link rel="next" href="{{ $nextUrl }}">@endif
@endpush

@php
    $logo = asset('assets/site/logo-128.png');
    $filtered = $q !== '' || $filter !== 'all';

    // URL for this page with some options changed (always back to page 1).
    $filterUrl = fn (array $changes) => route('news', array_filter(array_merge(
        ['q' => $q !== '' ? $q : null, 'filter' => $filter !== 'all' ? $filter : null, 'sort' => $sort !== 'newest' ? $sort : null],
        $changes
    ), fn ($v) => $v !== null && $v !== ''));
@endphp

@section('content')
<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="crumbs" aria-label="{{ __('home.bizp_crumbs') }}">
            <a href="{{ url('/') }}">{{ __('home.nav_home') }}</a>
            <svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg>
            <span aria-current="page">{{ __('home.nav_news') }}</span>
        </nav>
        <h1>{{ __('home.nwl_title') }}</h1>
        <p class="page-lead">{{ __('home.nwl_sub') }}</p>

        <form class="dir-search" method="GET" action="{{ route('news') }}" role="search">
            @if ($filter !== 'all')<input type="hidden" name="filter" value="{{ $filter }}">@endif
            @if ($sort !== 'newest')<input type="hidden" name="sort" value="{{ $sort }}">@endif
            <svg class="icon" aria-hidden="true"><use href="#i-search"/></svg>
            <input type="search" name="q" value="{{ $q }}" maxlength="80" enterkeyhint="search"
                   placeholder="{{ __('home.nwl_search_ph') }}" aria-label="{{ __('home.nwl_search_ph') }}">
            <button type="submit">{{ __('home.search_btn') }}</button>
        </form>
    </div>
</section>

<div class="container dir">
    {{-- Filter chips --}}
    <nav class="cat-bar" aria-label="{{ __('home.nwl_filters') }}">
        @foreach ($filters as $f)
            <a href="{{ $filterUrl(['filter' => $f === 'all' ? null : $f]) }}" class="cat-chip {{ $filter === $f ? 'is-on' : '' }}" @if ($filter === $f) aria-current="true" @endif>
                {{ __('home.nwl_filter_' . $f) }} <span>{{ number_format($counts[$f]) }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Result count + sort --}}
    <div class="dir-bar">
        <p class="dir-count" role="status">
            <span><b>{{ number_format($total) }}</b> {{ __('home.nwl_count') }}
            @if ($q !== '') {{ __('home.bizp_for') }} “{{ $q }}”@endif
            @if ($filter !== 'all') · {{ __('home.nwl_filter_' . $filter) }}@endif</span>
            @if ($filtered)
                <a class="dir-clear" href="{{ route('news') }}">{{ __('home.nwl_clear') }}</a>
            @endif
        </p>
        <form class="dir-sort" method="GET" action="{{ route('news') }}">
            @if ($q !== '')<input type="hidden" name="q" value="{{ $q }}">@endif
            @if ($filter !== 'all')<input type="hidden" name="filter" value="{{ $filter }}">@endif
            <label for="dir-sort">{{ __('home.bizp_sort') }}</label>
            <select id="dir-sort" name="sort" onchange="this.form.submit()">
                @foreach ($sorts as $s)
                    <option value="{{ $s }}" @selected($sort === $s)>{{ __('home.nwl_sort_' . $s) }}</option>
                @endforeach
            </select>
            <noscript><button type="submit">{{ __('home.search_btn') }}</button></noscript>
        </form>
    </div>

    @if ($lead)
        {{-- Lead story: newest featured article, page 1 of the plain list only --}}
        <section class="nwl-lead-wrap" aria-label="{{ __('home.nwl_lead_aria') }}">
            @include('partials.news-card', ['article' => $lead, 'lang' => $lang, 'isLead' => true])
        </section>
    @endif

    @if ($articles->count())
        @php
            // Infinite scroll (progressive enhancement) only when starting from page 1; a shared ?page=N link keeps the pager.
            $infinite = $articles->onFirstPage() && $articles->hasMorePages();
        @endphp
        <ul class="dir-grid nwl-grid" @if ($infinite) data-next="{{ $nextUrl }}" @endif>
            @foreach ($articles as $article)
                @include('partials.news-card', ['article' => $article, 'lang' => $lang])
            @endforeach
        </ul>

        @if ($articles->hasPages())
            <nav class="pager" aria-label="{{ __('home.bizp_pages') }}">
                @if ($articles->onFirstPage())
                    <span class="pager-btn is-off"><svg class="icon flip" aria-hidden="true"><use href="#i-chev-r"/></svg>{{ __('home.bizp_prev') }}</span>
                @else
                    <a class="pager-btn" href="{{ $prevUrl }}" rel="prev"><svg class="icon flip" aria-hidden="true"><use href="#i-chev-r"/></svg>{{ __('home.bizp_prev') }}</a>
                @endif
                <span class="pager-info">{{ __('home.bizp_page', ['page' => $articles->currentPage(), 'last' => $articles->lastPage()]) }}</span>
                @if ($articles->hasMorePages())
                    <a class="pager-btn" href="{{ $nextUrl }}" rel="next">{{ __('home.bizp_next') }}<svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg></a>
                @else
                    <span class="pager-btn is-off">{{ __('home.bizp_next') }}<svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg></span>
                @endif
            </nav>
        @endif

        @if ($infinite)
            {{-- Infinite scroll: site.js shows this and hides the pager above; without JS the pager stays. --}}
            <div class="dir-more" data-dir-more hidden
                 data-showing="{{ __('home.nwl_showing', ['shown' => '__SHOWN__', 'total' => number_format($total)]) }}"
                 data-retry="{{ __('home.bizp_retry') }}" data-load="{{ __('home.bizp_load_more') }}">
                <span class="dir-sentinel" aria-hidden="true"></span>
                <p class="dir-loading" hidden><span class="dir-spin" aria-hidden="true"></span>{{ __('home.nwl_loading') }}</p>
                <button type="button" class="pager-btn dir-more-btn" hidden>{{ __('home.bizp_load_more') }}</button>
                <p class="dir-end" hidden>{{ __('home.nwl_end', ['count' => number_format($total)]) }}</p>
                <p class="sr-only" role="status" aria-live="polite" data-dir-status></p>
            </div>
        @endif
    @elseif (!$lead)
        <div class="empty">
            <img src="{{ $logo }}" alt="" width="48" height="48" loading="lazy">
            <p>{{ $filtered ? __('home.nwl_none') : __('home.empty_news') }}</p>
            @if ($filtered)
                <a class="dir-clear" href="{{ route('news') }}">{{ __('home.nwl_clear') }}</a>
            @endif
        </div>
    @endif
</div>
@endsection
