@extends('layouts.site')

@section('title', __('home.plp_meta_title'))
@section('description', __('home.plp_meta_desc'))
@php
    // Page 1 is the clean URL (no ?page=1); used by the pager and the rel prev/next links below.
    $prevUrl = $places->onFirstPage() ? null
        : ($places->currentPage() === 2 ? request()->fullUrlWithoutQuery('page') : $places->previousPageUrl());
    $nextUrl = $places->nextPageUrl();

    // Canonical: search text and sort order are just views of the same list, so they are left out.
    $canonicalUrl = route('places', array_filter([
        'page' => $places->currentPage() > 1 ? $places->currentPage() : null,
        'lang' => $lang === 'en' ? 'en' : null,
    ]));
@endphp
@section('canonical', $canonicalUrl)
@push('head')
    @if ($prevUrl)<link rel="prev" href="{{ $prevUrl }}">@endif
    @if ($nextUrl)<link rel="next" href="{{ $nextUrl }}">@endif
@endpush

@php
    $logo = asset('assets/site/logo-128.png');
    $total = $places->total();
@endphp

@section('content')
<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="crumbs" aria-label="{{ __('home.bizp_crumbs') }}">
            <a href="{{ url('/') }}">{{ __('home.nav_home') }}</a>
            <svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg>
            <span aria-current="page">{{ __('home.nav_places') }}</span>
        </nav>
        <h1>{{ __('home.plp_title') }}</h1>
        <p class="page-lead">{{ __('home.plp_sub') }}</p>

        <form class="dir-search" method="GET" action="{{ route('places') }}" role="search">
            @if ($sort !== 'popular')<input type="hidden" name="sort" value="{{ $sort }}">@endif
            <svg class="icon" aria-hidden="true"><use href="#i-search"/></svg>
            <input type="search" name="q" value="{{ $q }}" maxlength="80" enterkeyhint="search"
                   placeholder="{{ __('home.plp_search_ph') }}" aria-label="{{ __('home.plp_search_ph') }}">
            <button type="submit">{{ __('home.search_btn') }}</button>
        </form>
    </div>
</section>

<div class="container dir">
    {{-- Result count + sort --}}
    <div class="dir-bar">
        <p class="dir-count" role="status">
            <span><b>{{ number_format($total) }}</b> {{ __('home.plp_count') }}
            @if ($q !== '') {{ __('home.bizp_for') }} “{{ $q }}”@endif</span>
            @if ($q !== '')
                <a class="dir-clear" href="{{ route('places') }}">{{ __('home.plp_clear') }}</a>
            @endif
        </p>
        <form class="dir-sort" method="GET" action="{{ route('places') }}">
            @if ($q !== '')<input type="hidden" name="q" value="{{ $q }}">@endif
            <label for="dir-sort">{{ __('home.bizp_sort') }}</label>
            <select id="dir-sort" name="sort" onchange="this.form.submit()">
                @foreach ($sorts as $s)
                    <option value="{{ $s }}" @selected($sort === $s)>{{ __('home.bizp_sort_' . $s) }}</option>
                @endforeach
            </select>
            <noscript><button type="submit">{{ __('home.search_btn') }}</button></noscript>
        </form>
    </div>

    @if ($places->count())
        @php
            // Infinite scroll (progressive enhancement) only when starting from page 1; a shared ?page=N link keeps the pager.
            $infinite = $places->onFirstPage() && $places->hasMorePages();
        @endphp
        <ul class="dir-grid plp-grid" @if ($infinite) data-next="{{ $nextUrl }}" @endif>
            @foreach ($places as $place)
                @include('partials.place-card', ['place' => $place])
            @endforeach
        </ul>

        @if ($places->hasPages())
            <nav class="pager" aria-label="{{ __('home.bizp_pages') }}">
                @if ($places->onFirstPage())
                    <span class="pager-btn is-off"><svg class="icon flip" aria-hidden="true"><use href="#i-chev-r"/></svg>{{ __('home.bizp_prev') }}</span>
                @else
                    <a class="pager-btn" href="{{ $prevUrl }}" rel="prev"><svg class="icon flip" aria-hidden="true"><use href="#i-chev-r"/></svg>{{ __('home.bizp_prev') }}</a>
                @endif
                <span class="pager-info">{{ __('home.bizp_page', ['page' => $places->currentPage(), 'last' => $places->lastPage()]) }}</span>
                @if ($places->hasMorePages())
                    <a class="pager-btn" href="{{ $nextUrl }}" rel="next">{{ __('home.bizp_next') }}<svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg></a>
                @else
                    <span class="pager-btn is-off">{{ __('home.bizp_next') }}<svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg></span>
                @endif
            </nav>
        @endif

        @if ($infinite)
            {{-- Infinite scroll: site.js shows this and hides the pager above; without JS the pager stays. --}}
            <div class="dir-more" data-dir-more hidden
                 data-showing="{{ __('home.plp_showing', ['shown' => '__SHOWN__', 'total' => number_format($total)]) }}"
                 data-retry="{{ __('home.bizp_retry') }}" data-load="{{ __('home.bizp_load_more') }}">
                <span class="dir-sentinel" aria-hidden="true"></span>
                <p class="dir-loading" hidden><span class="dir-spin" aria-hidden="true"></span>{{ __('home.plp_loading') }}</p>
                <button type="button" class="pager-btn dir-more-btn" hidden>{{ __('home.bizp_load_more') }}</button>
                <p class="dir-end" hidden>{{ __('home.plp_end', ['count' => number_format($total)]) }}</p>
                <p class="sr-only" role="status" aria-live="polite" data-dir-status></p>
            </div>
        @endif
    @else
        <div class="empty">
            <img src="{{ $logo }}" alt="" width="48" height="48" loading="lazy">
            <p>{{ $q !== '' ? __('home.plp_none') : __('home.empty_places') }}</p>
            @if ($q !== '')
                <a class="dir-clear" href="{{ route('places') }}">{{ __('home.plp_clear') }}</a>
            @endif
        </div>
    @endif
</div>
@endsection
