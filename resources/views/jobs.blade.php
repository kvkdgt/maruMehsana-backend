@extends('layouts.site')

{{-- HtmlString: the layout escapes title/description itself, so don't let @section escape them a second time. --}}
@section('title', new \Illuminate\Support\HtmlString(__('home.jbl_meta_title')))
@section('description', new \Illuminate\Support\HtmlString(__('home.jbl_meta_desc')))
@php
    // Page 1 is the clean URL (no ?page=1); used by the pager and the rel prev/next links below.
    $prevUrl = $jobs->onFirstPage() ? null
        : ($jobs->currentPage() === 2 ? request()->fullUrlWithoutQuery('page') : $jobs->previousPageUrl());
    $nextUrl = $jobs->nextPageUrl();

    // Canonical: each page of a job type points to itself; search text and sort order
    // are just views of the same list, so they are left out.
    $canonicalUrl = route('jobs', array_filter([
        'type' => $type !== 'all' ? $type : null,
        'page' => $jobs->currentPage() > 1 ? $jobs->currentPage() : null,
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
    $playStore = 'https://play.google.com/store/apps/details?id=com.MaruMehsana';
    $filtered = $q !== '' || $type !== 'all';

    // URL for this page with some options changed (always back to page 1).
    $filterUrl = fn (array $changes) => route('jobs', array_filter(array_merge(
        [
            'q'        => $q !== '' ? $q : null,
            'type'     => $type !== 'all' ? $type : null,
            'sort'     => $sort !== 'newest' ? $sort : null,
        ],
        $changes
    ), fn ($v) => $v !== null && $v !== ''));
@endphp

@section('content')
<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="crumbs" aria-label="{{ __('home.bizp_crumbs') }}">
            <a href="{{ url('/') }}">{{ __('home.nav_home') }}</a>
            <svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg>
            <span aria-current="page">{{ __('home.nav_jobs') }}</span>
        </nav>
        <h1>{{ __('home.jbl_title') }}</h1>
        <p class="page-lead">{{ __('home.jbl_sub') }}</p>

        <form class="dir-search" method="GET" action="{{ route('jobs') }}" role="search">
            @if ($type !== 'all')<input type="hidden" name="type" value="{{ $type }}">@endif
            @if ($sort !== 'newest')<input type="hidden" name="sort" value="{{ $sort }}">@endif
            <svg class="icon" aria-hidden="true"><use href="#i-search"/></svg>
            <input type="search" name="q" value="{{ $q }}" maxlength="80" enterkeyhint="search"
                   placeholder="{{ __('home.jbl_search_ph') }}" aria-label="{{ __('home.jbl_search_ph') }}">
            <button type="submit">{{ __('home.search_btn') }}</button>
        </form>
    </div>
</section>

<div class="container dir">
    {{-- Job type chips (types with no jobs are hidden) --}}
    <nav class="cat-bar" aria-label="{{ __('home.jbl_types') }}">
        <a href="{{ $filterUrl(['type' => null]) }}" class="cat-chip {{ $type === 'all' ? 'is-on' : '' }}" @if ($type === 'all') aria-current="true" @endif>
            {{ __('home.jbl_type_all') }} <span>{{ number_format($counts['all']) }}</span>
        </a>
        @foreach ($types as $t)
            <a href="{{ $filterUrl(['type' => $t]) }}" class="cat-chip {{ $type === $t ? 'is-on' : '' }}" @if ($type === $t) aria-current="true" @endif>
                {{ __('home.jbl_type_' . $t) }} <span>{{ number_format($counts[$t]) }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Result count + sort --}}
    <div class="dir-bar">
        <p class="dir-count" role="status">
            <span><b>{{ number_format($total) }}</b> {{ __('home.jbl_count') }}
            @if ($q !== '') {{ __('home.bizp_for') }} “{{ $q }}”@endif
            @if ($type !== 'all') · {{ __('home.jbl_type_' . $type) }}@endif</span>
            @if ($filtered)
                <a class="dir-clear" href="{{ route('jobs') }}">{{ __('home.jbl_clear') }}</a>
            @endif
        </p>
        <form class="dir-sort jbl-opts" method="GET" action="{{ route('jobs') }}">
            @if ($q !== '')<input type="hidden" name="q" value="{{ $q }}">@endif
            @if ($type !== 'all')<input type="hidden" name="type" value="{{ $type }}">@endif
            <label for="dir-sort">{{ __('home.bizp_sort') }}</label>
            <select id="dir-sort" name="sort" onchange="this.form.submit()">
                @foreach ($sorts as $s)
                    <option value="{{ $s }}" @selected($sort === $s)>{{ __('home.jbl_sort_' . $s) }}</option>
                @endforeach
            </select>
            <noscript><button type="submit">{{ __('home.search_btn') }}</button></noscript>
        </form>
    </div>

    @if ($jobs->count())
        @php
            // Infinite scroll (progressive enhancement) only when starting from page 1; a shared ?page=N link keeps the pager.
            $infinite = $jobs->onFirstPage() && $jobs->hasMorePages();
        @endphp
        <ul class="dir-grid jbl-grid" @if ($infinite) data-next="{{ $nextUrl }}" @endif>
            @foreach ($jobs as $job)
                @include('partials.job-card', ['job' => $job, 'lang' => $lang])
            @endforeach
        </ul>

        @if ($jobs->hasPages())
            <nav class="pager" aria-label="{{ __('home.bizp_pages') }}">
                @if ($jobs->onFirstPage())
                    <span class="pager-btn is-off"><svg class="icon flip" aria-hidden="true"><use href="#i-chev-r"/></svg>{{ __('home.bizp_prev') }}</span>
                @else
                    <a class="pager-btn" href="{{ $prevUrl }}" rel="prev"><svg class="icon flip" aria-hidden="true"><use href="#i-chev-r"/></svg>{{ __('home.bizp_prev') }}</a>
                @endif
                <span class="pager-info">{{ __('home.bizp_page', ['page' => $jobs->currentPage(), 'last' => $jobs->lastPage()]) }}</span>
                @if ($jobs->hasMorePages())
                    <a class="pager-btn" href="{{ $nextUrl }}" rel="next">{{ __('home.bizp_next') }}<svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg></a>
                @else
                    <span class="pager-btn is-off">{{ __('home.bizp_next') }}<svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg></span>
                @endif
            </nav>
        @endif

        @if ($infinite)
            {{-- Infinite scroll: site.js shows this and hides the pager above; without JS the pager stays. --}}
            <div class="dir-more" data-dir-more hidden
                 data-showing="{{ __('home.jbl_showing', ['shown' => '__SHOWN__', 'total' => number_format($total)]) }}"
                 data-retry="{{ __('home.bizp_retry') }}" data-load="{{ __('home.bizp_load_more') }}">
                <span class="dir-sentinel" aria-hidden="true"></span>
                <p class="dir-loading" hidden><span class="dir-spin" aria-hidden="true"></span>{{ __('home.jbl_loading') }}</p>
                <button type="button" class="pager-btn dir-more-btn" hidden>{{ __('home.bizp_load_more') }}</button>
                <p class="dir-end" hidden>{{ __('home.jbl_end', ['count' => number_format($total)]) }}</p>
                <p class="sr-only" role="status" aria-live="polite" data-dir-status></p>
            </div>
        @endif
    @else
        <div class="empty">
            <img src="{{ $logo }}" alt="" width="48" height="48" loading="lazy">
            <p>{{ $filtered ? __('home.jbl_none') : __('home.empty_jobs') }}</p>
            @if ($filtered)
                <a class="dir-clear" href="{{ route('jobs') }}">{{ __('home.jbl_clear') }}</a>
            @endif
        </div>
    @endif

    {{-- Employers post jobs from the app --}}
    <aside class="dir-cta">
        <span class="dir-cta-badge" aria-hidden="true"><svg class="icon"><use href="#i-briefcase"/></svg></span>
        <div class="dir-cta-text">
            <h2>{{ __('home.jbl_cta_title') }}</h2>
            <p>{{ __('home.jbl_cta_sub') }}</p>
            <ul class="dir-cta-points">
                <li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>{{ __('home.jbl_cta_p1') }}</li>
                <li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>{{ __('home.jbl_cta_p2') }}</li>
                <li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>{{ __('home.jbl_cta_p3') }}</li>
            </ul>
        </div>
        <a class="dir-cta-btn" href="{{ $playStore }}" target="_blank" rel="noopener"><svg class="icon" aria-hidden="true"><use href="#i-play"/></svg>{{ __('home.jbl_cta_btn') }}</a>
    </aside>
</div>
@endsection
