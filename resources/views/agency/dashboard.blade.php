{{-- resources/views/agency/dashboard.blade.php --}}
@extends('agency.layout.app')

@section('title', 'Dashboard - ' . $agency->name)

@section('content')
<div class="dashboard-container">
    <!-- Welcome Header -->
    <div class="welcome-header">
        <div class="welcome-content">
            <h1 class="welcome-title">Welcome back, {{ $admin->name }}!</h1>
            <p class="welcome-subtitle">{{ $agency->name }} • {{ now()->format('l, F j, Y') }}</p>
        </div>
        <div class="agency-logo">
            @if($agency->logo)
                <img src="{{ $agency->logo_url }}" alt="{{ $agency->name }}" class="logo-img">
            @else
                <div class="logo-placeholder">{{ $agency->initial }}</div>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10,9 9,9 8,9"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($stats['total_articles']) }}</div>
                <div class="stat-label">Total Articles</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12,6 12,12 16,14"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($stats['published_today']) }}</div>
                <div class="stat-label">Published Today</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($stats['draft_articles']) }}</div>
                <div class="stat-label">Draft Articles</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($stats['total_views']) }}</div>
                <div class="stat-label">Total Views</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2 class="section-title">Quick Actions</h2>
        <div class="actions-grid">
            <a href="#" class="action-card">
                <div class="action-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <div class="action-content">
                    <div class="action-title">Create Article</div>
                    <div class="action-desc">Write a new article</div>
                </div>
            </a>

            <a href="#" class="action-card">
                <div class="action-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                    </svg>
                </div>
                <div class="action-content">
                    <div class="action-title">Manage Articles</div>
                    <div class="action-desc">Edit existing content</div>
                </div>
            </a>

            <a href="#" class="action-card">
                <div class="action-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/>
                    </svg>
                </div>
                <div class="action-content">
                    <div class="action-title">Categories</div>
                    <div class="action-desc">Manage categories</div>
                </div>
            </a>

            <a href="{{ route('agency.profile') }}" class="action-card">
                <div class="action-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div class="action-content">
                    <div class="action-title">Profile Settings</div>
                    <div class="action-desc">Update your profile</div>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.welcome-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 2rem;
    background: linear-gradient(135deg, #2c3e50, #34495e);
    color: white;
    border-radius: 16px;
}

.welcome-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.welcome-subtitle {
    opacity: 0.8;
    font-size: 1rem;
}

.agency-logo .logo-img {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    object-fit: cover;
}

.logo-placeholder {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #2c3e50, #34495e);
    color: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
}

.stat-label {
    color: #64748b;
    font-size: 0.875rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #2c3e50;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
}

.action-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 1rem;
    text-decoration: none;
    transition: all 0.3s ease;
}

.action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.action-icon {
    width: 40px;
    height: 40px;
    background: #f8fafc;
    color: #2c3e50;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.action-desc {
    font-size: 0.875rem;
    color: #64748b;
}

@media (max-width: 768px) {
    .welcome-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection