{{-- One news card on /news. Used by the full page, the lead story (with $isLead = true) and the infinite-scroll JSON (?page=N).
     Needs: $article (with agency loaded), $lang. Optional: $isLead --}}
@php
    $isLead = (bool) ($isLead ?? false);
    $date = $article->created_at;
    $when = $date
        ? ($date->diffInDays(now()) < 30 ? $date->copy()->locale($lang)->diffForHumans() : $date->copy()->locale($lang)->isoFormat('D MMM YYYY'))
        : null;
    // Carbon's Gujarati strings spell "ago" as "પેહલા"; the usual spelling is "પહેલાં".
    if ($when && $lang === 'gu') { $when = str_replace('પેહલા', 'પહેલાં', $when); }
    $excerpt = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $article->excerpt), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    $tag = $isLead ? 'div' : 'li';
@endphp
<{{ $tag }} class="dir-card nwl-card{{ $isLead ? ' nwl-lead' : '' }}">
    <div class="media ph">
        <svg class="icon ph-ico" aria-hidden="true"><use href="#i-news"/></svg>
        @if ($article->image_url)
            <img src="{{ $article->image_url }}" alt="" width="{{ $isLead ? 800 : 400 }}" height="{{ $isLead ? 500 : 250 }}"
                 @if ($isLead) fetchpriority="high" @else loading="lazy" @endif decoding="async" onerror="this.remove()">
        @endif
        @if ($isLead)
            <span class="nwl-flag"><svg class="icon icon--fill" aria-hidden="true"><use href="#i-star"/></svg>{{ __('home.nwl_featured_badge') }}</span>
        @endif
    </div>
    <div class="dir-body">
        <p class="nwl-meta">
            @if ($article->agency)<span class="nwl-agency">{{ $article->agency->name }}</span>@endif
            @if ($when)<time datetime="{{ $date->toIso8601String() }}">{{ $when }}</time>@endif
        </p>
        @if ($isLead)
            <h2 class="nwl-lead-title clamp-2"><a class="stretched" href="{{ $article->web_url }}">{{ $article->title }}</a></h2>
        @else
            <h2 class="clamp-2"><a class="stretched" href="{{ $article->web_url }}">{{ $article->title }}</a></h2>
        @endif
        @if ($excerpt !== '')
            <p class="dir-services {{ $isLead ? 'clamp-3' : 'clamp-2' }}">{{ $excerpt }}</p>
        @endif
    </div>
    <div class="plp-foot" aria-hidden="true">
        <span>{{ __('home.nwl_read') }}</span>
        <span class="go"><svg class="icon"><use href="#i-arrow"/></svg></span>
    </div>
</{{ $tag }}>
