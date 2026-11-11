{{-- One business card on /businesses. Used by the full page and by the infinite-scroll JSON (?page=N). Needs: $business --}}
@php
    $reviews = (int) $business->reviews_count;
    $avg = $business->reviews_avg_rating;
    $phone = preg_replace('/\D+/', '', (string) $business->mobile_no);
    $wa = $business->whatsapp_no ?: $business->mobile_no;
    $waDigits = preg_replace('/\D+/', '', (string) $wa);
    $waDigits = strlen($waDigits) === 10 ? '91' . $waDigits : $waDigits;
    $thumb = $business->thumbnail
        ? (\Illuminate\Support\Str::startsWith($business->thumbnail, ['http://', 'https://']) ? $business->thumbnail : asset('storage/' . ltrim($business->thumbnail, '/')))
        : null;
@endphp
<li class="dir-card">
    <div class="media ph">
        <svg class="icon ph-ico" aria-hidden="true"><use href="#i-store"/></svg>
        @if ($thumb)
            <img src="{{ $thumb }}" alt="" width="400" height="260" loading="lazy" decoding="async" onerror="this.remove()">
        @endif
    </div>
    <div class="dir-body">
        <p class="dir-cat clamp-1">{{ optional($business->category)->name ?: __('home.business_default') }}</p>
        <h2 class="clamp-2"><a class="stretched" href="{{ route('businesses.show', $business->id) }}">{{ $business->name }}</a></h2>
        @if ($reviews > 0 && $avg)
            <p class="rating" aria-label="{{ __('home.rating_aria', ['rating' => number_format((float) $avg, 1), 'count' => $reviews]) }}">
                <svg class="icon icon--fill star" aria-hidden="true"><use href="#i-star"/></svg>
                <b>{{ number_format((float) $avg, 1) }}</b>
                <span>({{ number_format($reviews) }})</span>
            </p>
        @endif
        @if ($business->services)
            <p class="dir-services clamp-2">{{ $business->services }}</p>
        @endif
    </div>
    @if ($phone || $wa)
        <div class="dir-actions">
            @if ($phone)
                <a class="dir-btn dir-btn--call" href="tel:{{ $phone }}"><svg class="icon" aria-hidden="true"><use href="#i-phone"/></svg>{{ __('home.bizp_call') }}</a>
            @endif
            @if ($wa)
                <a class="dir-btn dir-btn--wa" href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener"><svg class="icon" aria-hidden="true"><use href="#i-chat"/></svg>WhatsApp</a>
            @endif
        </div>
    @endif
</li>
