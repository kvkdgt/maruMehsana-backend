@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
@php
    $statusMap = [
        'requested'  => ['#92400e', '#fef3c7'],
        'confirmed'  => ['#1d4ed8', '#dbeafe'],
        'dispatched' => ['#6d28d9', '#ede9fe'],
        'delivered'  => ['#166534', '#dcfce7'],
        'cancelled'  => ['#475569', '#f1f5f9'],
        'rejected'   => ['#991b1b', '#fee2e2'],
    ];
@endphp

<div style="padding:4px;">
  <div style="margin-bottom:18px;">
    <h3 style="font-size:1.5rem; font-weight:700; color:#2c3e50; margin:0 0 4px;">Orders</h3>
    <p style="color:#777; font-size:0.95rem; margin:0;">All delivery orders across businesses</p>
  </div>

  {{-- Analytics cards --}}
  <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:14px; margin-bottom:22px;">
    @php
      $cards = [
        ['Total Orders', number_format($stats['total']), '#3498db', 'fa-receipt'],
        ['Requested',    number_format($stats['requested']), '#f39c12', 'fa-clock'],
        ['Active',       number_format($stats['active']), '#8b5cf6', 'fa-truck'],
        ['Delivered',    number_format($stats['delivered']), '#16a34a', 'fa-check-circle'],
        ['Cancelled',    number_format($stats['cancelled']), '#ef4444', 'fa-times-circle'],
        ['Revenue (Delivered)', '₹' . number_format($stats['revenue'], 0), '#0077b6', 'fa-indian-rupee-sign'],
      ];
    @endphp
    @foreach($cards as [$label, $value, $color, $icon])
      <div style="background:#fff; border-radius:14px; padding:18px; box-shadow:0 2px 10px rgba(0,0,0,0.05); display:flex; align-items:center; gap:14px;">
        <div style="width:44px; height:44px; border-radius:12px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:{{ $color }}1a;">
          <i class="fas {{ $icon }}" style="color:{{ $color }}; font-size:1.1rem;"></i>
        </div>
        <div>
          <div style="font-size:1.4rem; font-weight:800; color:#2c3e50; line-height:1.1;">{{ $value }}</div>
          <div style="font-size:0.78rem; color:#9aa5b1; font-weight:600;">{{ $label }}</div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Filters --}}
  <form method="GET" action="{{ route('admin.orders') }}" style="background:#fff; border-radius:12px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:18px; display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:12px; align-items:end;">
    <div>
      <label style="font-size:0.78rem; color:#7f8c9a; font-weight:700;">Search</label>
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Order #, business, customer, mobile"
        style="width:100%; padding:9px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:0.9rem; margin-top:4px;">
    </div>
    <div>
      <label style="font-size:0.78rem; color:#7f8c9a; font-weight:700;">Status</label>
      <select name="status" style="width:100%; padding:9px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:0.9rem; margin-top:4px;">
        <option value="">All</option>
        @foreach(['requested','confirmed','dispatched','delivered','cancelled','rejected'] as $st)
          <option value="{{ $st }}" {{ request('status')==$st ? 'selected':'' }}>{{ ucfirst($st) }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label style="font-size:0.78rem; color:#7f8c9a; font-weight:700;">From</label>
      <input type="date" name="start_date" value="{{ request('start_date') }}" style="width:100%; padding:9px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:0.9rem; margin-top:4px;">
    </div>
    <div>
      <label style="font-size:0.78rem; color:#7f8c9a; font-weight:700;">To</label>
      <input type="date" name="end_date" value="{{ request('end_date') }}" style="width:100%; padding:9px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:0.9rem; margin-top:4px;">
    </div>
    <div style="display:flex; gap:8px;">
      <button type="submit" style="background:#0077b6; color:#fff; border:none; padding:10px 16px; border-radius:8px; font-weight:600; cursor:pointer;">Filter</button>
      <a href="{{ route('admin.orders') }}" style="background:#f1f5f9; color:#475569; padding:10px 16px; border-radius:8px; font-weight:600; text-decoration:none;">Reset</a>
    </div>
  </form>

  {{-- Table --}}
  <div style="background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
      <thead>
        <tr style="background:#f8f9fb; text-align:left;">
          @foreach(['Order', 'Business', 'Customer', 'Items', 'Total', 'Status', 'Date'] as $h)
            <th style="padding:14px 16px; font-size:0.8rem; color:#7f8c9a; text-transform:uppercase; letter-spacing:0.5px;">{{ $h }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $o)
        <tr style="border-top:1px solid #eef1f5;">
          <td style="padding:14px 16px; font-weight:700; color:#2c3e50;">{{ $o->order_number }}</td>
          <td style="padding:14px 16px; color:#555;">{{ $o->business->name ?? '—' }}</td>
          <td style="padding:14px 16px;">
            <div style="color:#2c3e50; font-weight:600;">{{ $o->customer_name ?: ($o->customer->name ?? '—') }}</div>
            <div style="font-size:0.82rem; color:#0077b6;">{{ $o->customer_mobile ?: ($o->customer->phone ?? '') }}</div>
          </td>
          <td style="padding:14px 16px; color:#555;">{{ $o->items_count }}</td>
          <td style="padding:14px 16px; font-weight:700; color:#2c3e50;">₹{{ number_format($o->total_amount, 0) }}</td>
          <td style="padding:14px 16px;">
            @php [$fg,$bg] = $statusMap[$o->status] ?? ['#475569','#f1f5f9']; @endphp
            <span style="padding:4px 12px; border-radius:20px; font-size:0.78rem; font-weight:700; color:{{ $fg }}; background:{{ $bg }};">{{ ucfirst($o->status) }}</span>
          </td>
          <td style="padding:14px 16px; color:#777; font-size:0.85rem;">{{ $o->created_at?->format('d M Y, h:i A') }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="padding:40px; text-align:center; color:#94a3b8;">No orders found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:16px;">{{ $orders->links() }}</div>
</div>
@endsection
