@extends('agency.layout.app')

@section('title', $news->title)
@section('header-title', 'View Article')

@push('styles')
<style>
    .article-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .article-header {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .article-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-active {
        background: #dcfce7;
        color: #166534;
    }

    .status-inactive {
        background: #fef2f2;
        color: #991b1b;
    }

    .featured-badge {
        background: linear-gradient(135deg, #f2652d, #e55a26);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #64748b;
        font-size: 0.875rem;
    }

    .article-title {
        color: #2c3e50;
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 1rem;
    }

    .article-excerpt {
        color: #64748b;
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: #f8fafc;
        border-left: 4px solid #f2652d;
        border-radius: 0 8px 8px 0;
    }

    .article-body {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .article-image {
        width: 100%;
        height: 300px;
        object-fit: cover;
        display: block;
    }

    .article-content {
        padding: 2rem;
    }

    .article-text {
        color: #374151;
        font-size: 1rem;
        line-height: 1.7;
        white-space: pre-line;
    }

    .action-bar {
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 1.5rem 2rem;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #f2652d, #e55a26);
        color: white;
        box-shadow: 0 2px 8px rgba(242, 101, 45, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(242, 101, 45, 0.4);
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    .btn-danger {
        background: #ef4444;
        color: white;
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    .btn-outline {
        background: transparent;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .btn-outline:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .article-details {
        background: #f8fafc;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .detail-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-value {
        font-size: 0.875rem;
        color: #374151;
        font-weight: 500;
    }

    .no-image-placeholder {
        height: 300px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 1.1rem;
    }

    @media (max-width: 768px) {
        .article-container {
            margin: 0 1rem;
        }

        .article-header, .article-content {
            padding: 1.5rem;
        }

        .article-title {
            font-size: 1.5rem;
        }

        .action-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .action-buttons {
            justify-content: center;
        }

        .article-meta {
            flex-direction: column;
            align-items: flex-start;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="article-container">
    <!-- Article Header -->
    <div class="article-header">
        <div class="article-meta">
            <div class="status-badge {{ $news->is_active ? 'status-active' : 'status-inactive' }}">
                {{ $news->is_active ? 'Active' : 'Inactive' }}
            </div>
            
            @if($news->is_featured)
                <div class="featured-badge">Featured</div>
            @endif
@if($news->is_for_mehsana)
    <div class="featured-badge" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
        Mehsana
    </div>
@endif
            <div class="meta-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12,6 12,12 16,14"/>
                </svg>
                Created {{ $news->created_at->format('M d, Y \a\t H:i') }}
            </div>

            @if($news->updated_at != $news->created_at)
                <div class="meta-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 4v6h6"/>
                        <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                    </svg>
                    Updated {{ $news->updated_at->format('M d, Y \a\t H:i') }}
                </div>
            @endif
        </div>

        <h1 class="article-title">{{ $news->title }}</h1>
        
        <div class="article-excerpt">
            {{ $news->excerpt }}
        </div>

        <div class="article-details">
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Article ID</span>
                    <span class="detail-value">#{{ $news->id }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Slug</span>
                    <span class="detail-value">{{ $news->slug }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Author</span>
                    <span class="detail-value">{{ $news->agency->name }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">{{ $news->is_active ? 'Published' : 'Draft' }}</span>
                </div>
                <div class="detail-item">
    <span class="detail-label">Category</span>
    <span class="detail-value">{{ $news->is_for_mehsana ? 'Mehsana' : 'General' }}</span>
</div>
<div class="detail-item">
    <span class="detail-label">Total Views</span>
    <span class="detail-value">{{ number_format($news->visitor) }}</span>
</div>
            </div>
        </div>
    </div>

    <!-- Article Body -->
    <div class="article-body">
        @if($news->image)
            <img src="{{ $news->image_url }}" alt="{{ $news->title }}" class="article-image">
        @else
            <div class="no-image-placeholder">
                <div style="text-align: center;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-bottom: 0.5rem;">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21,15 16,10 5,21"/>
                    </svg>
                    <div>No featured image</div>
                </div>
            </div>
        @endif

        <div class="article-content">
            <div class="article-text">{!! $news->content !!}</div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <div class="action-buttons">
            <a href="{{ route('agency.news.index') }}" class="btn btn-outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Articles
            </a>
        </div>

        <div class="action-buttons">
            <a href="{{ route('agency.news.edit', $news) }}" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit Article
            </a>

            <form method="POST" action="{{ route('agency.news.destroy', $news) }}" style="display: inline;" 
                  onsubmit="return confirm('Are you sure you want to delete this article? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3,6 5,6 21,6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                    Delete Article
                </button>
            </form>
        </div>
    </div>
</div>
@endsection