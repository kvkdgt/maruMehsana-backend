@extends('layouts.admin')

@section('title', 'App Users')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">

<div class="users-container">
  <div class="header-container">
    <div>
      <h3 class="categories-label" style="font-size: 1.5rem; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">App Users</h3>
      <p class="categories-description" style="color: #777; font-size: 0.95rem;">Manage and monitor your application users</p>
    </div>
  </div>

  <div class="filter-section">
    <form action="{{ route('admin.app-users') }}" method="GET">
      <div class="filter-grid">
        <div class="filter-control">
          <label>Search</label>
          <input type="text" name="search" placeholder="Name or Email" value="{{ request()->search }}" class="filter-input">
        </div>

        <div class="filter-control">
          <label>Login Status</label>
          <select name="is_login" class="filter-input">
            <option value="">All Users</option>
            <option value="1" {{ request()->is_login == '1' ? 'selected' : '' }}>Logged In</option>
            <option value="0" {{ request()->is_login == '0' ? 'selected' : '' }}>Logged Out</option>
          </select>
        </div>

        <div class="filter-control">
          <label>FCM Token</label>
          <select name="has_fcm" class="filter-input">
            <option value="">All</option>
            <option value="yes" {{ request()->has_fcm == 'yes' ? 'selected' : '' }}>With Token</option>
            <option value="no" {{ request()->has_fcm == 'no' ? 'selected' : '' }}>No Token</option>
          </select>
        </div>

        <div class="filter-control">
          <label>Sort By</label>
          <select name="sort_by" class="filter-input">
            <option value="newest" {{ request()->sort_by == 'newest' ? 'selected' : '' }}>Newest First</option>
            <option value="oldest" {{ request()->sort_by == 'oldest' ? 'selected' : '' }}>Oldest First</option>
          </select>
        </div>

        <div class="btn-group">
          <button type="submit" class="filter-btn">Apply</button>
          <a href="{{ route('admin.app-users') }}" class="reset-btn">Reset</a>
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
          <th>User Info</th>
          <th>Joined Date</th>
          <th>FCM Token</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($appUsers as $index => $user)
        <tr>
          <td>{{ $appUsers->firstItem() + $index }}</td>
          <td>
            <div style="display: flex; flex-direction: column;">
                <span style="font-weight: 600; color: #2c3e50;">{{ $user->name }}</span>
                <span style="font-size: 0.8rem; color: #777;">{{ $user->email }}</span>
            </div>
          </td>
          <td>{{ $user->created_at->format('d M Y') }}</td>
          <td>
            @if(!empty($user->fcm_tokens) && count($user->fcm_tokens) > 0)
              <span class="badge-premium badge-success"><i class="fas fa-check-circle"></i> Yes</span>
            @else
              <span class="badge-premium badge-danger"><i class="fas fa-times-circle"></i> No</span>
            @endif
          </td>
          <td>
            @if($user->is_login)
              <span class="badge-premium badge-info">Logged In</span>
            @else
              <span class="badge-premium badge-warning">Logged Out</span>
            @endif
          </td>
          <td>
            <form action="{{ route('admin.app-users.delete', $user->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirmDelete()">
              @csrf
              @method('DELETE')
              <button type="submit" class="delete-btn">Delete</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="pagination-container">
      {{ $appUsers->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
  </div>
</div>

<script>
  function confirmDelete() {
    return confirm("Are you sure you want to delete this user? This action cannot be undone.");
  }
</script>
@endsection
