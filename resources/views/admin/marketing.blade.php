@extends('layouts.admin')

@section('title', 'Marketing')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/marketing.css') }}">

<div class="marketing-container">
  <h3 class="marketing-label">Marketing</h3>
  <p class="marketing-description">Manage Notifications and Banner Ads from here.</p>

  <div class="cards-container">
    <!-- Notifications Card -->
    <div class="marketing-card">
      <img src="{{ URL::asset('assets/marketing/notifications.png') }}
" alt="Notifications" class="card-image">
      <h4 class="card-title">Notifications</h4>
      <p class="card-description">Manage push notifications and alerts for users.</p>
      <a href="marketing/notifications" class="card-btn">Go to Notifications</a>
    </div>

    <!-- Banner Ads Card -->
    <div class="marketing-card">
      <img src="{{ URL::asset('assets/marketing/banner_ads.png') }}" alt="Banner Ads" class="card-image">
      <h4 class="card-title">Banner Ads</h4>
      <p class="card-description">Create and manage banner ads to promote services.</p>
      <a href="marketing/banner-ads" class="card-btn">Go to Banner Ads</a>
    </div>
  </div>
</div>

@endsection
