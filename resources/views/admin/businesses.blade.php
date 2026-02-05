@extends('layouts.admin')

@section('title', 'Businesses')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/businesses.css'); }}">
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">

<div class="businesses-container">
  <div class="header-container">
    <div>
      <h3 class="categories-label" style="font-size: 1.5rem; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">Businesses</h3>
      <p class="categories-description" style="color: #777; font-size: 0.95rem;">Manage and explore all registered businesses</p>
    </div>

    <button class="btn-add-premium" id="openModalBtn" onclick="window.location.href='{{ url('admin/businesses/create') }}'">
      <i class="fas fa-plus-circle"></i> Add New Business
    </button>
  </div>

  <div class="filter-section">
    <form action="{{ route('admin.businesses') }}" method="GET">
      <div class="filter-grid">
        <div class="filter-control">
          <label>Search</label>
          <input type="text" name="search" placeholder="Business name, products..." value="{{ request()->search }}" class="filter-input">
        </div>

        <div class="filter-control">
          <label>Category</label>
          <select name="category_id" class="filter-input">
            <option value="">All Categories</option>
            @foreach($categories as $category)
              <option value="{{ $category->id }}" {{ request()->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="filter-control">
          <label>Sort By</label>
          <select name="sort_by" class="filter-input">
            <option value="newest" {{ request()->sort_by == 'newest' ? 'selected' : '' }}>Newest First</option>
            <option value="highest" {{ request()->sort_by == 'highest' ? 'selected' : '' }}>Most Visitors</option>
            <option value="lowest" {{ request()->sort_by == 'lowest' ? 'selected' : '' }}>Least Visitors</option>
            <option value="oldest" {{ request()->sort_by == 'oldest' ? 'selected' : '' }}>Oldest First</option>
          </select>
        </div>

        <div class="btn-group">
          <button type="submit" class="filter-btn">Apply</button>
          <a href="{{ route('admin.businesses') }}" class="reset-btn">Reset</a>
        </div>
      </div>
    </form>
  </div>

  @if (session('success'))
  <div class="alert alert-success">
    <span class="alert-icon">&#10004;</span>
    <div class="alert-text">{{ session('success') }}</div>
    <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
  </div>
  @endif

  <div class="premium-table-container">
    <table class="premium-table">
      <thead>
        <tr>
          <th>Sr. No.</th>
          <th>Business Info</th>
          <th>Category</th>
          <th>Visitors</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($businesses as $index => $business)
        <tr>
          <td>{{ $businesses->firstItem() + $index }}</td>
          <td>
            <div style="display: flex; align-items: center; gap: 15px;">
                @if($business->thumbnail)
                    <img src="{{ asset('storage/' . $business->thumbnail) }}" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
                @else
                    <div style="width: 50px; height: 50px; border-radius: 8px; background: #eee; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-store" style="color: #ccc;"></i>
                    </div>
                @endif
                <div style="display: flex; flex-direction: column;">
                    <span style="font-weight: 600; color: #2c3e50;">{{ $business->name }}</span>
                    <span style="font-size: 0.8rem; color: #777;">{{ Str::limit($business->description, 50) }}</span>
                </div>
            </div>
          </td>
          <td>
            <span class="badge-premium badge-info">{{ $business->category->name }}</span>
          </td>
          <td>
            <div style="display: flex; align-items: center; gap: 5px;">
                <i class="fas fa-eye" style="color: #3498db; font-size: 0.8rem;"></i>
                <span style="font-weight: 500;">{{ number_format($business->visitors) }}</span>
            </div>
          </td>
          <td>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('business.edit', $business->id) }}" class="edit-btn">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('business.delete', $business->id) }}" method="POST" onsubmit="return confirmDelete()">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="delete-btn">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="pagination-container">
      {{ $businesses->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
  </div>
</div>

<style>
    /* No additional styles needed, using global utilities */
</style>

<script>
  function confirmDelete() {
    return confirm("Are you sure you want to delete this business? This action cannot be undone.");
  }
</script>

@endsection