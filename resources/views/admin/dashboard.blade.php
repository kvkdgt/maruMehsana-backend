@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<!-- Header Banner with Stats Summary -->
<div class="header-banner">
  <div class="banner-content">
    <h1>Welcome, {{ Auth::user()->name }}</h1>
    <p>Here's your business overview for today</p>
    <div class="banner-stats">
      <div class="banner-stat">
        <span class="count">{{ $totalCategories }}</span>
        <span class="label">Categories</span>
      </div>
      <div class="banner-stat">
        <span class="count">{{ $totalBusinesses }}</span>
        <span class="label">Businesses</span>
      </div>
      <div class="banner-stat">
        <span class="count">{{ $totalAppUsers ?? 0 }}</span>
        <span class="label">App Users</span>
      </div>
      <div class="banner-stat">
        <span class="count">{{ $totalTouristPlaces ?? 0 }}</span>
        <span class="label">Tourist Places</span>
      </div>
    </div>
  </div>
</div>

<!-- Quick Actions Section -->
<div class="quick-actions-container">
  <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
  <div class="actions">
    <a href="{{ url('admin/categories') }}" class="quick-action-btn category-btn">
      <i class="fas fa-folder-plus"></i> Categories
    </a>
    <a href="{{ url('admin/businesses/create') }}" class="quick-action-btn business-btn">
      <i class="fas fa-store"></i> Add Business
    </a>
    <a href="{{ url('admin/tourist-places') }}" class="quick-action-btn tourist-btn">
      <i class="fas fa-map-marker-alt"></i>  Tourist Place
    </a>
    <a href="{{ url('admin/marketing/notifications') }}" class="quick-action-btn notification-btn">
      <i class="fas fa-bell"></i> Push Notifications
    </a>
  </div>
</div>

