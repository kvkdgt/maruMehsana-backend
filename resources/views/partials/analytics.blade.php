{{--
    Google Analytics 4 for the public website. Included in the <head> of layouts/site and privacy.
    Outputs nothing unless services.google_analytics.id is set AND .enabled is true.

    Child views can set (in their top php block; extends passes them on to the layout):
      $gaPage   = ['content_id' => ..., 'content_category' => ..., ...]   merged over the defaults below
      $gaSearch = ['term' => $q, 'location' => 'businesses', 'count' => 12] on list pages when q is not empty
    Never put names, phone numbers or e-mails in these: search terms and page_location are redacted here,
    but other values are sent as given.
--}}
@php
    $gaCfg = config('services.google_analytics', []);
    $gaId  = trim((string) ($gaCfg['id'] ?? ''));
    $gaOn  = $gaId !== '' && !empty($gaCfg['enabled']);
@endphp
@if ($gaOn)
@php
    // Emails -> [email]; runs of 7+ digits (spaces/dashes allowed) -> [num]; max 100 chars.
    $gaRedact = function ($s) {
        $s = (string) $s;
        $s = preg_replace('/[^\s@]+@[^\s@]+/u', '[email]', $s) ?? '';
        $s = preg_replace('/\+?\d(?:[\s\-]*\d){6,}/u', '[num]', $s) ?? '';

        return mb_substr(trim($s), 0, 100);
    };

    $gaRoute = optional(request()->route())->getName();
    $gaPath  = trim(request()->path(), '/');
    $gaType  = [
        'businesses'      => 'business_list',
        'businesses.show' => 'business_detail',
        'places'          => 'place_list',
        'places.show'     => 'place_detail',
        'news'            => 'news_list',
        'news.show'       => 'news_detail',
        'jobs'            => 'job_list',
        'jobs.show'       => 'job_detail',
    ][$gaRoute] ?? ($gaPath === '' ? 'home' : ($gaPath === 'privacy-policy' ? 'privacy' : 'other'));

    $gaCtx = array_merge([
        'page_type'     => $gaType,
        'content_group' => $activeNav ?? ($gaType === 'privacy' ? 'legal' : 'home'),
        'site_language' => $lang ?? 'gu',
    ], isset($gaPage) && is_array($gaPage) ? $gaPage : []);
    $gaCtx = array_filter($gaCtx, fn ($v) => $v !== null && $v !== '');

    // page_location without raw search text (it can hold phone numbers or e-mails).
    $gaQuery = request()->query();
    if (isset($gaQuery['q']) && is_string($gaQuery['q'])) {
        $gaQuery['q'] = $gaRedact($gaQuery['q']);
    } else {
        unset($gaQuery['q']);
    }
    $gaQs = \Illuminate\Support\Arr::query($gaQuery);
    $gaLocation = url()->current() . ($gaQs !== '' ? '?' . str_replace(['%5B', '%5D'], ['[', ']'], $gaQs) : '');

    $gaConfig = [
        'page_location'                    => $gaLocation,
        'allow_google_signals'             => false,
        'allow_ad_personalization_signals' => false,
    ];
    // Only add debug_mode when wanted: any value (even false) turns DebugView on.
    if (!empty($gaCfg['debug']) || request()->query('ga_debug') === '1') {
        $gaConfig['debug_mode'] = true;
    }

    $gaSearchEvent = null;
    if (isset($gaSearch) && is_array($gaSearch) && trim((string) ($gaSearch['term'] ?? '')) !== '') {
        $gaSearchEvent = [
            'search_term'     => $gaRedact($gaSearch['term']),
            'search_location' => $gaSearch['location'] ?? $gaType,
            'results_count'   => (int) ($gaSearch['count'] ?? 0),
        ];
    }

@endphp
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    // Consent (India DPDP Act): no analytics cookies until the visitor accepts in the cookie banner
    // (partials/consent.blade.php). Until then GA4 Consent Mode sends cookieless pings only.
    // Advertising storage is always denied. The choice is kept in the first-party cookie mm_consent.
    (function () {
        var m = document.cookie.match(/(?:^|;\s*)mm_consent=(granted|denied)/);
        if (!m) { try { var ls = localStorage.getItem('mm_consent'); if (ls === 'granted' || ls === 'denied') m = [null, ls]; } catch (e) {} }
        gtag('consent', 'default', {
            ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied',
            analytics_storage: m && m[1] === 'granted' ? 'granted' : 'denied',
            wait_for_update: 500
        });
    })();
    window.MM_GA = @json($gaCtx);
    gtag('js', new Date());
    gtag('set', 'user_properties', {site_language: window.MM_GA.site_language || 'gu'});
    // page_referrer: the previous page on this site can be a search URL (?q=phone number), so mask it the same way.
    var gaRef = document.referrer;
    try {
        var gaRefUrl = new URL(gaRef);
        if (gaRefUrl.origin === location.origin && gaRefUrl.searchParams.has('q')) {
            gaRefUrl.searchParams.set('q', gaRefUrl.searchParams.get('q')
                .replace(/[^\s@]+@[^\s@]+\.[^\s@]+/g, '[email]')
                .replace(/(?:\d[\s.-]?){7,}/g, '[num]')
                .slice(0, 100));
            gaRef = gaRefUrl.toString();
        }
    } catch (e) { /* no or invalid referrer */ }
    gtag('config', @json($gaId), Object.assign(@json($gaConfig), gaRef ? {page_referrer: gaRef} : {}, window.MM_GA));
    @if ($gaSearchEvent)
    gtag('event', 'search', @json($gaSearchEvent));
    @if ($gaSearchEvent['results_count'] === 0)
    gtag('event', 'search_no_results', @json($gaSearchEvent));
    @endif
    @endif
    @if (($gaCtx['page_type'] ?? '') === 'not_found')
    gtag('event', 'page_not_found', {page_path: location.pathname});
    @endif
    </script>
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($gaId) }}"></script>
@endif
