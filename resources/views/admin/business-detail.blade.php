@extends('layouts.admin')

@section('title', $business->name)

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/businesses.css'); }}">

<div class="businesses-container">
  <div class="header-container">
    <div>
      <h3 style="font-size: 1.5rem; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">Business Details</h3>
      <p style="color: #777; font-size: 0.95rem;">Full overview of the selected business</p>
    </div>
    <div style="display: flex; gap: 10px;">
      <a href="{{ route('admin.businesses') }}" class="reset-btn"><i class="fas fa-arrow-left"></i> Back</a>
      <a href="{{ route('business.edit', $business->id) }}" class="filter-btn" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
        <i class="fas fa-edit"></i> Edit
      </a>
    </div>
  </div>

  {{-- Top summary card --}}
  <div style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:24px;">
    <div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">
      @if($business->thumbnail)
        <img src="{{ asset('storage/' . $business->thumbnail) }}" style="width:140px; height:140px; border-radius:12px; object-fit:cover;">
      @else
        <div style="width:140px; height:140px; border-radius:12px; background:#eee; display:flex; align-items:center; justify-content:center;">
          <i class="fas fa-store" style="color:#ccc; font-size:2rem;"></i>
        </div>
      @endif

      <div style="flex:1; min-width:260px;">
        <h2 style="margin:0 0 6px; color:#2c3e50;">{{ $business->name }}</h2>
        <span class="badge-premium badge-info">{{ $business->category->name ?? 'Uncategorized' }}</span>
        <p style="color:#555; margin-top:12px; line-height:1.5;">{{ $business->description }}</p>
      </div>

      {{-- Stats --}}
      <div style="display:flex; gap:16px; flex-wrap:wrap;">
        <div style="text-align:center; padding:14px 20px; background:#f8f9fb; border-radius:10px; min-width:90px;">
          <div style="font-size:1.4rem; font-weight:700; color:#f1c40f;">
            <i class="fas fa-star" style="font-size:1rem;"></i>
            {{ $business->reviews_avg_rating ? number_format($business->reviews_avg_rating, 1) : '0.0' }}
          </div>
          <div style="font-size:0.8rem; color:#777;">Avg. Rating</div>
        </div>
        <div style="text-align:center; padding:14px 20px; background:#f8f9fb; border-radius:10px; min-width:90px;">
          <div style="font-size:1.4rem; font-weight:700; color:#9b59b6;">{{ number_format($business->reviews_count) }}</div>
          <div style="font-size:0.8rem; color:#777;">Total Reviews</div>
        </div>
        <div style="text-align:center; padding:14px 20px; background:#f8f9fb; border-radius:10px; min-width:90px;">
          <div style="font-size:1.4rem; font-weight:700; color:#3498db;">{{ number_format($business->visitors) }}</div>
          <div style="font-size:0.8rem; color:#777;">Visitors</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Contact / meta info --}}
  <div style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:24px;">
    <h4 style="margin:0 0 16px; color:#2c3e50;">Contact & Information</h4>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
      <div><strong><i class="fas fa-phone" style="color:#3498db;"></i> Mobile:</strong> {{ $business->mobile_no ?: '—' }}</div>
      <div><strong><i class="fab fa-whatsapp" style="color:#25D366;"></i> WhatsApp:</strong> {{ $business->whatsapp_no ?: '—' }}</div>
      <div><strong><i class="fas fa-envelope" style="color:#e74c3c;"></i> Email:</strong> {{ $business->email_id ?: '—' }}</div>
      <div><strong><i class="fas fa-globe" style="color:#9b59b6;"></i> Website:</strong>
        @if($business->website_url)
          <a href="{{ $business->website_url }}" target="_blank">{{ $business->website_url }}</a>
        @else — @endif
      </div>
      <div style="grid-column:1/-1;"><strong><i class="fas fa-box" style="color:#f39c12;"></i> Products:</strong> {{ $business->products ?: '—' }}</div>
      <div style="grid-column:1/-1;"><strong><i class="fas fa-concierge-bell" style="color:#1abc9c;"></i> Services:</strong> {{ $business->services ?: '—' }}</div>
      <div style="grid-column:1/-1;"><strong><i class="fas fa-user-tie" style="color:#0077b6;"></i> Business Owner:</strong>
        @if($business->owner)
          {{ $business->owner->name }} <span style="color:#777;">({{ $business->owner->email ?: 'no email' }})</span>
        @else — @endif
      </div>
      <div><strong><i class="fas fa-user" style="color:#777;"></i> Created By:</strong> {{ $business->creator->name ?? '—' }}</div>
      <div><strong><i class="fas fa-calendar" style="color:#777;"></i> Created At:</strong> {{ $business->created_at?->format('d M Y, h:i A') }}</div>
    </div>
  </div>

  {{-- Gallery --}}
  @if($business->businessImages->count())
  <div style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:24px;">
    <h4 style="margin:0 0 16px; color:#2c3e50;">Gallery ({{ $business->businessImages->count() }})</h4>
    <div style="display:flex; gap:12px; flex-wrap:wrap;">
      @foreach($business->businessImages as $img)
        <a href="{{ asset('storage/' . $img->image) }}" target="_blank">
          <img src="{{ asset('storage/' . $img->image) }}" style="width:120px; height:120px; border-radius:10px; object-fit:cover;">
        </a>
      @endforeach
    </div>
  </div>
  @endif

  {{-- Reviews --}}
  <div style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
    <h4 style="margin:0 0 16px; color:#2c3e50;">Reviews ({{ number_format($business->reviews_count) }})</h4>

    @forelse($business->reviews as $review)
      <div style="border-bottom:1px solid #eee; padding:14px 0;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
          <span style="font-weight:600; color:#2c3e50;">
            <i class="fas fa-user-circle" style="color:#bbb;"></i>
            {{ $review->user->name ?? 'Anonymous' }}
          </span>
          <span style="color:#f1c40f; font-weight:600;">
            @for($i = 1; $i <= 5; $i++)
              <i class="fa{{ $i <= $review->rating ? 's' : 'r' }} fa-star"></i>
            @endfor
            <span style="color:#777; font-size:0.85rem;">({{ $review->rating }})</span>
          </span>
        </div>
        @if($review->comment)
          <p style="color:#555; margin:4px 0;">{{ $review->comment }}</p>
        @endif
        <small style="color:#999;">{{ $review->created_at?->format('d M Y, h:i A') }}</small>
      </div>
    @empty
      <p style="color:#777; text-align:center; padding:20px 0;">No reviews yet for this business.</p>
    @endforelse
  </div>
</div>
@endsection