<!-- Main Dashboard Cards -->
<div class="dashboard-container">
  <!-- First Row: Key Metrics -->
  <div class="card-container">
    <!-- Categories Card -->
    <div class="stat-card category-card">
      <div class="card-icon">
        <i class="fas fa-folder"></i>
      </div>
      <div class="stat-content">
        <h3>Categories</h3>
        <p class="count">{{ $totalCategories }}</p>
        <div class="stat-footer">
          <span class="stat-label">Total Visitors</span>
          <span class="stat-value">{{ $totalCategoryVisitors ?? 0 }}</span>
        </div>
        <a href="{{ url('admin/categories') }}" class="card-link">Manage Categories</a>
      </div>
    </div>

    <!-- Businesses Card -->
    <div class="stat-card business-card">
      <div class="card-icon">
        <i class="fas fa-store"></i>
      </div>
      <div class="stat-content">
        <h3>Businesses</h3>
        <p class="count">{{ $totalBusinesses }}</p>
        <div class="stat-footer">
          <span class="stat-label">Total Visitors</span>
          <span class="stat-value">{{ $totalBusinessVisitors ?? 0 }}</span>
        </div>
        <a href="{{ url('admin/businesses') }}" class="card-link">Manage Businesses</a>
      </div>
    </div>

    <!-- Tourist Places Card -->
    <div class="stat-card tourist-card">
      <div class="card-icon">
        <i class="fas fa-map-marked-alt"></i>
      </div>
      <div class="stat-content">
        <h3>Tourist Places</h3>
        <p class="count">{{ $totalTouristPlaces ?? 0 }}</p>
        <div class="stat-footer">
          <span class="stat-label">Total Visitors</span>
          <span class="stat-value">{{ $totalTouristVisitors ?? 0 }}</span>
        </div>
        <a href="{{ url('admin/tourist-places') }}" class="card-link">Manage Places</a>
      </div>
    </div>
  </div>

  <!-- Second Row: App Users & Enquiries -->
  <div class="dual-container">
    <!-- App Users Card -->
    <div class="wide-card user-card">
      <div class="wide-card-header">
        <h3><i class="fas fa-users"></i> App Users</h3>
        <span class="badge">{{ $totalAppUsers ?? 0 }} Total</span>
      </div>
      <div class="wide-card-body">
        <div class="user-stats">
          <div class="user-stat">
            <span class="stat-value">{{ $activeUsers ?? 0 }}</span>
            <span class="stat-label">Active Users</span>
          </div>
          <div class="user-stat">
            <span class="stat-value">{{ $newUsersToday ?? 0 }}</span>
            <span class="stat-label">New Today</span>
          </div>
        </div>
        @if(isset($recentUsers) && count($recentUsers) > 0)
        <div class="recent-users">
          <h4>Recent Users</h4>
          <div class="user-list">
            @foreach($recentUsers as $user)
            <div class="user-item">
              <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
              </div>
              <div class="user-info-dashboard">
                <span class="user-name">{{ $user->name }}</span>
                <span class="user-email">{{ $user->email }}</span>
              </div>
              <div class="user-status {{ $user->is_login ? 'online' : 'offline' }}">
                {{ $user->is_login ? 'Online' : 'Not Loggedin' }}
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @else
        <div class="no-data">
          <p>No recent users to display</p>
        </div>
        @endif
        <a href="{{ url('admin/app-users') }}" class="view-all-btn">View All Users</a>
      </div>
    </div>

    <!-- Business Enquiries Card -->
    <div class="wide-card enquiry-card">
      <div class="wide-card-header">
        <h3><i class="fas fa-envelope-open-text"></i> Business Enquiries</h3>
        <span class="badge">{{ $pendingEnquiries ?? 0 }} Pending</span>
      </div>
      <div class="wide-card-body">
        @if(isset($recentEnquiries) && count($recentEnquiries) > 0)
        <div class="enquiry-list">
          @foreach($recentEnquiries as $enquiry)
          <div class="enquiry-item">
            <div class="enquiry-content">
              <h4>{{ $enquiry->business_name }}</h4>
              <p>Owner: {{ $enquiry->owner_name }}</p>
              <div class="enquiry-contact">
                <span><i class="fas fa-phone"></i> {{ $enquiry->mobile_no }}</span>
                <span><i class="fab fa-whatsapp"></i> {{ $enquiry->whatsapp_no }}</span>
              </div>
            </div>
            <div class="enquiry-status {{ strtolower($enquiry->status) }}">
              {{ $enquiry->status }}
            </div>
          </div>
          @endforeach
        </div>
        @else
        <div class="no-data">
          <p>No recent enquiries to display</p>
        </div>
        @endif
        <a href="{{ url('admin/business-enquiry') }}" class="view-all-btn">View All Enquiries</a>
      </div>
    </div>
  </div>

  <!-- Third Row: Banner Ads & Quick Facts -->
  <div class="dual-container">
    <!-- Banner Ads Card -->
    <div class="wide-card ads-card">
      <div class="wide-card-header">
        <h3><i class="fas fa-ad"></i> Banner Ads</h3>
        <a href="{{ url('admin/marketing/banner-ads') }}" class="add-btn"><i class="fas fa-plus"></i> Add New</a>
      </div>
      <div class="wide-card-body">
        @if(isset($activeBannerAds) && count($activeBannerAds) > 0)
        <div class="banner-ads-container">
          @foreach($activeBannerAds as $ad)
          <div class="banner-ad-item">
            <div class="banner-ad-image">
              <img src="{{ asset('storage/'.$ad->image) }}" alt="{{ $ad->title }}">
            </div>
            <div class="banner-ad-info">
              <h4>{{ $ad->title }}</h4>
              <a href="{{ $ad->link }}" class="ad-link" target="_blank">{{ $ad->link }}</a>
              <div class="ad-status {{ $ad->status ? 'active' : 'inactive' }}">
                {{ $ad->status ? 'Active' : 'Inactive' }}
              </div>
            </div>
          </div>
          @endforeach
        </div>
        @else
        <div class="no-data">
          <p>No active banner ads to display</p>
        </div>
        @endif
        <a href="{{ url('admin/marketing/banner-ads') }}" class="view-all-btn">Manage Banner Ads</a>
      </div>
    </div>

    <!-- Quick Facts Card -->
    <div class="wide-card facts-card">
      <div class="wide-card-header">
        <h3><i class="fas fa-lightbulb"></i> Daily Facts</h3>
        <a href="{{ url('admin/facts') }}" class="add-btn"><i class="fas fa-plus"></i> Add New</a>
      </div>
      <div class="wide-card-body">
        @if(isset($randomFacts) && count($randomFacts) > 0)
        <div class="fact-carousel">
          @foreach($randomFacts as $fact)
          <div class="fact-item">
            <div class="fact-icon">
              <i class="fas fa-quote-left"></i>
            </div>
            <p>{{ $fact->fact }}</p>
          </div>
          @endforeach
        </div>
        @else
        <div class="no-data">
          <p>No facts available</p>
        </div>
        @endif
        <a href="{{ url('admin/facts') }}" class="view-all-btn">Manage Facts</a>
      </div>
    </div>
  </div>
