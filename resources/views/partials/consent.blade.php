{{--
    Cookie consent banner (India DPDP Act 2023): shown only when Google Analytics is on.
    - Notice in the page language (Gujarati / English), with Accept and Decline as equal choices.
    - No analytics cookies before "Accept" (see partials/analytics.blade.php consent default).
    - Choice stored for 12 months in the first-party cookie mm_consent (granted|denied).
    - Any element with [data-consent-open] (footer "Cookie settings") reopens the banner, so consent
      can be withdrawn as easily as it was given. Declining also deletes existing _ga cookies.
    Self-contained (own styles + script) so it also works on the standalone privacy page.
--}}
@php
    $ccGa = config('services.google_analytics');
    $ccOn = !empty($ccGa['id']) && !empty($ccGa['enabled']);
    $ccEn = ($lang ?? app()->getLocale()) === 'en';
    $ccText = $ccEn
        ? ['title' => 'Cookies on Maru Mehsana', 'body' => 'With your permission we use Google Analytics cookies to count visits and see which pages are useful. We do not use advertising cookies, and we never send your name, phone number or e-mail. You can change this any time from "Cookie settings" at the bottom of the page.', 'more' => 'Privacy policy', 'accept' => 'Accept', 'decline' => 'Decline']
        : ['title' => 'મારું મહેસાણા પર કૂકીઝ', 'body' => 'તમારી મંજૂરીથી અમે મુલાકાતો ગણવા અને કયા પેજ ઉપયોગી છે તે જાણવા Google Analytics કૂકીઝનો ઉપયોગ કરીએ છીએ. અમે જાહેરાત માટેની કૂકીઝ વાપરતા નથી અને તમારું નામ, ફોન નંબર કે ઈ-મેલ ક્યારેય મોકલતા નથી. પેજના નીચે "કૂકી સેટિંગ્સ"માંથી તમે ગમે ત્યારે આ બદલી શકો છો.', 'more' => 'ગોપનીયતા નીતિ', 'accept' => 'સ્વીકારો', 'decline' => 'નકારો'];
@endphp
@if ($ccOn)
<div class="mm-cc" id="mm-cc" role="dialog" aria-modal="false" aria-labelledby="mm-cc-title" aria-describedby="mm-cc-body" hidden>
    <div class="mm-cc-in">
        <div class="mm-cc-text">
            <p class="mm-cc-title" id="mm-cc-title">{{ $ccText['title'] }}</p>
            <p class="mm-cc-body" id="mm-cc-body">{{ $ccText['body'] }} <a href="{{ url('/privacy-policy') }}#analytics">{{ $ccText['more'] }}</a></p>
        </div>
        <div class="mm-cc-btns">
            <button type="button" class="mm-cc-btn" data-consent="denied">{{ $ccText['decline'] }}</button>
            <button type="button" class="mm-cc-btn" data-consent="granted">{{ $ccText['accept'] }}</button>
        </div>
    </div>
</div>
<style>
.mm-cc{position:fixed;left:0;right:0;bottom:0;z-index:9999;padding:12px max(12px,env(safe-area-inset-left,0px)) calc(12px + env(safe-area-inset-bottom,0px)) max(12px,env(safe-area-inset-right,0px));pointer-events:none}
.mm-cc-in{pointer-events:auto;max-width:960px;margin:0 auto;display:flex;gap:16px;align-items:center;background:#fff;color:#14213a;border:1px solid #dbe4ef;border-radius:14px;box-shadow:0 10px 30px rgba(15,35,70,.18);padding:16px 18px;font-size:14px;line-height:1.5}
.mm-cc-text{flex:1;min-width:0}
.mm-cc-title{margin:0 0 4px;font-weight:700;font-size:15px}
.mm-cc-body{margin:0;color:#4a5a73}
.mm-cc-body a{color:#0b57b0;text-decoration:underline}
.mm-cc-btns{display:flex;gap:10px;flex-shrink:0}
.mm-cc-btn{font:inherit;font-weight:700;min-height:42px;padding:0 20px;border-radius:10px;border:1.5px solid #0b57b0;background:#fff;color:#0b57b0;cursor:pointer}
.mm-cc-btn[data-consent="granted"]{background:#0b57b0;color:#fff}
.mm-cc-btn:focus-visible{outline:3px solid #9cc3f2;outline-offset:2px}
@media (max-width:640px){.mm-cc-in{flex-direction:column;align-items:stretch}.mm-cc-btns .mm-cc-btn{flex:1}}
</style>
<script>
(function () {
    'use strict';
    var box = document.getElementById('mm-cc');
    if (!box) return;
    var YEAR = 60 * 60 * 24 * 365;

    // Cookie is shared by maru-mehsana.com and www.maru-mehsana.com (Domain=.maru-mehsana.com);
    // localStorage is a second copy for browsers/webviews that drop cookies.
    function cookieDomain() {
        var m = location.hostname.match(/(^|\.)(maru-mehsana\.com)$/i);
        return m ? '; domain=.' + m[2] : '';
    }
    function current() {
        var m = document.cookie.match(/(?:^|;\s*)mm_consent=(granted|denied)/);
        if (m) return m[1];
        try {
            var v = localStorage.getItem('mm_consent');
            if (v === 'granted' || v === 'denied') {
                // restore the cookie so analytics (consent default) sees it too
                document.cookie = 'mm_consent=' + v + '; Max-Age=' + YEAR + '; path=/' + cookieDomain() + '; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
                return v;
            }
        } catch (e) {}
        return null;
    }
    function dropGaCookies() {
        // Remove existing Google Analytics cookies when consent is withdrawn.
        document.cookie.split(';').forEach(function (c) {
            var name = c.split('=')[0].trim();
            if (/^_ga(_|$)/.test(name)) {
                var host = location.hostname, parts = host.split('.');
                var domains = ['', host];
                for (var i = 1; i < parts.length - 1; i++) domains.push('.' + parts.slice(i).join('.'));
                domains.forEach(function (d) {
                    document.cookie = name + '=; Max-Age=0; path=/' + (d ? '; domain=' + d : '');
                });
            }
        });
    }
    function choose(value) {
        document.cookie = 'mm_consent=' + value + '; Max-Age=' + YEAR + '; path=/' + cookieDomain() + '; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
        try { localStorage.setItem('mm_consent', value); } catch (e) {}
        if (typeof window.gtag === 'function') {
            window.gtag('consent', 'update', {analytics_storage: value});
            if (window.mmTrack && value === 'granted') window.mmTrack('consent_update', {consent_choice: value});
        }
        if (value === 'denied') dropGaCookies();
        box.hidden = true;
    }
    box.addEventListener('click', function (e) {
        var b = e.target.closest('[data-consent]');
        if (b) choose(b.getAttribute('data-consent'));
    });
    document.addEventListener('click', function (e) {
        var o = e.target.closest && e.target.closest('[data-consent-open]');
        if (o) { e.preventDefault(); box.hidden = false; var f = box.querySelector('[data-consent="granted"]'); if (f) f.focus(); }
    });
    if (!current()) box.hidden = false;
})();
</script>
@endif
