@extends('layouts.site')

@php
    $agency = $article->agency;
    $agencyName = trim((string) optional($agency)->name);
    $agencyLogo = optional($agency)->logo_url;
    $hero = $article->image_url;
    $published = $article->created_at;
    $modified = $article->updated_at ?: $published;
    $wasUpdated = $published && $modified && $modified->gt($published->copy()->addHour());

    $bodyHtml = \App\Support\HtmlSanitizer::render($article->content);
    $plainBody = (string) $article->content;
    if (\App\Support\HtmlSanitizer::isHtml($plainBody)) {
        // From the sanitized body: strip_tags alone would keep <script>/<style> text.
        $plainBody = html_entity_decode(strip_tags(str_replace('<', ' <', $bodyHtml)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    $plainBody = trim(preg_replace('/\s+/u', ' ', $plainBody));
    $rawExcerpt = (string) $article->getRawOriginal('excerpt');
    if (\App\Support\HtmlSanitizer::isHtml($rawExcerpt)) {
        $rawExcerpt = html_entity_decode(strip_tags(\App\Support\HtmlSanitizer::clean($rawExcerpt)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    $rawExcerpt = trim(preg_replace('/\s+/u', ' ', $rawExcerpt));
    $desc = \Illuminate\Support\Str::limit($rawExcerpt !== '' ? $rawExcerpt : $plainBody, 155);

    $webUrl = $article->web_url;                       // shared link: canonical, Gujarati default
    $canonicalUrl = $webUrl . ($lang === 'en' ? '?lang=en' : '');
    $shareUrl = url('/news/' . $article->id);          // app deep link
    $playUrl = 'https://play.google.com/store/apps/details?id=com.MaruMehsana';
    $shareText = $article->title . ' ' . $webUrl;
    $waUrl = 'https://wa.me/?text=' . rawurlencode($shareText);
    $fbUrl = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($webUrl);
    $xUrl = 'https://x.com/intent/tweet?text=' . rawurlencode($article->title) . '&url=' . rawurlencode($webUrl);

    $when = function ($date) use ($lang) {
        return $date->diffInDays(now()) < 30 ? $date->locale($lang)->diffForHumans() : null;
    };

    $org = $agencyName !== ''
        ? array_filter(['@type' => 'Organization', 'name' => $agencyName, 'logo' => $agencyLogo])
        : ['@type' => 'Organization', 'name' => 'Maru Mehsana', 'url' => url('/'), 'logo' => asset('assets/site/logo-512.png')];
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => \Illuminate\Support\Str::limit($article->title, 105, '…'),
        'url' => $canonicalUrl,
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonicalUrl],
        'inLanguage' => $lang,
        'author' => $org,
        'publisher' => $org,
    ];
    if ($desc !== '') { $ld['description'] = $desc; }
    if ($hero) { $ld['image'] = [$hero]; }
    if ($published) { $ld['datePublished'] = $published->toIso8601String(); }
    if ($modified) { $ld['dateModified'] = $modified->toIso8601String(); }
    $crumbsLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => __('home.nav_home'), 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => __('home.nav_news'), 'item' => route('news')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $article->title, 'item' => $canonicalUrl],
        ],
    ];
    $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG;
@endphp

{{-- HtmlString: the layout escapes title/description itself, so don't let @section escape them a second time. --}}
@section('title', new \Illuminate\Support\HtmlString($article->title . ' | ' . __('home.nav_news') . ' | Maru Mehsana'))
@section('description', new \Illuminate\Support\HtmlString($desc !== '' ? $desc : __('home.nwd_meta_desc', ['title' => $article->title])))
@section('canonical', $canonicalUrl)

@if ($hero)
    @section('og_image', new \Illuminate\Support\HtmlString($hero))
@section('og_type', 'article')
@section('twitter_card', 'summary_large_image')
@endif
@push('head')
    @if ($hero)
        <meta property="og:image:alt" content="{{ $article->title }}">
    @endif
    @if ($published)
        <meta property="article:published_time" content="{{ $published->toIso8601String() }}">
    @endif
    @if ($modified)
        <meta property="article:modified_time" content="{{ $modified->toIso8601String() }}">
    @endif
    <meta property="article:section" content="{{ __('home.nav_news') }}">
    <script type="application/ld+json">{!! json_encode($ld, $jsonFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode($crumbsLd, $jsonFlags) !!}</script>
@endpush

@section('content')
<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="crumbs" aria-label="{{ __('home.bizp_crumbs') }}">
            <a href="{{ url('/') }}">{{ __('home.nav_home') }}</a>
            <svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg>
            <a href="{{ route('news') }}">{{ __('home.nav_news') }}</a>
            <svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg>
            <span aria-current="page" class="clamp-1">{{ $article->title }}</span>
        </nav>
        <h1 class="nwd-title">{{ $article->title }}</h1>
    </div>
</section>

<div class="container bd">
    <article class="bd-main nwd-main">
        <div class="nwd-meta">
            @if ($agencyName !== '')
                <span class="nwd-agency">
                    <span class="nwd-agency-logo" aria-hidden="true">
                        <span>{{ mb_strtoupper(mb_substr($agencyName, 0, 1)) }}</span>
                        @if ($agencyLogo)
                            <img src="{{ $agencyLogo }}" alt="" width="40" height="40" loading="eager" decoding="async" onerror="this.remove()">
                        @endif
                    </span>
                    <span class="nwd-agency-text">
                        <b class="clamp-1">{{ $agencyName }}</b>
                        @if ($published)
                            <span>
                                <time datetime="{{ $published->toIso8601String() }}">{{ $published->locale($lang)->isoFormat('D MMM YYYY, h:mm A') }}</time>
                                @if ($rel = $when($published))<span class="nwd-dot">{{ $rel }}</span>@endif
                            </span>
                        @endif
                    </span>
                </span>
            @endif
            <span class="nwd-tags">
                @if ($article->is_for_mehsana)
                    <span class="nwd-tag"><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg>{{ __('home.nwd_local') }}</span>
                @endif
            </span>
        </div>
        @if ($wasUpdated)
            <p class="nwd-updated">{{ __('home.nwd_updated') }}: <time datetime="{{ $modified->toIso8601String() }}">{{ $modified->locale($lang)->isoFormat('D MMM YYYY, h:mm A') }}</time></p>
        @endif

        @if ($hero)
            <figure class="bd-photo ph nwd-hero">
                <svg class="icon ph-ico" aria-hidden="true"><use href="#i-news"/></svg>
                <img class="bd-photo-bg" src="{{ $hero }}" alt="" aria-hidden="true" loading="eager" decoding="async" onerror="this.closest('.nwd-hero').remove()">
                <button type="button" class="bd-photo-btn" data-lb="0" aria-label="{{ __('home.bizd_zoom') }}">
                    <img src="{{ $hero }}" alt="{{ $article->title }}" width="1200" height="675" loading="eager" fetchpriority="high" decoding="async">
                </button>
            </figure>
        @endif

        @if ($rawExcerpt !== '' && !\Illuminate\Support\Str::contains($plainBody, \Illuminate\Support\Str::limit($rawExcerpt, 60, '')))
            <p class="nwd-lead">{{ $rawExcerpt }}</p>
        @endif

        @if ($bodyHtml !== '')
            <div class="nwd-body">{!! $bodyHtml !!}</div>
        @endif

        <section class="nwd-share" aria-labelledby="nwd-share-title">
            <h2 id="nwd-share-title">{{ __('home.nwd_share') }}</h2>
            <div class="nwd-share-btns">
                <a class="nwd-sb nwd-sb--wa" href="{{ $waUrl }}" target="_blank" rel="noopener nofollow" aria-label="{{ __('home.nwd_share_on', ['site' => 'WhatsApp']) }}">
                    <svg class="icon" aria-hidden="true"><use href="#i-chat"/></svg><span>WhatsApp</span>
                </a>
                <a class="nwd-sb nwd-sb--fb" href="{{ $fbUrl }}" target="_blank" rel="noopener nofollow" aria-label="{{ __('home.nwd_share_on', ['site' => 'Facebook']) }}">
                    <svg class="icon icon--fill" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8.5V6.8c0-.8.2-1.3 1.4-1.3H17V2.2c-.3 0-1.3-.2-2.5-.2-2.5 0-4.2 1.5-4.2 4.3v2.2H7.6V12h2.7v10H14V12h2.8l.4-3.5H14Z"/></svg><span>Facebook</span>
                </a>
                <a class="nwd-sb nwd-sb--x" href="{{ $xUrl }}" target="_blank" rel="noopener nofollow" aria-label="{{ __('home.nwd_share_on', ['site' => 'X']) }}">
                    <svg class="icon icon--fill" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.8 2.5h3.1l-6.8 7.8 8 10.6h-6.3l-4.9-6.4-5.6 6.4H2.2l7.3-8.3L1.8 2.5h6.4l4.4 5.9 5.2-5.9Zm-1.1 16.5h1.7L7.4 4.3H5.6L16.7 19Z"/></svg><span>X</span>
                </a>
                <button type="button" class="nwd-sb nwd-sb--copy" data-nwd-copy="{{ $webUrl }}">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"/></svg><span>{{ __('home.nwd_copy') }}</span>
                </button>
                <a class="nwd-sb nwd-sb--app" href="{{ $shareUrl }}" rel="noopener">
                    <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg><span>{{ __('home.bizd_app_open') }}</span>
                </a>
            </div>
            <p class="nwd-copy-msg" role="status" aria-live="polite" hidden data-ok="{{ __('home.nwd_copied') }}" data-fail="{{ __('home.nwd_copy_fail') }}"></p>
        </section>
    </article>

    <aside class="bd-side">
        <section class="bd-card bd-app">
            <div class="bd-app-top">
                <img class="bd-app-icon" src="{{ asset('assets/site/logo-128.png') }}" alt="" width="56" height="56" loading="lazy">
                <div>
                    <h2>{{ __('home.bizd_app_title') }}</h2>
                    <p>{{ __('home.nwd_app_sub') }}</p>
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

        @if ($mostRead->count())
            <section class="bd-card nwd-popular">
                <h2>{{ __('home.nwd_most_read') }}</h2>
                <ol class="nwd-pop-list">
                    @foreach ($mostRead as $i => $item)
                        <li>
                            <a href="{{ $item->web_url }}">
                                <span class="nwd-pop-n" aria-hidden="true">{{ $i + 1 }}</span>
                                <span class="clamp-2">{{ $item->title }}</span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        <a class="bd-back" href="{{ route('news') }}"><svg class="icon flip" aria-hidden="true"><use href="#i-chev-r"/></svg>{{ __('home.nwd_back') }}</a>
    </aside>
</div>

@if ($related->count())
    <section class="container bd-similar">
        <h2>{{ __('home.nwd_related') }}</h2>
        <ul class="place-grid nwd-grid">
            @foreach ($related as $item)
                <li>
                    <a class="place-card nwd-card" href="{{ $item->web_url }}">
                        <span class="media ph">
                            <svg class="icon ph-ico" aria-hidden="true"><use href="#i-news"/></svg>
                            @if ($item->image_url)
                                <img src="{{ $item->image_url }}" alt="" width="400" height="240" loading="lazy" decoding="async" onerror="this.remove()">
                            @endif
                        </span>
                        <span class="place-foot">
                            <span class="place-text">
                                <span class="place-name clamp-2">{{ $item->title }}</span>
                                <span class="news-meta">
                                    <svg class="icon" aria-hidden="true"><use href="#i-calendar"/></svg>
                                    @if ($item->created_at)
                                        <time datetime="{{ $item->created_at->toDateString() }}">{{ $item->created_at->locale($lang)->isoFormat('D MMM YYYY') }}</time>
                                    @endif
                                    @if ($item->agency)<span class="news-agency clamp-1">{{ $item->agency->name }}</span>@endif
                                </span>
                            </span>
                            <span class="go" aria-hidden="true"><svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
@endif

@if ($hero)
    <dialog class="lb single" id="lb" aria-label="{{ __('home.bizd_photo') }}" data-photos="{{ json_encode([$hero]) }}">
        <button type="button" class="lb-x" data-lb-close aria-label="{{ __('home.bizd_close') }}"><svg class="icon" aria-hidden="true"><use href="#i-close"/></svg></button>
        <button type="button" class="lb-nav lb-prev" data-lb-prev aria-label="{{ __('home.bizd_prev') }}"><svg class="icon flip" aria-hidden="true"><use href="#i-chev-r"/></svg></button>
        <img class="lb-img" alt="">
        <button type="button" class="lb-nav lb-next" data-lb-next aria-label="{{ __('home.bizd_next') }}"><svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg></button>
        <p class="lb-count" aria-live="polite"></p>
    </dialog>
@endif
@endsection

@push('scripts')
<script>
(function () {
  var btn = document.querySelector('[data-nwd-copy]');
  var msg = document.querySelector('.nwd-copy-msg');
  if (!btn || !msg) return;
  var timer;
  function say(ok) {
    msg.textContent = msg.getAttribute(ok ? 'data-ok' : 'data-fail');
    msg.hidden = false;
    msg.classList.toggle('is-fail', !ok);
    clearTimeout(timer);
    timer = setTimeout(function () { msg.hidden = true; }, 3500);
  }
  function legacyCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly', '');
    ta.style.position = 'fixed';
    ta.style.top = '-1000px';
    document.body.appendChild(ta);
    ta.select();
    var ok = false;
    try { ok = document.execCommand('copy'); } catch (e) {}
    document.body.removeChild(ta);
    return ok;
  }
  btn.addEventListener('click', function () {
    var url = btn.getAttribute('data-nwd-copy');
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(url).then(function () { say(true); }, function () { say(legacyCopy(url)); });
    } else {
      say(legacyCopy(url));
    }
  });
})();
</script>
@endpush
