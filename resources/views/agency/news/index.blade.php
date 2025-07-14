@extends('agency.layout.app')

@section('title', 'News Articles')
@section('header-title', 'News Articles')

@push('styles')
<style>
    .news-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .search-filters {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-control {
        padding: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #f2652d;
        box-shadow: 0 0 0 3px rgba(242, 101, 45, 0.1);
    }

    .news-table {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        background: #f8fafc;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
        border-bottom: 1px solid #e2e8f0;
    }

    .table td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background: #f9fafb;
    }

    .news-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
    }

    .news-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }

    .news-excerpt {
        color: #64748b;
        font-size: 0.875rem;
        line-height: 1.4;
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
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
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
        background: #f2652d;
        color: white;
    }

    .btn-primary:hover {
        background: #e55a26;
        transform: translateY(-1px);
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

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.8rem;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #64748b;
    }

    .empty-state svg {
        width: 64px;
        height: 64px;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 2rem;
    }

    .pagination a, .pagination span {
        padding: 0.5rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        text-decoration: none;
        color: #64748b;
        transition: all 0.2s ease;
    }

    .pagination a:hover {
        background: #f2652d;
        color: white;
        border-color: #f2652d;
    }

    .pagination .active span {
        background: #f2652d;
        color: white;
        border-color: #f2652d;
    }

    @media (max-width: 768px) {
        .news-header {
            flex-direction: column;
            align-items: stretch;
        }

        .search-filters {
            flex-direction: column;
        }

        .table {
            font-size: 0.875rem;
        }

        .table th, .table td {
            padding: 0.75rem;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="news-header">
    <div>
        <h2 style="color: #2c3e50; margin-bottom: 0.5rem;">News Articles</h2>
        <p style="color: #64748b;">Manage your news articles and content</p>
    </div>
    <a href="{{ route('agency.news.create') }}" class="btn btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M5 12h14"/>
        </svg>
        Create Article
    </a>
</div>

<!-- Search and Filters -->
<div class="news-table" style="margin-bottom: 1rem; padding: 1.5rem;">
    <form method="GET" class="search-filters">
        <div class="form-group">
            <input type="text" name="search" placeholder="Search articles..." 
                   value="{{ request('search') }}" class="form-control" style="width: 300px;">
        </div>
        <div class="form-group">
            <select name="status" class="form-control" style="width: 150px;">
                <option value="">All Status</option>
                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
            Search
        </button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('agency.news.index') }}" class="btn" style="background: #f1f5f9; color: #64748b;">
                Clear Filters
            </a>
        @endif
    </form>
</div>

<div class="news-table">
    @if($articles->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title & Excerpt</th>
                  <th>Status</th>
<th>Category</th>
<th>Created</th>
<th>Views</th>
<th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($articles as $article)
                    <tr>
                        <td>
                            @if($article->image)
                                <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="news-image">
                            @else
                                <div class="news-image" style="background: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21,15 16,10 5,21"/>
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="news-title">
                                {{ $article->title }}
                                @if($article->is_featured)
                                    <span class="featured-badge">Featured</span>
                                @endif
                            </div>
                            <div class="news-excerpt">{{ $article->excerpt }}</div>
                        </td>
                         <td>
                            <span class="status-badge {{ $article->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $article->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                       <td>
    @if($article->is_for_mehsana)
        <span class="status-badge" style="background: #dbeafe; color: #1e40af;">Mehsana</span>
    @else
        <span class="status-badge" style="background: #f3f4f6; color: #6b7280;">General</span>
    @endif
</td>
                        <td>
                            <div style="color: #64748b; font-size: 0.875rem;">
                                {{ $article->created_at->format('M d, Y') }}
                            </div>
                            <div style="color: #9ca3af; font-size: 0.75rem;">
                                {{ $article->created_at->format('H:i') }}
                            </div>
                        </td>
                        <td>
    <div style="display: flex; align-items: center; gap: 0.5rem; color: #64748b; font-size: 0.875rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>
        {{ number_format($article->visitor) }}
    </div>
</td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('agency.news.show', $article) }}" class="btn btn-secondary btn-sm">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    View
                                </a>
                                <a href="{{ route('agency.news.edit', $article) }}" class="btn btn-primary btn-sm">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('agency.news.destroy', $article) }}" style="display: inline;" 
                                      onsubmit="return confirm('Are you sure you want to delete this article?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3,6 5,6 21,6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            {{ $articles->links() }}
        </div>
    @else
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14,2 14,8 20,8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10,9 9,9 8,9"/>
            </svg>
            <h3 style="color: #64748b; margin-bottom: 0.5rem;">No articles found</h3>
            <p>Get started by creating your first news article.</p>
            <a href="{{ route('agency.news.create') }}" class="btn btn-primary" style="margin-top: 1rem;">
                Create First Article
            </a>
        </div>
    @endif
</div>
@endsection