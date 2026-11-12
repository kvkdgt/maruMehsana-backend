@extends('layouts.site')

@php

    // ₹ amounts with Indian digit grouping (12,34,567).
    $inr = function ($n) {
        $s = (string) (int) $n;
        if (strlen($s) <= 3) {
            return $s;
        }
        $rest = substr($s, 0, -3);
        $rest = strrev(implode(',', str_split(strrev($rest), 2)));

        return $rest . ',' . substr($s, -3);
    };
    $salaryText = function ($j) use ($inr) {
        $min = (int) $j->salary_min;
        $max = (int) $j->salary_max;
        if ($j->salary_type === 'not_disclosed' || (!$min && !$max)) {
            return null;
        }
        $per = ['monthly' => 'per_month', 'yearly' => 'per_year', 'hourly' => 'per_hour'][$j->salary_type] ?? null;
        $range = ($min && $max && $min !== $max)
            ? '₹' . $inr(min($min, $max)) . ' – ₹' . $inr(max($min, $max))
            : '₹' . $inr($max ?: $min);

        return $range . ($per ? ' / ' . __('home.' . $per) : '');
    };
    $typeLabel = fn ($t) => \Illuminate\Support\Facades\Lang::has('home.jbd_type_' . $t) ? __('home.jbd_type_' . $t) : \Illuminate\Support\Str::headline((string) $t);

    $isFilled = $job->status === 'filled';
    $company = trim((string) $job->company_name);
    $location = trim((string) $job->location);
    $salary = $salaryText($job);
    $posted = $job->created_at;
    $expires = $job->expires_at;
    $daysLeft = $expires ? now()->startOfDay()->diffInDays($expires->copy()->startOfDay(), false) : null;
    $thumb = $job->thumbnail
        ? (\Illuminate\Support\Str::startsWith($job->thumbnail, ['http://', 'https://']) ? $job->thumbnail : asset('storage/' . ltrim($job->thumbnail, '/')))
        : null;

    // Contact details: the same ones the app's job detail shows (contact_name / contact_mobile / contact_email).
    $phone = preg_replace('/\D+/', '', (string) $job->contact_mobile);
    $waDigits = strlen($phone) === 10 ? '91' . $phone : $phone;
    $email = filter_var(trim((string) $job->contact_email), FILTER_VALIDATE_EMAIL) ?: null;
    $applyVia = $job->apply_via;
    $applyWa = $waDigits !== '' ? 'https://wa.me/' . $waDigits . '?text=' . rawurlencode(__('home.jbd_wa_text', ['title' => $job->title])) : null;
    $applyMail = $email ? 'mailto:' . $email . '?subject=' . rawurlencode(__('home.jbd_mail_subject', ['title' => $job->title])) : null;
    $applyButtons = [];
    if ($phone !== '') {
        $applyButtons['call'] = ['href' => 'tel:' . $phone, 'label' => __('home.jbd_call'), 'class' => 'jbd-btn--call'];
    }
    if ($applyWa) {
        $applyButtons['whatsapp'] = ['href' => $applyWa, 'label' => 'WhatsApp', 'class' => 'jbd-btn--wa', 'external' => true];
    }
    if ($applyMail) {
        $applyButtons['email'] = ['href' => $applyMail, 'label' => __('home.jbd_email'), 'class' => 'jbd-btn--mail'];
    }
    // The poster's preferred way first (walk-in jobs lead with Call so people can confirm the timing).
    $first = $applyVia === 'walk_in' ? 'call' : $applyVia;
    if (isset($applyButtons[$first])) {
        $applyButtons = [$first => $applyButtons[$first]] + $applyButtons;
    }

    $bodyHtml = \App\Support\HtmlSanitizer::render($job->description);
    $reqHtml = \App\Support\HtmlSanitizer::render($job->requirements);
    $toPlain = function (?string $raw, string $html) {
        $plain = (string) $raw;
        if (\App\Support\HtmlSanitizer::isHtml($plain)) {
            // From the sanitized HTML: strip_tags alone would keep <script>/<style> text.
            $plain = html_entity_decode(strip_tags(str_replace('<', ' <', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return trim(preg_replace('/\s+/u', ' ', $plain));
    };
    $plainBody = $toPlain($job->description, $bodyHtml);

    $lead = __('home.jbd_meta_desc', ['title' => $job->title, 'company' => $company, 'location' => $location]);
    $desc = \Illuminate\Support\Str::limit(trim($lead . ($salary ? ' ' . $salary . '.' : '') . ' ' . $plainBody), 155);

    $webUrl = route('jobs.show', $job->id);                // shared link: canonical, Gujarati default
    $canonicalUrl = $webUrl . ($lang === 'en' ? '?lang=en' : '');
    $shareUrl = url('/job/' . $job->id);                    // app deep link
    $playUrl = 'https://play.google.com/store/apps/details?id=com.MaruMehsana';
    $shareTitle = $job->title . ($company !== '' ? ' - ' . $company : '');
    $waShareUrl = 'https://wa.me/?text=' . rawurlencode($shareTitle . ' ' . $webUrl);
    $fbUrl = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($webUrl);
    $xUrl = 'https://x.com/intent/tweet?text=' . rawurlencode($shareTitle) . '&url=' . rawurlencode($webUrl);

    $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG;

    // schema.org JobPosting only while the job is open: Google wants filled/expired postings removed from the markup.
    $jobLd = null;
    if (!$isFilled) {
        $employment = [
            'full_time'  => 'FULL_TIME',
            'part_time'  => 'PART_TIME',
            'freelance'  => 'CONTRACTOR',
            'contract'   => 'CONTRACTOR',
            'internship' => 'INTERN',
        ][$job->job_type] ?? 'OTHER';
        $ldDesc = $bodyHtml . ($reqHtml !== '' ? '<h3>' . e(__('home.jbd_requirements')) . '</h3>' . $reqHtml : '');
        $jobLd = [
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $job->title,
            'description' => $ldDesc !== '' ? $ldDesc : e($job->title),
            'url' => $canonicalUrl,
            'employmentType' => $employment,
            'hiringOrganization' => array_filter(['@type' => 'Organization', 'name' => $company !== '' ? $company : 'Maru Mehsana', 'logo' => $thumb]),
            'jobLocation' => [
                '@type' => 'Place',
                'address' => array_filter([
                    '@type' => 'PostalAddress',
                    'addressLocality' => $location,
                    'addressRegion' => 'Gujarat',
                    'addressCountry' => 'IN',
                ]),
            ],
            'directApply' => false,
        ];
        if ($posted) { $jobLd['datePosted'] = $posted->toIso8601String(); }
        if ($expires) { $jobLd['validThrough'] = $expires->copy()->endOfDay()->toIso8601String(); }
        if ((int) $job->vacancies_count > 0) { $jobLd['totalJobOpenings'] = (int) $job->vacancies_count; }
        $min = (int) $job->salary_min;
        $max = (int) $job->salary_max;
        $unit = ['monthly' => 'MONTH', 'yearly' => 'YEAR', 'hourly' => 'HOUR'][$job->salary_type] ?? null;
        if ($unit && ($min || $max)) {
            $value = ['@type' => 'QuantitativeValue', 'unitText' => $unit];
            if ($min && $max && $min !== $max) {
                $value['minValue'] = min($min, $max);
                $value['maxValue'] = max($min, $max);
            } else {
                $value['value'] = $max ?: $min;
            }
            $jobLd['baseSalary'] = ['@type' => 'MonetaryAmount', 'currency' => 'INR', 'value' => $value];
        }
    }
    $crumbsLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => __('home.nav_home'), 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => __('home.nav_jobs'), 'item' => route('jobs')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $job->title, 'item' => $canonicalUrl],
        ],
    ];
@endphp

{{-- HtmlString: the layout escapes title/description itself, so don't let @section escape them a second time. --}}
@section('title', new \Illuminate\Support\HtmlString($job->title . ($company !== '' ? ' - ' . $company : '') . ' | ' . __('home.nav_jobs') . ' | Maru Mehsana'))
@section('description', new \Illuminate\Support\HtmlString($desc))
@section('canonical', $canonicalUrl)

@if ($thumb)
    @section('og_image', new \Illuminate\Support\HtmlString($thumb))
@endif
@push('head')
    @if ($jobLd)
        <script type="application/ld+json">{!! json_encode($jobLd, $jsonFlags) !!}</script>
    @endif
    <script type="application/ld+json">{!! json_encode($crumbsLd, $jsonFlags) !!}</script>
@endpush

@section('content')
<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="crumbs" aria-label="{{ __('home.bizp_crumbs') }}">
            <a href="{{ url('/') }}">{{ __('home.nav_home') }}</a>
            <svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg>
            <a href="{{ route('jobs') }}">{{ __('home.nav_jobs') }}</a>
            <svg class="icon" aria-hidden="true"><use href="#i-chev-r"/></svg>
            <span aria-current="page" class="clamp-1">{{ $job->title }}</span>
        </nav>
        <h1 class="nwd-title">{{ $job->title }}</h1>
        @if ($company !== '')
            <p class="jbd-hero-co">{{ $company }}</p>
        @endif
    </div>
</section>

<div class="container bd">
    <article class="bd-main jbd-main">
        @if ($isFilled)
            <div class="jbd-filled" role="status">
                <svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>
                <div>
                    <b>{{ __('home.jbd_filled_title') }}</b>
                    <p>{{ __('home.jbd_filled_sub') }}</p>
                </div>
            </div>
        @endif

        <div class="jbd-head">
            <span class="jbd-logo" aria-hidden="true">
                <svg class="icon" aria-hidden="true"><use href="#i-briefcase"/></svg>
                @if ($thumb)
                    <img src="{{ $thumb }}" alt="" width="64" height="64" loading="eager" decoding="async" onerror="this.remove()">
                @endif
            </span>
            <div class="jbd-head-text">
                @if ($company !== '')<b class="jbd-co">{{ $company }}</b>@endif
                <div class="nwd-tags jbd-tags">
                    <span class="nwd-tag"><svg class="icon" aria-hidden="true"><use href="#i-briefcase"/></svg>{{ $typeLabel($job->job_type) }}</span>
                    @if ($location !== '')
                        <span class="nwd-tag"><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg>{{ $location }}</span>
                    @endif
                    @if ($isFilled)
                        <span class="nwd-tag jbd-tag--filled">{{ __('home.jbd_filled') }}</span>
                    @endif
                </div>
            </div>
        </div>

        <section class="bd-sec" aria-labelledby="jbd-facts-title">
            <h2 id="jbd-facts-title">{{ __('home.jbd_facts') }}</h2>
            <dl class="jbd-facts">
                <div class="jbd-fact jbd-fact--pay">
                    <dt>{{ __('home.jbd_salary') }}</dt>
                    <dd>{{ $salary ?: __('home.jbd_salary_nd') }}</dd>
                </div>
                @if ($job->experience_required)
                    <div class="jbd-fact"><dt>{{ __('home.jbd_experience') }}</dt><dd>{{ $job->experience_required }}</dd></div>
                @endif
                @if ((int) $job->vacancies_count > 0)
                    <div class="jbd-fact"><dt>{{ __('home.jbd_openings') }}</dt><dd>{{ number_format((int) $job->vacancies_count) }}</dd></div>
                @endif
                @if ($job->education_required)
                    <div class="jbd-fact"><dt>{{ __('home.jbd_education') }}</dt><dd>{{ $job->education_required }}</dd></div>
                @endif
                @if (in_array($job->gender_preference, ['male', 'female'], true))
                    <div class="jbd-fact"><dt>{{ __('home.jbd_gender') }}</dt><dd>{{ __('home.jbd_gender_' . $job->gender_preference) }}</dd></div>
                @endif
                @if ($posted)
                    <div class="jbd-fact">
                        <dt>{{ __('home.jbd_posted') }}</dt>
                        <dd><time datetime="{{ $posted->toDateString() }}">{{ $posted->locale($lang)->isoFormat('D MMM YYYY') }}</time></dd>
                    </div>
                @endif
                @if ($expires && !$isFilled)
                    <div class="jbd-fact">
                        <dt>{{ __('home.jbd_closes') }}</dt>
                        <dd>
                            <time datetime="{{ $expires->toDateString() }}">{{ $expires->copy()->locale($lang)->isoFormat('D MMM YYYY') }}</time>
                            <span class="jbd-left{{ $daysLeft <= 3 ? ' is-soon' : '' }}">
                                @if ($daysLeft <= 0)
                                    {{ __('home.jbd_closes_today') }}
                                @elseif ($daysLeft === 1)
                                    {{ __('home.jbd_closes_tomorrow') }}
                                @else
                                    {{ __('home.jbd_closes_in', ['count' => $daysLeft]) }}
                                @endif
                            </span>
                        </dd>
                    </div>
                @endif
            </dl>
        </section>

        @unless ($isFilled)
            <section class="jbd-apply" id="apply" aria-labelledby="jbd-apply-title">
                <h2 id="jbd-apply-title">{{ __('home.jbd_apply') }}</h2>
                @if ($applyVia === 'walk_in')
                    <p class="jbd-walkin"><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg><span><b>{{ __('home.jbd_walk_in') }}</b> {{ __('home.jbd_walk_in_sub', ['location' => $location]) }}</span></p>
                @else
                    <p>{{ __('home.jbd_apply_sub') }}</p>
                @endif
                @if (trim((string) $job->contact_name) !== '')
                    <p class="jbd-contact">{{ __('home.jbd_contact') }}: <b>{{ $job->contact_name }}</b></p>
                @endif
                <div class="jbd-apply-btns">
                    @foreach ($applyButtons as $key => $btn)
                        <a class="jbd-btn {{ $btn['class'] }}" href="{{ $btn['href'] }}" @if (!empty($btn['external'])) target="_blank" rel="noopener nofollow" @else rel="nofollow" @endif>
                            @if ($key === 'call')
                                <svg class="icon" aria-hidden="true"><use href="#i-phone"/></svg>
                            @elseif ($key === 'whatsapp')
                                <svg class="icon" aria-hidden="true"><use href="#i-chat"/></svg>
                            @else
                                <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            @endif
                            <span>{{ $btn['label'] }}</span>
                        </a>
                    @endforeach
                    <a class="jbd-btn jbd-btn--app" href="{{ $shareUrl }}" rel="noopener">
                        <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg><span>{{ __('home.bizd_app_open') }}</span>
                    </a>
                </div>
            </section>
        @endunless

        @if ($bodyHtml !== '')
            <section class="bd-sec">
                <h2>{{ __('home.jbd_about') }}</h2>
                <div class="nwd-body jbd-body">{!! $bodyHtml !!}</div>
            </section>
        @endif
        @if ($reqHtml !== '')
            <section class="bd-sec">
                <h2>{{ __('home.jbd_requirements') }}</h2>
                <div class="nwd-body jbd-body">{!! $reqHtml !!}</div>
            </section>
        @endif

        <section class="nwd-share" aria-labelledby="nwd-share-title">
            <h2 id="nwd-share-title">{{ __('home.jbd_share') }}</h2>
            <div class="nwd-share-btns">
                <a class="nwd-sb nwd-sb--wa" href="{{ $waShareUrl }}" target="_blank" rel="noopener nofollow" aria-label="{{ __('home.nwd_share_on', ['site' => 'WhatsApp']) }}">
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
            </div>
            <p class="nwd-copy-msg" role="status" aria-live="polite" hidden data-ok="{{ __('home.nwd_copied') }}" data-fail="{{ __('home.nwd_copy_fail') }}"></p>
        </section>
    </article>

    <aside class="bd-side">
        <section class="bd-card jbd-safe">
            <h2>
                <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 6v6c0 4.5 3.4 8.3 8 9 4.6-.7 8-4.5 8-9V6l-8-3Z"/><path d="M12 8v5M12 16.5v.01"/></svg>
                {{ __('home.jbd_safety_title') }}
            </h2>
            <p>{{ __('home.jbd_safety') }}</p>
            <a class="jbd-report" href="{{ $shareUrl }}" rel="nofollow">{{ __('home.jbd_report') }}</a>
        </section>

        <section class="bd-card bd-app">
            <div class="bd-app-top">
                <img class="bd-app-icon" src="{{ asset('assets/site/logo-128.png') }}" alt="" width="56" height="56" loading="lazy">
                <div>
                    <h2>{{ __('home.bizd_app_title') }}</h2>
                    <p>{{ __('home.jbd_app_sub') }}</p>
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

        <a class="bd-back" href="{{ route('jobs') }}"><svg class="icon flip" aria-hidden="true"><use href="#i-chev-r"/></svg>{{ __('home.jbd_back') }}</a>
    </aside>
</div>

@if ($similar->count())
    <section class="container bd-similar">
        <h2>{{ __('home.jbd_similar') }}</h2>
        <ul class="job-list jbd-similar">
            @foreach ($similar as $item)
                @php $pay = $salaryText($item); @endphp
                <li>
                    <a class="job-row" href="{{ route('jobs.show', $item->id) }}">
                        <span class="job-ic"><svg class="icon" aria-hidden="true"><use href="#i-briefcase"/></svg></span>
                        <span class="job-text">
                            <span class="job-title clamp-1">{{ $item->title }}</span>
                            <span class="job-meta clamp-1"><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg>{{ $item->location }}@if ($item->location && $item->company_name) &middot; @endif{{ $item->company_name }}</span>
                            <span class="jbd-sim-meta">
                                <span class="jbd-sim-type">{{ $typeLabel($item->job_type) }}</span>
                                @if ($pay)<span class="job-pay">{{ $pay }}</span>@endif
                            </span>
                        </span>
                        <svg class="icon job-go" aria-hidden="true"><use href="#i-chev-r"/></svg>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
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
