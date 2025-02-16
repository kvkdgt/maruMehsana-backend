@extends('layouts.admin')

@section('title', 'Businesses')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/businesses.css'); }}">
<div class="categories-container">
  <div class="header-container">
    <div>
      <h3 class="categories-label">Businesses</h3>
      <p class="categories-description">Manage all businesses from here</p>
    </div>

    <button class="add-category-btn" id="openModalBtn" onclick="window.location.href='{{ url('admin/businesses/create') }}'">+ Add New Business</button>

  </div>

  <form action="{{ route('admin.businesses') }}" method="GET" class="filter-form">
    <div class="filter-items">
      <!-- Search Field -->
      <input type="text" name="search" placeholder="Search business" value="{{ request()->search }}" class="search-input">

      <!-- Dropdown for Sorting by Visitors -->
      <select name="sort_by" class="sort-dropdown">
        <option value="">Sort by Visitors</option>
        <option value="highest" {{ request()->sort_by == 'highest' ? 'selected' : '' }}>Highest Visitors</option>
        <option value="lowest" {{ request()->sort_by == 'lowest' ? 'selected' : '' }}>Lowest Visitors</option>
      </select>

      <!-- Reset Filter Button -->
      <button type="submit" class="filter-btn">Apply Filter</button>
      <a href="{{ route('admin.businesses') }}" class="reset-btn">Reset Filter</a>
    </div>
  </form>
  @if (session('success'))
  <div class="alert alert-success">
    <span class="alert-icon">&#10004;</span> <!-- Green check mark icon -->
    <div class="alert-text">
      {{ session('success') }}
    </div>
    <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
  </div>
  @endif

  @if (session('error'))
  <div class="alert alert-danger">
    <span class="alert-icon">&#9888;</span> <!-- Red warning icon -->
    <div class="alert-text">
      {{ session('error') }}
    </div>
    <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
  </div>
  @endif

  <div class="table-container">
    <table class="categories-table">
      <thead>
        <tr>
          <th>Sr. No.</th>
          <th>Business Name</th>
          <th>Description</th>
          <th>Business Visitor</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Static Data Example -->
        @foreach($businesses as $index => $business)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $business->name }} <span class="category-name">{{ $business->category->name }}</span></td>
          <td>{{ $business->description }}</td>
          <td>{{ $business->visitors }}</td> <!-- Placeholder value -->
          <td>

          <form action="{{ route('business.edit', $business->id) }}" method="POST" style="display:inline-block;" >
              @csrf
              @method('GET')
              <button type="submit" class="edit-btn">Edit</button>
            </form>
            <form action="{{ route('business.delete', $business->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirmDelete()">
              @csrf
              @method('DELETE')
              <button type="submit" class="delete-btn">Delete</button>
            </form>
          </td>
        </tr>
        @endforeach

      </tbody>
    </table>
  </div>
</div>

<script>
  function confirmDelete() {
    return confirm("Are you sure you want to delete this business?, it can't be restore.");
  }
</script>

@endsection