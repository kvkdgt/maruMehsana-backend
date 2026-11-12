{{-- One job card on /jobs. Used by the full page and by the infinite-scroll JSON (?page=N). Needs: $job, $lang.
     Shows card fields only: never the poster's phone or email. --}}
@php
    // Indian digit grouping: 1234567 -> 12,34,567
    $inr = function ($n) {
        $s = (string) (int) $n;
        if (strlen($s) <= 3) return $s;
        $last3 = substr($s, -3);
        $rest = substr($s, 0, -3);
        return preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',' . $last3;
    };
    $min = (int) $job->salary_min;
    $max = (int) $job->salary_max;
    $pay = null;
    if ($job->salary_type !== 'not_disclosed' && ($min || $max)) {
        if ($min && $max && $min !== $max) {
            $amount = '₹' . $inr(min($min, $max)) . ' – ₹' . $inr(max($min, $max));
        } elseif ($min && $max) {
            $amount = '₹' . $inr($min);
        } elseif ($min) {
            $amount = __('home.jbl_pay_from', ['amount' => '₹' . $inr($min)]);
        } else {
            $amount = __('home.jbl_pay_upto', ['amount' => '₹' . $inr($max)]);
        }
        $pay = in_array($job->salary_type, ['monthly', 'yearly', 'hourly'], true)
            ? __('home.jbl_pay_' . $job->salary_type, ['amount' => $amount])
            : $amount;
    }

    $date = $job->created_at;
    $when = $date
        ? ($date->diffInDays(now()) < 30 ? $date->copy()->locale($lang)->diffForHumans() : $date->copy()->locale($lang)->isoFormat('D MMM YYYY'))
        : null;
    // Carbon's Gujarati strings spell "ago" as "પેહલા"; the usual spelling is "પહેલાં".
    if ($when && $lang === 'gu') { $when = str_replace('પેહલા', 'પહેલાં', $when); }

    $filled = $job->status === 'filled';
    // Deadline note for open jobs closing within a week (the list never shows expired jobs).
    $closes = null;
    if (!$filled && $job->expires_at) {
        $days = (int) now()->startOfDay()->diffInDays($job->expires_at->copy()->startOfDay(), false);
        if ($days >= 0 && $days <= 7) {
            $closes = $days === 0 ? __('home.jbl_closes_today')
                : ($days === 1 ? __('home.jbl_closes_tomorrow') : __('home.jbl_closes_in', ['days' => $days]));
        }
    }

    $thumb = $job->thumbnail
        ? (\Illuminate\Support\Str::startsWith($job->thumbnail, ['http://', 'https://']) ? $job->thumbnail : asset('storage/' . ltrim($job->thumbnail, '/')))
        : null;
    $openings = (int) $job->vacancies_count;
@endphp
<li class="dir-card jbl-card{{ $filled ? ' is-filled' : '' }}">
    <div class="dir-body">
        <div class="jbl-top">
            <span class="jbl-logo" aria-hidden="true">
                <svg class="icon"><use href="#i-briefcase"/></svg>
                @if ($thumb)
                    <img src="{{ $thumb }}" alt="" width="48" height="48" loading="lazy" decoding="async" onerror="this.remove()">
                @endif
            </span>
            <div class="jbl-head">
                <h2 class="clamp-2"><a class="stretched" href="{{ route('jobs.show', $job->id) }}">{{ $job->title }}</a></h2>
                @if ($job->company_name)
                    <p class="jbl-co clamp-1">{{ $job->company_name }}</p>
                @endif
            </div>
        </div>

        <p class="jbl-tags">
            @if (in_array($job->job_type, \App\Http\Controllers\JobListController::TYPES, true))
                <span class="jbl-tag">{{ __('home.jbl_type_' . $job->job_type) }}</span>
            @endif
            @if ($filled)
                <span class="jbl-tag jbl-tag--filled">{{ __('home.jbl_filled') }}</span>
            @elseif ($closes)
                <span class="jbl-tag jbl-tag--soon">{{ $closes }}</span>
            @endif
        </p>

        <p class="jbl-pay{{ $pay ? '' : ' is-none' }}">{{ $pay ?? __('home.jbl_pay_none') }}</p>

        <ul class="jbl-meta">
            @if ($job->location)
                <li><svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg><span>{{ $job->location }}</span></li>
            @endif
            @if ($job->experience_required)
                <li><svg class="icon" aria-hidden="true"><use href="#i-briefcase"/></svg><span>{{ __('home.jbl_exp', ['exp' => $job->experience_required]) }}</span></li>
            @endif
            @if ($openings > 1)
                <li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg><span>{{ __('home.jbl_openings', ['count' => $openings]) }}</span></li>
            @endif
        </ul>
    </div>
    <div class="plp-foot">
        @if ($when)<time class="jbl-when" datetime="{{ $date->toIso8601String() }}">{{ __('home.jbl_posted', ['when' => $when]) }}</time>@else<span aria-hidden="true">{{ __('home.jbl_view') }}</span>@endif
        <span class="go" aria-hidden="true"><svg class="icon"><use href="#i-arrow"/></svg></span>
    </div>
</li>
