@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
  .pro-dash {
    --accent: #F2652D;
    --accent-2: #ff8a5c;
    --ink: #1f2937;
    --muted: #6b7280;
    --line: #eef1f5;
    --card-radius: 16px;
    font-family: 'Poppins', sans-serif;
    color: var(--ink);
  }
  .pro-dash * { box-sizing: border-box; }

  /* ── Hero ───────────────────────────────── */
  .pd-hero {
    background: linear-gradient(120deg, #2c3e50 0%, #1a2733 100%);
    border-radius: var(--card-radius);
    padding: 28px 32px;
    color: #fff;
    position: relative;
    overflow: hidden;
    margin-bottom: 24px;
  }
  .pd-hero::after {
    content: '';
    position: absolute; right: -60px; top: -60px;
    width: 220px; height: 220px; border-radius: 50%;
    background: radial-gradient(circle, rgba(242,101,45,0.35), transparent 70%);
  }
  .pd-hero h1 { margin: 0; font-size: 1.6rem; font-weight: 600; }
  .pd-hero p  { margin: 6px 0 0; opacity: 0.8; font-size: 0.92rem; }
  .pd-hero-chips { display: flex; gap: 12px; margin-top: 20px; flex-wrap: wrap; position: relative; z-index: 1; }
  .pd-chip {
    background: rgba(255,255,255,0.1); backdrop-filter: blur(4px);
    border: 1px solid rgba(255,255,255,0.15);
    padding: 10px 16px; border-radius: 12px; min-width: 110px;
  }
  .pd-chip .v { font-size: 1.3rem; font-weight: 700; }
  .pd-chip .l { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.75; }

  /* ── Section title ──────────────────────── */
  .pd-sec-title { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); font-weight: 600; margin: 26px 0 14px; }

  /* ── KPI cards ──────────────────────────── */
  .pd-grid { display: grid; gap: 20px; }
  .pd-grid-4 { grid-template-columns: repeat(4, 1fr); }
  .pd-grid-3 { grid-template-columns: repeat(3, 1fr); }
  .pd-grid-2 { grid-template-columns: 1.4fr 1fr; }
  @media (max-width: 1100px){ .pd-grid-4{grid-template-columns:repeat(2,1fr);} .pd-grid-2{grid-template-columns:1fr;} }
  @media (max-width: 760px){ .pd-grid-4,.pd-grid-3{grid-template-columns:1fr;} }

  .pd-card {
    background: #fff; border-radius: var(--card-radius); padding: 20px;
    box-shadow: 0 6px 20px rgba(17,24,39,0.05); border: 1px solid var(--line);
  }
  .pd-kpi { display: flex; align-items: center; gap: 16px; transition: transform .2s, box-shadow .2s; }
  .pd-kpi:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(17,24,39,0.1); }
  .pd-kpi-icon { width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #fff; flex-shrink: 0; }
  .pd-kpi-val { font-size: 1.7rem; font-weight: 700; line-height: 1; }
  .pd-kpi-lbl { font-size: 0.82rem; color: var(--muted); margin-top: 5px; }
  .pd-kpi-sub { font-size: 0.72rem; margin-top: 6px; font-weight: 600; }
  .pd-up { color: #16a34a; } .pd-mut { color: var(--muted); }

  /* ── Mini visitor stats ─────────────────── */
  .pd-vis { display: flex; align-items: center; justify-content: space-between; }
  .pd-vis .ico { font-size: 1.2rem; width: 44px; height: 44px; border-radius: 12px; display:flex; align-items:center; justify-content:center; }

  /* ── Lists / panels ─────────────────────── */
  .pd-panel-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
  .pd-panel-head h3 { margin: 0; font-size: 1.05rem; font-weight: 600; }
  .pd-panel-head a { font-size: 0.8rem; color: var(--accent); text-decoration: none; font-weight: 600; }

  .pd-row { display: flex; align-items: center; gap: 12px; padding: 11px 0; border-bottom: 1px solid var(--line); }
  .pd-row:last-child { border-bottom: none; }
  .pd-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent-2)); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:600; flex-shrink:0; }
  .pd-row .main { flex: 1; min-width: 0; }
  .pd-row .name { font-weight: 600; font-size: 0.9rem; }
  .pd-row .meta { font-size: 0.78rem; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

  .pd-badge { font-size: 0.7rem; padding: 4px 10px; border-radius: 20px; font-weight: 600; white-space: nowrap; }
  .pd-badge.pending { background:#fff4e5; color:#b45309; }
  .pd-badge.resolved, .pd-badge.completed { background:#e8f8f0; color:#15803d; }
  .pd-badge.default { background:#eef2f7; color:#475569; }

  .pd-empty { text-align: center; color: var(--muted); padding: 24px 0; font-size: 0.85rem; }

  /* ── Notification health ────────────────── */
  .pd-health { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
  .pd-health .h { text-align: center; padding: 14px 8px; border-radius: 12px; background: #f8fafc; }
  .pd-health .h .v { font-size: 1.3rem; font-weight: 700; }
  .pd-health .h .l { font-size: 0.7rem; color: var(--muted); text-transform: uppercase; }

  /* ── Banner ads ─────────────────────────── */
  .pd-ads { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; }
  @media (max-width: 900px){ .pd-ads{grid-template-columns:repeat(2,1fr);} }
  .pd-ad { border-radius: 12px; overflow: hidden; border: 1px solid var(--line); background:#fff; }
  .pd-ad img { width: 100%; height: 110px; object-fit: cover; display:block; }
  .pd-ad .b { padding: 10px 12px; display:flex; justify-content:space-between; align-items:center; }
  .pd-ad .b h4 { margin:0; font-size:0.82rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .pd-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }

  /* ── Facts ──────────────────────────────── */
  .pd-fact { background: linear-gradient(135deg,#fff7f3,#ffece2); border-left: 4px solid var(--accent); padding: 14px 16px; border-radius: 10px; font-size: 0.88rem; }
</style>

<div class="pro-dash">

  {{-- ── Hero ─────────────────────────────── --}}
  <div class="pd-hero">
    <h1>Welcome back, {{ Auth::user()->name }} 👋</h1>
    <p>{{ now()->format('l, d M Y') }} — here's what's happening across Maru Mehsana today.</p>
    <div class="pd-hero-chips">
      <div class="pd-chip"><div class="v">{{ number_format($totalAppUsers ?? 0) }}</div><div class="l">Total Users</div></div>
      <div class="pd-chip"><div class="v">{{ number_format($loggedInUsers ?? 0) }}</div><div class="l">Logged-in Users</div></div>
      <div class="pd-chip"><div class="v">{{ number_format($totalJobs ?? 0) }}</div><div class="l">Job Vacancies</div></div>
      <div class="pd-chip"><div class="v">{{ number_format($totalJobViews ?? 0) }}</div><div class="l">Job Views</div></div>
    </div>
  </div>

  {{-- ── KPI cards ────────────────────────── --}}
  <div class="pd-sec-title">Overview</div>
  <div class="pd-grid pd-grid-4">
    <div class="pd-card pd-kpi">
      <div class="pd-kpi-icon" style="background:#3b82f6;"><i class="fas fa-th-large"></i></div>
      <div>
        <div class="pd-kpi-val">{{ number_format($totalCategories) }}</div>
        <div class="pd-kpi-lbl">Categories</div>
        <div class="pd-kpi-sub pd-mut">{{ number_format($totalCategoryVisitors ?? 0) }} visits</div>
      </div>
    </div>
    <div class="pd-card pd-kpi">
      <div class="pd-kpi-icon" style="background:#F2652D;"><i class="fas fa-store"></i></div>
      <div>
        <div class="pd-kpi-val">{{ number_format($totalBusinesses) }}</div>
        <div class="pd-kpi-lbl">Businesses</div>
        <div class="pd-kpi-sub pd-mut">{{ number_format($totalBusinessVisitors ?? 0) }} visits</div>
      </div>
    </div>
    <div class="pd-card pd-kpi">
      <div class="pd-kpi-icon" style="background:#8b5cf6;"><i class="fas fa-users"></i></div>
      <div>
        <div class="pd-kpi-val">{{ number_format($totalAppUsers ?? 0) }}</div>
        <div class="pd-kpi-lbl">App Users</div>
        <div class="pd-kpi-sub pd-up"><i class="fas fa-arrow-up"></i> {{ number_format($newUsersToday ?? 0) }} today</div>
      </div>
    </div>
    <div class="pd-card pd-kpi">
      <div class="pd-kpi-icon" style="background:#10b981;"><i class="fas fa-map-marked-alt"></i></div>
      <div>
        <div class="pd-kpi-val">{{ number_format($totalTouristPlaces ?? 0) }}</div>
        <div class="pd-kpi-lbl">Tourist Places</div>
        <div class="pd-kpi-sub pd-mut">{{ number_format($totalTouristVisitors ?? 0) }} visits</div>
      </div>
    </div>
  </div>

  {{-- ── Jobs ─────────────────────────────── --}}
  <div class="pd-sec-title">Job Vacancies</div>
  <div class="pd-grid pd-grid-3">
    <div class="pd-card pd-kpi">
      <div class="pd-kpi-icon" style="background:#0ea5e9;"><i class="fas fa-briefcase"></i></div>
      <div>
        <div class="pd-kpi-val">{{ number_format($totalJobs ?? 0) }}</div>
        <div class="pd-kpi-lbl">Total Jobs</div>
        <div class="pd-kpi-sub pd-up"><i class="fas fa-circle" style="font-size:0.5rem;vertical-align:middle;"></i> {{ number_format($activeJobs ?? 0) }} active</div>
      </div>
    </div>
    <div class="pd-card pd-kpi">
      <div class="pd-kpi-icon" style="background:#F2652D;"><i class="fas fa-eye"></i></div>
      <div>
        <div class="pd-kpi-val">{{ number_format($totalJobViews ?? 0) }}</div>
        <div class="pd-kpi-lbl">Total Job Views</div>
        <div class="pd-kpi-sub pd-mut">across all vacancies</div>
      </div>
    </div>
    <div class="pd-card pd-kpi">
      <div class="pd-kpi-icon" style="background:#64748b;"><i class="fas fa-eye-slash"></i></div>
      <div>
        <div class="pd-kpi-val">{{ number_format(($totalJobs ?? 0) - ($activeJobs ?? 0)) }}</div>
        <div class="pd-kpi-lbl">Inactive / Hidden Jobs</div>
        <div class="pd-kpi-sub pd-mut">not visible to users</div>
      </div>
    </div>
  </div>

  {{-- ── Users breakdown ──────────────────── --}}
  <div class="pd-sec-title">Users</div>
  <div class="pd-grid pd-grid-2">
    <div class="pd-card">
      <div class="pd-panel-head"><h3>Logged-in vs Total Users</h3>
        <span class="pd-badge default">
          {{ ($totalAppUsers ?? 0) > 0 ? round(($loggedInUsers ?? 0) / $totalAppUsers * 100) : 0 }}% online
        </span>
      </div>
      @php $pct = ($totalAppUsers ?? 0) > 0 ? min(100, ($loggedInUsers ?? 0) / $totalAppUsers * 100) : 0; @endphp
      <div style="display:flex;justify-content:space-between;font-size:0.85rem;margin-bottom:8px;">
        <span><i class="fas fa-circle" style="color:#10b981;font-size:0.6rem;"></i> Logged-in: <strong>{{ number_format($loggedInUsers ?? 0) }}</strong></span>
        <span><i class="fas fa-circle" style="color:#e2e8f0;font-size:0.6rem;"></i> Total: <strong>{{ number_format($totalAppUsers ?? 0) }}</strong></span>
      </div>
      <div style="height:14px;background:#eef2f7;border-radius:10px;overflow:hidden;">
        <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#10b981,#34d399);border-radius:10px;"></div>
      </div>
      <div style="font-size:0.78rem;color:var(--muted);margin-top:10px;">
        {{ number_format($loggedInUsers ?? 0) }} of {{ number_format($totalAppUsers ?? 0) }} users are currently logged in.
      </div>
    </div>

    <div class="pd-card">
      <div class="pd-panel-head"><h3>User Snapshot</h3></div>
      <div class="pd-health">
        <div class="h"><div class="v" style="color:#8b5cf6;">{{ number_format($totalAppUsers ?? 0) }}</div><div class="l">Total</div></div>
        <div class="h"><div class="v" style="color:#10b981;">{{ number_format($loggedInUsers ?? 0) }}</div><div class="l">Logged-in</div></div>
        <div class="h"><div class="v" style="color:#0ea5e9;">{{ number_format($newUsersToday ?? 0) }}</div><div class="l">New Today</div></div>
      </div>
    </div>
  </div>

  {{-- ── Recent activity ──────────────────── --}}
  <div class="pd-sec-title">Recent Activity</div>
  <div class="pd-grid pd-grid-3">

    {{-- Recent users --}}
    <div class="pd-card">
      <div class="pd-panel-head"><h3>New Users</h3><a href="{{ route('admin.app-users') }}">View all</a></div>
      @forelse($recentUsers ?? [] as $user)
        <div class="pd-row">
          <div class="pd-avatar">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</div>
          <div class="main">
            <div class="name">{{ $user->name }}</div>
            <div class="meta">{{ $user->email }}</div>
          </div>
          <span class="meta">{{ optional($user->created_at)->diffForHumans() }}</span>
        </div>
      @empty
        <div class="pd-empty"><i class="fas fa-user-slash"></i><br>No recent users</div>
      @endforelse
    </div>

    {{-- Recent enquiries --}}
    <div class="pd-card">
      <div class="pd-panel-head"><h3>Business Enquiries</h3><a href="{{ route('admin.business-enquiry') }}">View all</a></div>
      @forelse($recentEnquiries ?? [] as $enquiry)
        <div class="pd-row">
          <div class="pd-avatar" style="background:linear-gradient(135deg,#3b82f6,#60a5fa);"><i class="fas fa-briefcase"></i></div>
          <div class="main">
            <div class="name">{{ $enquiry->business_name }}</div>
            <div class="meta">{{ $enquiry->owner_name }} • {{ $enquiry->mobile_no }}</div>
          </div>
          <span class="pd-badge {{ strtolower($enquiry->status ?? 'default') === 'pending' ? 'pending' : (strtolower($enquiry->status ?? '') === 'resolved' ? 'resolved' : 'default') }}">
            {{ $enquiry->status }}
          </span>
        </div>
      @empty
        <div class="pd-empty"><i class="fas fa-inbox"></i><br>No recent enquiries</div>
      @endforelse
    </div>

    {{-- Top viewed jobs --}}
    <div class="pd-card">
      <div class="pd-panel-head"><h3>Most Viewed Jobs</h3><a href="{{ route('admin.jobs') }}">View all</a></div>
      @forelse($topJobs ?? [] as $job)
        <div class="pd-row">
          <div class="pd-avatar" style="background:linear-gradient(135deg,#0ea5e9,#38bdf8);"><i class="fas fa-briefcase"></i></div>
          <div class="main">
            <div class="name">{{ Str::limit($job->title, 26) }}</div>
            <div class="meta">{{ Str::limit($job->company_name ?? 'N/A', 30) }}</div>
          </div>
          <span class="pd-badge default"><i class="fas fa-eye"></i> {{ number_format($job->views_count ?? 0) }}</span>
        </div>
      @empty
        <div class="pd-empty"><i class="fas fa-briefcase"></i><br>No jobs posted yet</div>
      @endforelse
    </div>
  </div>

  {{-- ── Banner ads ───────────────────────── --}}
  @if(isset($activeBannerAds) && count($activeBannerAds) > 0)
  <div class="pd-sec-title">Active Banner Ads</div>
  <div class="pd-ads">
    @foreach($activeBannerAds as $ad)
      <div class="pd-ad">
        @if($ad->image)
          <img src="{{ asset('storage/'.$ad->image) }}" alt="{{ $ad->title }}">
        @else
          <div style="height:110px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#cbd5e1;"><i class="fas fa-image fa-2x"></i></div>
        @endif
        <div class="b">
          <h4>{{ $ad->title }}</h4>
          <span class="pd-dot" style="background:{{ $ad->status ? '#10b981' : '#cbd5e1' }};" title="{{ $ad->status ? 'Active' : 'Inactive' }}"></span>
        </div>
      </div>
    @endforeach
  </div>
  @endif

  {{-- ── Did you know (facts) ─────────────── --}}
  @if(isset($randomFacts) && count($randomFacts) > 0)
  <div class="pd-sec-title">Did You Know?</div>
  <div class="pd-grid pd-grid-3">
    @foreach($randomFacts->take(3) as $fact)
      <div class="pd-fact"><i class="fas fa-lightbulb" style="color:var(--accent);"></i> {{ $fact->fact }}</div>
    @endforeach
  </div>
  @endif

</div>
@endsection
