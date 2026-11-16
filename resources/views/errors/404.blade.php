@extends('layouts.site')

@php
    // Rendered by the exception handler: for unknown URLs no controller (and no web middleware)
    // has run, so work out the language here: ?lang=, then the mm_lang cookie, then Gujarati.
    if (!isset($lang) || !in_array($lang, ['gu', 'en'], true)) {
        $lang = request()->query('lang');
        if (!in_array($lang, ['gu', 'en'], true)) {
            $lang = request()->cookie('mm_lang');
            if (is_string($lang) && !in_array($lang, ['gu', 'en'], true)) {
                // Cookie not decrypted yet (EncryptCookies is web middleware, which never ran).
                try {
                    $lang = \Illuminate\Cookie\CookieValuePrefix::validate(
                        'mm_lang', decrypt($lang, false), app('encrypter')->getKey()
                    );
                } catch (\Throwable $e) {
                    $lang = null;
                }
            }
        }
        if (!in_array($lang, ['gu', 'en'], true)) {
            $lang = 'gu';
        }
    }
    app()->setLocale($lang);
    \Carbon\Carbon::setLocale($lang);

    $activeNav = 'none';   // no header tab is active
    $gaPage = ['page_type' => 'not_found', 'content_group' => 'error', 'content_id' => null, 'content_category' => null];
    $nfLinks = [
        ['url' => route('businesses'), 'icon' => 'i-store', 'label' => __('home.nav_businesses')],
        ['url' => route('places'), 'icon' => 'i-pin', 'label' => __('home.nav_places')],
        ['url' => route('news'), 'icon' => 'i-news', 'label' => __('home.nav_news')],
        ['url' => route('jobs'), 'icon' => 'i-briefcase', 'label' => __('home.nav_jobs')],
    ];
@endphp

{{-- HtmlString: the layout escapes title/description itself, so don't let @section escape them a second time. --}}
@section('title', new \Illuminate\Support\HtmlString(__('home.nf_meta_title')))
@section('description', new \Illuminate\Support\HtmlString(__('home.nf_meta_desc')))
@push('head')
    <meta name="robots" content="noindex, follow">
@endpush

@section('content')
<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="crumbs" aria-label="{{ __('home.bizp_crumbs') }}">
            <a href="{{ url('/') }}">{{ __('home.nav_home') }}</a>
            <svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg>
            <span aria-current="page">{{ __('home.nf_eyebrow') }}</span>
        </nav>
        <h1>{{ __('home.nf_title') }}</h1>
        <p class="page-lead">{{ __('home.nf_sub') }}</p>
    </div>
</section>

<div class="container nf">
    <ul class="nf-links">
        @foreach ($nfLinks as $link)
            <li>
                <a class="pager-btn" href="{{ $link['url'] }}">
                    <svg class="icon" aria-hidden="true"><use href="#{{ $link['icon'] }}"/></svg>{{ $link['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
    <p class="nf-home">
        <a class="dir-clear" href="{{ url('/') }}">{{ __('home.nf_home') }}<svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
    </p>
</div>
@endsection
