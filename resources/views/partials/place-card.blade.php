{{-- One tourist place card on /places. Used by the full page and by the infinite-scroll JSON (?page=N). Needs: $place --}}
@php
    $reviews = (int) $place->reviews_count;
    $avg = $place->reviews_avg_rating;
    $thumb = $place->thumbnail
        ? (\Illuminate\Support\Str::startsWith($place->thumbnail, ['http://', 'https://']) ? $place->thumbnail : asset('storage/' . ltrim($place->thumbnail, '/')))
        : null;
    $desc = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $place->description)));
@endphp
<li class="dir-card plp-card">
    <div class="media ph">
        <svg class="icon ph-ico" aria-hidden="true"><use href="#i-pin"/></svg>
        @if ($thumb)
            <img src="{{ $thumb }}" alt="" width="400" height="240" loading="lazy" decoding="async" onerror="this.remove()">
        @endif
    </div>
    <div class="dir-body">
        @if ($place->location)
            <p class="plp-loc"><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg><span>{{ $place->location }}</span></p>
        @endif
        <h2 class="clamp-2"><a class="stretched" href="{{ route('places.show', $place->id) }}">{{ $place->name }}</a></h2>
        @if ($reviews > 0 && $avg)
            <p class="rating" aria-label="{{ __('home.rating_aria', ['rating' => number_format((float) $avg, 1), 'count' => $reviews]) }}">
                <svg class="icon icon--fill star" aria-hidden="true"><use href="#i-star"/></svg>
                <b>{{ number_format((float) $avg, 1) }}</b>
                <span>({{ number_format($reviews) }})</span>
            </p>
        @endif
        @if ($desc !== '')
            <p class="dir-services clamp-2">{{ \Illuminate\Support\Str::limit($desc, 180) }}</p>
        @endif
    </div>
    <div class="plp-foot" aria-hidden="true">
        <span>{{ __('home.plp_view') }}</span>
        <span class="go"><svg class="icon"><use href="#i-arrow"/></svg></span>
    </div>
</li>
