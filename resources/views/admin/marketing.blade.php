@extends('layouts.admin')

@section('title', 'Marketing Dashboard')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/marketing.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="marketing-dashboard">
    <!-- Header Section -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1 class="dashboard-title">Marketing Hub</h1>
            <p class="dashboard-subtitle">Manage your marketing channels and campaigns in one place</p>
        </div>
        <!-- <div class="dashboard-stats">
            <div class="stat-item">
                <span class="stat-value">2.4k</span>
                <span class="stat-label">Active Users</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">67%</span>
                <span class="stat-label">Engagement</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">12</span>
                <span class="stat-label">Campaigns</span>
            </div>
        </div> -->
    </div>

    <!-- Marketing Tools Section -->
    <div class="marketing-tools">
        <h2 class="section-title">Marketing Tools</h2>
        
        <div class="tools-grid">
            <!-- Notifications Tool -->
            <div class="tool-card notifications-card">
                <div class="card-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Notifications</h3>
                    <p class="card-description">Create and schedule push notifications, alerts and in-app messages to engage users.</p>
                    <div class="card-meta">
                        <!-- <span class="meta-stat"><i class="fas fa-paper-plane"></i> 24 active</span> -->
                        <span class="meta-stat"><i class="fas fa-clock"></i> {{$scheduledCount}} scheduled</span>
                    </div>
                    <a href="marketing/notifications" class="card-btn">
                        Manage Notifications <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Banner Ads Tool -->
            <div class="tool-card banner-ads-card">
                <div class="card-icon">
                    <i class="fas fa-ad"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Banner Ads</h3>
                    <p class="card-description">Design eye-catching banner advertisements and promotions to boost conversion rates.</p>
                    <div class="card-meta">
                        <span class="meta-stat"><i class="fas fa-image"></i> {{$activeBannerCount}} active</span>
                        <!-- <span class="meta-stat"><i class="fas fa-chart-line"></i> 3.2% CTR</span> -->
                    </div>
                    <a href="marketing/banner-ads" class="card-btn">
                        Manage Banner Ads <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Email Campaigns Tool -->
            <!-- <div class="tool-card email-card">
                <div class="card-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Email Campaigns</h3>
                    <p class="card-description">Create and automate email marketing campaigns to nurture leads and customers.</p>
                    <div class="card-meta">
                        <span class="meta-stat"><i class="fas fa-paper-plane"></i> 5 campaigns</span>
                        <span class="meta-stat"><i class="fas fa-chart-line"></i> 28% open rate</span>
                    </div>
                    <a href="marketing/email-campaigns" class="card-btn">
                        Manage Email Campaigns <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div> -->

            <!-- Analytics Tool -->
            <!-- <div class="tool-card analytics-card">
                <div class="card-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Marketing Analytics</h3>
                    <p class="card-description">Track performance metrics and gain insights into your marketing efforts.</p>
                    <div class="card-meta">
                        <span class="meta-stat"><i class="fas fa-users"></i> User growth</span>
                        <span class="meta-stat"><i class="fas fa-percentage"></i> Conversion rates</span>
                    </div>
                    <a href="marketing/analytics" class="card-btn">
                        View Analytics <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div> -->
        </div>
    </div>

    <!-- Marketing Activity -->
    <!-- <div class="marketing-activity">
        <div class="activity-header">
            <h2 class="section-title">Recent Activity</h2>
            <a href="marketing/activity" class="view-all">View All <i class="fas fa-chevron-right"></i></a>
        </div>
        <div class="activity-list">
            <div class="activity-item">
                <div class="activity-icon notification-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="activity-details">
                    <h4 class="activity-title">New Feature Announcement</h4>
                    <p class="activity-description">Push notification sent to 2,450 users</p>
                    <span class="activity-time">2 hours ago</span>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-icon banner-icon">
                    <i class="fas fa-ad"></i>
                </div>
                <div class="activity-details">
                    <h4 class="activity-title">Spring Sale Banner</h4>
                    <p class="activity-description">Banner published on homepage</p>
                    <span class="activity-time">Yesterday</span>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-icon email-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="activity-details">
                    <h4 class="activity-title">Weekly Newsletter</h4>
                    <p class="activity-description">Sent to 1,845 subscribers</p>
                    <span class="activity-time">2 days ago</span>
                </div>
            </div>
        </div>
    </div> -->
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add subtle animation to stats
    const statItems = document.querySelectorAll('.stat-item');
    statItems.forEach((item, index) => {
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, 100 * index);
    });
});
</script>
@endsection