</div>

<style>
  /* Base Styles */
  :root {
    --primary-color: #2c3e50;
    --secondary-color: #2ecc71;
    --accent-color: #e74c3c;
    --dark-color: #34495e;
    --light-color: #ecf0f1;
    --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    --hover-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    --border-radius: 15px;
    --card-padding: 25px;
    --transition: all 0.3s ease;
  }

  .dashboard-container {
    background-color: #f5f7fb;
    border-radius: var(--border-radius);
    padding: 20px;
    font-family: 'Poppins', sans-serif;
  }

  /* Header Banner */
  .header-banner {
    background: linear-gradient(135deg, #2c3e50, #516395);
    border-radius: var(--border-radius);
    padding: 40px;
    margin-bottom: 25px;
    color: white;
    box-shadow: var(--shadow);
    overflow: hidden;
    position: relative;
  }

  .header-banner::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 300px;
    height: 100%;
    background: url('data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMjAwIDIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMCAwQzQwIDQwIDYwIDgwIDgwIDEyMEMxMDAgMTYwIDE0MCAxNjAgMjAwIDEyMFYwSDAiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4xKSIvPjwvc3ZnPg==') no-repeat;
    background-size: cover;
    opacity: 0.3;
  }

  .banner-content h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 700;
  }

  .banner-content p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 30px;
  }

  .banner-stats {
    display: flex;
    gap: 30px;
  }

  .banner-stat {
    display: flex;
    flex-direction: column;
  }

  .banner-stat .count {
    font-size: 2.2rem;
    font-weight: 700;
  }

  .banner-stat .label {
    font-size: 0.95rem;
    opacity: 0.8;
  }

  /* Quick Actions */
  .quick-actions-container {
    text-align: center;
    padding: 25px;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    margin-bottom: 25px;
    position: relative;
    overflow: hidden;
  }

  .quick-actions-container h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: var(--dark-color);
  }

  .quick-actions-container h3 i {
    color: #f39c12;
    margin-right: 10px;
  }

  .actions {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 20px;
  }

  .quick-action-btn {
    padding: 15px 25px;
    font-size: 1rem;
    border-radius: 30px;
    text-decoration: none;
    color: white;
    text-align: center;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: var(--shadow);
    min-width: 160px;
  }

  .quick-action-btn i {
    font-size: 1.1rem;
  }

  .quick-action-btn:hover {
    transform: translateY(-5px);
    box-shadow: var(--hover-shadow);
  }

  .category-btn {
    background: linear-gradient(135deg, #FF9966, #FF5E62);
  }

  .business-btn {
    background: linear-gradient(135deg, #36D1DC, #5B86E5);
  }

  .tourist-btn {
    background: linear-gradient(135deg, #11998e, #38ef7d);
  }

  .notification-btn {
    background: linear-gradient(135deg, #8E2DE2, #4A00E0);
  }

  /* Card Container */
  .card-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 25px;
    margin-bottom: 25px;
  }

  .stat-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: var(--card-padding);
    display: flex;
    align-items: center;
    transition: var(--transition);
    overflow: hidden;
    position: relative;
  }

  .stat-card:hover {
    box-shadow: var(--hover-shadow);
    transform: translateY(-5px);
  }

  .card-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    flex-shrink: 0;
  }

  .card-icon i {
    font-size: 30px;
    color: white;
  }

  .category-card .card-icon {
    background: linear-gradient(135deg, #FF9966, #FF5E62);
  }

  .business-card .card-icon {
    background: linear-gradient(135deg, #36D1DC, #5B86E5);
  }

  .tourist-card .card-icon {
    background: linear-gradient(135deg, #11998e, #38ef7d);
  }

  .stat-content {
    flex-grow: 1;
  }

  .stat-content h3 {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--dark-color);
  }

  .stat-card .count {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 10px;
  }

  .stat-footer {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    margin-bottom: 15px;
    color: #777;
  }

  .card-link {
    display: inline-block;
    padding: 8px 15px;
    background-color: #f5f7fb;
    border-radius: 20px;
    text-decoration: none;
    color: var(--dark-color);
    font-size: 0.85rem;
    transition: var(--transition);
  }

  .card-link:hover {
    background-color: #e0e6ed;
  }

  /* Dual Container Layout */
  .dual-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
    margin-bottom: 25px;
  }

  /* Wide Cards */
  .wide-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    transition: var(--transition);
    overflow: hidden;
  }

  .wide-card:hover {
    box-shadow: var(--hover-shadow);
  }

  .wide-card-header {
    padding: 20px 25px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .wide-card-header h3 {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .wide-card-header i {
    color: var(--primary-color);
  }

  .badge {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
  }

  .wide-card-body {
    padding: 25px;
  }

  /* User Card */
  .user-card .badge {
    background-color: #e3f2fd;
    color: var(--primary-color);
  }

  .user-stats {
    display: flex;
    gap: 30px;
    margin-bottom: 25px;
  }

  .user-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .user-stat .stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--dark-color);
  }

  .user-stat .stat-label {
    font-size: 0.9rem;
    color: #777;
  }

  .recent-users h4 {
    font-size: 1.1rem;
    margin-bottom: 15px;
    color: var(--dark-color);
  }

  .user-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
  }

  .user-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    background-color: #fafafa;
    border-radius: 10px;
  }

  .user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e3f2fd;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
  }

  .user-avatar i {
    color: var(--primary-color);
  }

  .user-info-dashboard {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
  }

  .user-name {
    font-weight: 500;
    color: var(--dark-color);
  }

  .user-email {
    font-size: 0.85rem;
    color: #777;
  }

  .user-status {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
  }

  .user-status.online {
    background-color: #e3f5e9;
    color: var(--secondary-color);
  }

  .user-status.offline {
    background-color: #feeae9;
    color: var(--accent-color);
  }

  /* Enquiry Card */
  .enquiry-card .badge {
    background-color: #feeae9;
    color: var(--accent-color);
  }

  .enquiry-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
  }

  .enquiry-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background-color: #fafafa;
    border-radius: 10px;
  }

  .enquiry-content h4 {
    font-size: 1rem;
    margin-bottom: 5px;
    color: var(--dark-color);
  }

  .enquiry-content p {
    font-size: 0.9rem;
    margin-bottom: 8px;
    color: #555;
  }

  .enquiry-contact {
    display: flex;
    gap: 15px;
    font-size: 0.85rem;
    color: #777;
  }

  .enquiry-status {
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
  }

  .enquiry-status.pending {
    background-color: #fff8e1;
    color: #ffa000;
  }

  .enquiry-status.approved {
    background-color: #e3f5e9;
    color: var(--secondary-color);
  }

  .enquiry-status.rejected {
    background-color: #feeae9;
    color: var(--accent-color);
  }

  /* Banner Ads Card */
  .ads-card .wide-card-header i {
    color: #f39c12;
  }

  .add-btn {
    padding: 8px 15px;
    background-color: #f5f7fb;
    border-radius: 20px;
    text-decoration: none;
    color: var(--dark-color);
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: var(--transition);
  }

  .add-btn:hover {
    background-color: #e0e6ed;
  }

  .banner-ads-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }

  .banner-ad-item {
    background-color: #fafafa;
    border-radius: 10px;
    overflow: hidden;
  }

  .banner-ad-image {
    height: 120px;
    overflow: hidden;
  }

  .banner-ad-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .banner-ad-info {
    padding: 15px;
  }

  .banner-ad-info h4 {
    font-size: 1rem;
    margin-bottom: 8px;
    color: var(--dark-color);
  }

  .ad-link {
    display: block;
    font-size: 0.85rem;
    color: var(--primary-color);
    margin-bottom: 10px;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
  }

  .ad-status {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 10px;
    font-size: 0.75rem;
  }

  .ad-status.active {
    background-color: #e3f5e9;
    color: var(--secondary-color);
  }

  .ad-status.inactive {
    background-color: #feeae9;
    color: var(--accent-color);
  }

  /* Facts Card */
  .facts-card .wide-card-header i {
    color: #f39c12;
  }

  .fact-carousel {
    display: flex;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    gap: 20px;
    padding-bottom: 20px;
  }

  .fact-item {
    min-width: 280px;
    padding: 20px;
    background-color: #fafafa;
    border-radius: 10px;
    scroll-snap-align: start;
    position: relative;
  }

  .fact-icon {
    position: absolute;
    top: 10px;
    left: 10px;
    color: #ddd;
    font-size: 1.5rem;
  }

  .fact-item p {
    font-style: italic;
    padding-left: 25px;
    line-height: 1.6;
    color: #555;
  }

  /* Common Button Styles */
  .view-all-btn {
    display: block;
    text-align: center;
    padding: 12px;
    background-color: #f5f7fb;
    border-radius: 10px;
    text-decoration: none;
    color: var(--dark-color);
    margin-top: 20px;
    transition: var(--transition);
  }

  .view-all-btn:hover {
    background-color: #e0e6ed;
  }

  /* No Data State */
  .no-data {
    padding: 30px;
    text-align: center;
    color: #999;
    background-color: #fafafa;
    border-radius: 10px;
  }

  /* Responsive Design */
  @media (max-width: 1200px) {
    .banner-stats {
      flex-wrap: wrap;
    }
    
    .card-container {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 992px) {
    .dual-container {
      grid-template-columns: 1fr;
    }
    
    .banner-ads-container {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .card-container {
      grid-template-columns: 1fr;
    }
    
    .actions {
      flex-direction: column;
    }
    
    .quick-action-btn {
      width: 100%;
    }
    
    .header-banner {
      padding: 25px;
    }
    
    .banner-content h1 {
      font-size: 1.8rem;
    }
    
    .banner-stats {
      flex-direction: column;
      gap: 15px;
    }
    
    .user-stats {
      flex-wrap: wrap;
    }
  }
</style>

<!-- Include Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Include Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<script>
  // Simple carousel effect for facts
  document.addEventListener('DOMContentLoaded', function() {
    // Auto-scroll the fact carousel
    const factCarousel = document.querySelector('.fact-carousel');
    if (factCarousel && factCarousel.childElementCount > 1) {
      setInterval(() => {
        factCarousel.scrollBy({
          left: 320,
          behavior: 'smooth'
        });
        
        // Reset scroll position if at the end
        if (factCarousel.scrollLeft + factCarousel.clientWidth >= factCarousel.scrollWidth - 50) {
          setTimeout(() => {
            factCarousel.scrollTo({ left: 0, behavior: 'smooth' });
          }, 1000);
        }
      }, 5000);
    }
  });
</script>
@endsection