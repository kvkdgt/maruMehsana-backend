{{--
    Reports one real website view of an item. Usage: @include('partials.web-visit', ['type' => 'business', 'id' => $business->id])
    The view counts only when the browser runs JavaScript, is not automated, and keeps the page
    visible for WebVisit::MIN_SECONDS. The server then checks the signed token (App\Support\WebVisit).
--}}
@push('scripts')
<script>
(function () {
    var data = {!! json_encode([
        'type'  => $type,
        'id'    => (int) $id,
        'token' => \App\Support\WebVisit::token($type, (int) $id),
    ], JSON_HEX_TAG) !!};
    var url = {!! json_encode(route('web-visit')) !!};
    var need = {{ \App\Support\WebVisit::MIN_SECONDS * 1000 }};
    if (navigator.webdriver || !window.fetch) return;

    var shown = 0, since = null, sent = false, timer = null;
    function send() {
        if (sent) return;
        sent = true;
        var body = new URLSearchParams(data);
        if (navigator.sendBeacon) {
            navigator.sendBeacon(url, body);
        } else {
            fetch(url, { method: 'POST', body: body, keepalive: true, credentials: 'omit' });
        }
    }
    function tick() {
        clearTimeout(timer);
        if (sent) return;
        if (document.visibilityState === 'visible') {
            since = Date.now();
            timer = setTimeout(function () { shown += Date.now() - since; since = null; send(); }, Math.max(0, need - shown));
        } else if (since !== null) {
            shown += Date.now() - since;
            since = null;
        }
    }
    document.addEventListener('visibilitychange', tick);
    tick();
})();
</script>
@endpush
