@extends('layouts.admin')

@section('title', 'News Agencies')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
.news-agencies-container {
    padding: 15px;
}

.page-header {
    background: linear-gradient(135deg, #34495E 0%, #2c3e50 100%);
    padding: 20px 25px;
    border-radius: 12px;
    margin-bottom: 20px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 15px rgba(52, 73, 94, 0.3);
}

.page-info h2 {
    margin: 0 0 5px 0;
    font-size: 1.6rem;
    font-weight: 600;
}

.page-info p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.9rem;
}

.add-btn {
    background: rgba(255,255,255,0.2);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    backdrop-filter: blur(10px);
}

.add-btn:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-2px);
    color: white;
    text-decoration: none;
}

.filters-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(52, 73, 94, 0.1);
}

.filter-form {
    display: flex;
    gap: 15px;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #34495E;
    font-size: 0.9rem;
}

.filter-input, .filter-select {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #ecf0f1;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.filter-input:focus, .filter-select:focus {
    border-color: #34495E;
    box-shadow: 0 0 0 3px rgba(52, 73, 94, 0.1);
    outline: none;
}

.filter-buttons {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.9rem;
}

.btn-primary {
    background: #34495E;
    color: white;
}

.btn-outline {
    background: white;
    color: #7f8c8d;
    border: 2px solid #ecf0f1;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-primary:hover {
    background: #2c3e50;
}

.btn-outline:hover {
    background: #ecf0f1;
    color: #34495E;
}

.agencies-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.agency-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 3px 15px rgba(52, 73, 94, 0.1);
    transition: all 0.3s ease;
    border-left: 4px solid #34495E;
}

.agency-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(52, 73, 94, 0.2);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
}

.agency-logo {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    object-fit: cover;
    border: 2px solid #ecf0f1;
}

.logo-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    background: #34495E;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    font-weight: 700;
}

.agency-info h3 {
    margin: 0 0 3px 0;
    font-size: 1.1rem;
    color: #2c3e50;
    font-weight: 600;
}

.agency-info .username {
    color: #7f8c8d;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-badge {
    margin-left: auto;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-active {
    background: rgba(39, 174, 96, 0.1);
    color: #27ae60;
}

.status-inactive {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
}

.card-details {
    margin-bottom: 15px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
    color: #5d6d7e;
    font-size: 0.85rem;
}

.detail-item i {
    width: 14px;
    color: #34495E;
}

.admin-info {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.admin-info h4 {
    margin: 0 0 8px 0;
    color: #34495E;
    font-size: 0.9rem;
    font-weight: 600;
}

.card-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

.action-btn {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-edit {
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
}

.btn-delete {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
}

.btn-toggle {
    background: rgba(155, 89, 182, 0.1);
    color: #9b59b6;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}

.alert {
    padding: 12px 18px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.alert-success {
    background: rgba(39, 174, 96, 0.1);
    color: #27ae60;
    border-left: 4px solid #27ae60;
}

.alert-error {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
    border-left: 4px solid #e74c3c;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.1rem;
    cursor: pointer;
    margin-left: auto;
    opacity: 0.7;
}

.close-btn:hover {
    opacity: 1;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #7f8c8d;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(52, 73, 94, 0.1);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.3;
    color: #34495E;
}

.empty-state h3 {
    margin-bottom: 10px;
    color: #34495E;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .filter-form {
        flex-direction: column;
    }
    
    .filter-buttons {
        width: 100%;
        justify-content: stretch;
    }
    
    .filter-buttons .btn {
        flex: 1;
        justify-content: center;
    }
    
    .agencies-grid {
        grid-template-columns: 1fr;
    }
    
    .card-actions {
        flex-wrap: wrap;
    }
}
</style>

<div class="news-agencies-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-info">
            <h2><i class="fas fa-newspaper"></i> News Agencies</h2>
            <p>Manage news agencies and administrators</p>
        </div>
        <a href="{{ route('admin.news-agencies.create') }}" class="add-btn">
            <i class="fas fa-plus"></i> Add Agency
        </a>
    </div>

    <!-- Filters -->
    <div class="filters-card">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label class="filter-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search agencies..." class="filter-input">
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <div class="filter-buttons">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="{{ route('admin.news-agencies') }}" class="btn btn-outline">
                    <i class="fas fa-refresh"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Alerts -->
    @if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
        <button class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
        <button class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
    @endif

    <!-- Agencies Grid -->
    @if($agencies->count() > 0)
    <div class="agencies-grid">
        @foreach($agencies as $agency)
        <div class="agency-card">
            <div class="card-header">
                @if($agency->logo)
                    <img src="{{ asset('storage/' . $agency->logo) }}" alt="Logo" class="agency-logo">
                @else
                    <div class="logo-placeholder">
                        {{ strtoupper(substr($agency->name, 0, 1)) }}
                    </div>
                @endif
                
                <div class="agency-info">
                    <h3>{{ $agency->name }}</h3>
                    <div class="username">{{"@"}}{{ $agency->username }}</div>
                </div>
                
                <span class="status-badge status-{{ $agency->status ? 'active' : 'inactive' }}">
                    {{ $agency->status ? 'active' : 'inactive' }}
                </span>
            </div>

            <div class="card-details">
                <div class="detail-item">
                    <i class="fas fa-envelope"></i>
                    <span>{{ $agency->email }}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-calendar"></i>
                    <span>{{ $agency->created_at->format('M d, Y') }}</span>
                </div>
            </div>

            @if($agency->admin)
            <div class="admin-info">
                <h4><i class="fas fa-user-shield"></i> Administrator</h4>
                <div class="detail-item">
                    <i class="fas fa-user"></i>
                    <span>{{ $agency->admin->name }}</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-envelope"></i>
                    <span>{{ $agency->admin->email }}</span>
                </div>
                @if($agency->admin->phone)
                <div class="detail-item">
                    <i class="fas fa-phone"></i>
                    <span>{{ $agency->admin->phone }}</span>
                </div>
                @endif
            </div>
            @endif

            <div class="card-actions">
                <a href="{{ route('admin.news-agencies.edit', $agency->id) }}" class="action-btn btn-edit">
                    <i class="fas fa-edit"></i> Edit
                </a>
                
                <button onclick="toggleStatus({{ $agency->id }})" class="action-btn btn-toggle">
                    <i class="fas fa-toggle-{{ $agency->status ? 'on' : 'off' }}"></i>
                    {{ $agency->status ? 'Disable' : 'Enable' }}
                </button>
                
                <form method="POST" action="{{ route('admin.news-agencies.destroy', $agency->id) }}" 
                      style="display: inline;" onsubmit="return confirmDelete()">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn btn-delete">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="pagination-container">
        {{ $agencies->appends(request()->query())->links() }}
    </div>
    @else
    <div class="empty-state">
        <i class="fas fa-newspaper"></i>
        <h3>No News Agencies Found</h3>
        <p>Start by adding your first news agency.</p>
        <a href="{{ route('admin.news-agencies.create') }}" class="btn btn-primary" style="margin-top: 15px;">
           Add First Agency
        </a>
    </div>
    @endif
</div>

<script>
function confirmDelete() {
    return confirm('Are you sure you want to delete this news agency? This action cannot be undone.');
}

function toggleStatus(agencyId) {
    if (!confirm('Are you sure you want to change the status of this agency?')) {
        return;
    }
    
    fetch(`/admin/news-agencies/${agencyId}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Something went wrong!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong!');
    });
}
</script>
@endsection