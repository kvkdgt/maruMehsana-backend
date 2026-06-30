@extends('layouts.admin')

@section('title', 'Delivery Requests')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div style="padding:4px;">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
      <h3 style="font-size:1.5rem; font-weight:700; color:#2c3e50; margin:0 0 4px;">Delivery Requests</h3>
      <p style="color:#777; font-size:0.95rem; margin:0;">Approve or reject home-delivery access for businesses</p>
    </div>
    <form method="GET" action="{{ route('admin.delivery-requests') }}">
      <select name="status" onchange="this.form.submit()"
        style="padding:8px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:0.9rem;">
        <option value="">All Statuses</option>
        <option value="pending"  {{ request('status')=='pending'  ? 'selected':'' }}>Pending</option>
        <option value="approved" {{ request('status')=='approved' ? 'selected':'' }}>Approved</option>
        <option value="rejected" {{ request('status')=='rejected' ? 'selected':'' }}>Rejected</option>
      </select>
    </form>
  </div>

  @if (session('success'))
    <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; padding:12px 16px; border-radius:10px; margin-bottom:16px; font-weight:600;">
      {{ session('success') }}
    </div>
  @endif

  <div style="background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
      <thead>
        <tr style="background:#f8f9fb; text-align:left;">
          <th style="padding:14px 16px; font-size:0.8rem; color:#7f8c9a; text-transform:uppercase; letter-spacing:0.5px;">Business</th>
          <th style="padding:14px 16px; font-size:0.8rem; color:#7f8c9a; text-transform:uppercase; letter-spacing:0.5px;">Owner</th>
          <th style="padding:14px 16px; font-size:0.8rem; color:#7f8c9a; text-transform:uppercase; letter-spacing:0.5px;">Requested</th>
          <th style="padding:14px 16px; font-size:0.8rem; color:#7f8c9a; text-transform:uppercase; letter-spacing:0.5px;">Status</th>
          <th style="padding:14px 16px; font-size:0.8rem; color:#7f8c9a; text-transform:uppercase; letter-spacing:0.5px; text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($requests as $b)
        <tr style="border-top:1px solid #eef1f5;" id="row-{{ $b->id }}">
          <td style="padding:14px 16px;">
            <div style="display:flex; align-items:center; gap:12px;">
              @if($b->thumbnail)
                <img src="{{ asset('storage/' . $b->thumbnail) }}" style="width:42px; height:42px; border-radius:8px; object-fit:cover;">
              @else
                <div style="width:42px; height:42px; border-radius:8px; background:#eee; display:flex; align-items:center; justify-content:center;"><i class="fas fa-store" style="color:#ccc;"></i></div>
              @endif
              <div>
                <a href="{{ route('business.show', $b->id) }}" style="font-weight:600; color:#2c3e50; text-decoration:none;">{{ $b->name }}</a>
              </div>
            </div>
          </td>
          <td style="padding:14px 16px;">
            @if($b->owner)
              <div style="font-weight:600; color:#2c3e50;">{{ $b->owner->name }}</div>
              <div style="font-size:0.82rem; color:#777;">{{ $b->owner->email ?: '—' }}</div>
              <div style="font-size:0.82rem; color:#0077b6;"><i class="fas fa-phone-alt" style="font-size:0.7rem;"></i> {{ $b->owner->phone ?: 'no mobile' }}</div>
            @else
              <span style="color:#c3ccd6;">— no owner —</span>
            @endif
          </td>
          <td style="padding:14px 16px; color:#555;">{{ $b->delivery_requested_at?->format('d M Y, h:i A') ?? '—' }}</td>
          <td style="padding:14px 16px;">
            @php
              $map = ['pending'=>['#92400e','#fef3c7'],'approved'=>['#166534','#dcfce7'],'rejected'=>['#991b1b','#fee2e2']];
              [$fg,$bg] = $map[$b->delivery_status] ?? ['#475569','#f1f5f9'];
            @endphp
            <span id="badge-{{ $b->id }}" style="padding:4px 12px; border-radius:20px; font-size:0.78rem; font-weight:700; color:{{ $fg }}; background:{{ $bg }};">
              {{ ucfirst($b->delivery_status) }}
            </span>
            @if($b->delivery_status==='rejected' && $b->delivery_reject_reason)
              <div style="font-size:0.78rem; color:#991b1b; margin-top:4px;">{{ $b->delivery_reject_reason }}</div>
            @endif
          </td>
          <td style="padding:14px 16px; text-align:right; white-space:nowrap;">
            <button onclick="setStatus({{ $b->id }},'approved')"
              style="background:#10b981; color:#fff; border:none; padding:8px 14px; border-radius:8px; font-weight:600; cursor:pointer; margin-left:6px;">
              <i class="fas fa-check"></i> Approve
            </button>
            <button onclick="rejectReq({{ $b->id }})"
              style="background:#ef4444; color:#fff; border:none; padding:8px 14px; border-radius:8px; font-weight:600; cursor:pointer; margin-left:6px;">
              <i class="fas fa-times"></i> Reject
            </button>
          </td>
        </tr>
        @empty
        <tr><td colspan="5" style="padding:40px; text-align:center; color:#94a3b8;">No delivery requests found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:16px;">{{ $requests->links() }}</div>
</div>

<script>
  function setStatus(id, status, reason) {
    fetch(`{{ url('admin/delivery-requests/update') }}/${id}`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ status: status, reject_reason: reason || null }),
    })
    .then(r => { if (r.ok) location.reload(); else return r.json().then(e => alert(e.message || 'Failed')); })
    .catch(() => alert('Request failed'));
  }
  function rejectReq(id) {
    const reason = prompt('Reason for rejecting this delivery request (optional):', '');
    if (reason === null) return; // cancelled
    setStatus(id, 'rejected', reason);
  }
</script>
@endsection
