@extends('layouts.admin')

@section('title', 'App Users')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">

<div class="users-container">
  <div class="header-container">
    <div>
      <h3 class="users-label">App Users</h3>
      <p class="users-description">Manage your application users from here</p>
    </div>
  </div>

  <form action="{{ route('admin.app-users') }}" method="GET" class="filter-form">
    <div class="filter-items">
      <!-- Search Field -->
      <input type="text" name="search" placeholder="Search Users" value="{{ request()->search }}" class="search-input">

      <!-- Dropdown for Sorting -->
      <select name="sort_by" class="sort-dropdown">
        <option value="">Sort by Date</option>
        <option value="newest" {{ request()->sort_by == 'newest' ? 'selected' : '' }}>Newest First</option>
        <option value="oldest" {{ request()->sort_by == 'oldest' ? 'selected' : '' }}>Oldest First</option>
      </select>

      <!-- Reset Filter Button -->
      <button type="submit" class="filter-btn">Apply Filter</button>
      <a href="{{ route('admin.app-users') }}" class="reset-btn">Reset Filter</a>
    </div>
  </form>

  @if (session('success'))
  <div class="alert alert-success">
    <span class="alert-icon">&#10004;</span>
    <div class="alert-text">
      {{ session('success') }}
    </div>
    <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
  </div>
  @endif

  @if (session('error'))
  <div class="alert alert-danger">
    <span class="alert-icon">&#9888;</span>
    <div class="alert-text">
      {{ session('error') }}
    </div>
    <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
  </div>
  @endif

  <div class="table-container">
    <table class="users-table">
      <thead>
        <tr>
          <th>Sr. No.</th>
          <th>Name</th>
          <th>Email</th>
          <th>Joined Date</th>
          <th>FCM Token</th>
          <th>Valid Session</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($appUsers as $index => $user)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $user->name }}</td>
          <td>{{ $user->email }}</td>
          <td>{{ $user->created_at->format('d M Y') }}</td>
          <td>
            @if(!empty($user->fcm_tokens) && count($user->fcm_tokens) > 0)
              <span class="status-badge status-active">Yes</span>
            @else
              <span class="status-badge status-inactive">No</span>
            @endif
          </td>
          <td>
            @if($user->is_login)
              <span class="status-badge status-active">Yes</span>
            @else
              <span class="status-badge status-inactive">No</span>
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

<style>
  /* Reuse styles from categories but adapted names */
  .users-container {
    background-color: #f5f7fb;
    border-radius: 10px;
    padding: 20px;
    padding-top: 0;
  }

  .header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .users-label {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
  }

  .users-description {
    font-size: 1rem;
    color: #777;
    margin-top: 5px;
  }

  .filter-form {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
  }

  .filter-items {
    display: flex;
    gap: 15px;
  }

  .search-input,
  .sort-dropdown {
    padding: 8px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 5px;
  }

  .filter-btn,
  .reset-btn {
    background-color: #2596be;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
  }

  .filter-btn:hover,
  .reset-btn:hover {
    background-color: #1abc9c;
  }

  .reset-btn {
    margin-left: 10px;
  }

  /* Table styles */
  .table-container {
    overflow-x: auto;
    margin-top: 20px;
  }

  .users-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  }

  .users-table th,
  .users-table td {
    padding: 15px 20px;
    text-align: left;
    border-bottom: 1px solid #e1e8ee;
    color: #333;
    font-size: 1rem;
  }

  .users-table th {
    background-color: #2596be;
    color: white;
    font-weight: 600;
    text-transform: uppercase;
  }

  .users-table td {
    background-color: #fafafa;
  }

  .users-table tr:nth-child(even) {
    background-color: #f9f9f9;
  }

  .users-table tr:hover {
    background-color: #f1f1f1;
  }

  .delete-btn {
    background-color: #e74c3c;
    border: none;
    cursor: pointer;
    padding: 6px 15px;
    border-radius: 30px;
    color: #fff;
    font-size: 0.9rem;
    transition: background-color 0.3s;
  }

  .delete-btn:hover {
    background-color: #c0392b;
  }
  
  .status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 500;
  }
  
  .status-active {
    background-color: #d1fae5;
    color: #065f46;
  }
  
  .status-inactive {
    background-color: #fee2e2;
    color: #991b1b;
  }

  /* Success Alert Styles */
  .alert {
      padding: 6px 10px;
      margin: 20px 0;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      display: flex;
      align-items: center;
      position: relative;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      animation: fadeIn 0.5s ease-out;
  }

  .alert-success {
      background: linear-gradient(135deg, #2c3e50, #34495e);
      color: #ffffff;
      border-left: 5px solid #2c3e50;
  }

  .alert-danger {
      background: linear-gradient(135deg, #2c3e50, #7f8c8d);
      color: #ffffff;
      border-left: 5px solid #2c3e50;
  }

  .alert-icon {
      font-size: 20px;
      margin-right: 10px;
  }
  
  .alert .close-btn {
      position: absolute;
      top: 5px;
      right: 20px;
      background: transparent;
      border: none;
      color: #ffffff;
      font-size: 24px;
      cursor: pointer;
      transition: transform 0.3s ease;
  }

  .alert .close-btn:hover {
      transform: rotate(90deg);
  }

  @keyframes fadeIn {
      0% {
          opacity: 0;
          transform: translateY(-20px);
      }
      100% {
          opacity: 1;
          transform: translateY(0);
      }
  }
</style>

<script>
  function confirmDelete() {
    return confirm("Are you sure you want to delete this user? This action cannot be undone.");
  }
</script>
@endsection
