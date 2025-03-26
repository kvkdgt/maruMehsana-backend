@extends('layouts.admin')

@section('title', 'Banner Ads')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/banner-ads.css') }}">
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">

<div class="categories-container">
  <div class="header-container">
    <div>
      <h3 class="categories-label">Banner Ads</h3>
      <p class="categories-description">Manage all Banner Ads from here.</p>
    </div>
    <button class="add-category-btn" id="openModalBtn">+ Add New Banner Ad</button>
  </div>

  <form action="{{ route('admin.banner-ads') }}" method="GET" class="filter-form">
    <div class="filter-items">
      <!-- Search Field -->
      <input type="text" name="search" placeholder="Search banner ad" value="{{ request()->search }}" class="search-input">

      <button type="submit" class="filter-btn">Apply Filter</button>
      <a href="{{ route('admin.banner-ads') }}" class="reset-btn">Reset Filter</a>
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
    <table class="categories-table">
      <thead>
        <tr>
          <th>Sr. No.</th>

          <th>Title</th>
          <th>Status</th>
          <th>Total Touch</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($bannerAds as $index => $banner)
        <tr>
          <td>{{ $index + 1 }}</td>

          <td>{{ $banner->title }}</td>
          <td>
            <label class="switch">
              <input type="checkbox" class="status-toggle" data-id="{{ $banner->id }}" {{ $banner->status ? 'checked' : '' }}>
              <span class="slider round"></span>
            </label>
          </td>
          <td>{{ $banner->touch }}</td>


          <td>
            <form action="{{ route('admin.banner-ads.destroy', $banner->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirmDelete()">
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

<div id="categoryModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" id="closeModalBtn">&times;</span>
    <h3 id="modal-title">Add New Banner Ad</h3>
    <form action="{{ route('admin.banner-ads.store') }}" method="POST" enctype="multipart/form-data" id="categoryForm">
      @csrf
      <input type="hidden" id="category-id" name="id">

      <label for="banner-title" class="required-label">Title</label>
      <input type="text" id="banner-title" name="title" required>

      <label for="banner-image" class="required-label">Image</label>
      <input type="file" id="banner-image" name="image" accept="image/*" required>
      <label for="banner-title">Redirection Link</label>
      <input type="text" id="banner-link" name="link">
      <label for="banner-status" class="required-label">Status</label>
      <select id="banner-status" name="status">
        <option value="1">Active</option>
        <option value="0">Inactive</option>
      </select>

      <button type="submit" class="submit-btn">Add Banner Ad</button>
    </form>
  </div>
</div>

<script>
  function confirmDelete() {
    return confirm("Are you sure you want to delete this banner ad? It cannot be restored.");
  }

  var modal = document.getElementById("categoryModal");
  var btn = document.getElementById("openModalBtn");
  var span = document.getElementById("closeModalBtn");

  btn.onclick = function() {
    modal.style.display = "flex";
    document.getElementById("modal-title").innerText = "Add New Banner Ad";
    document.getElementById('categoryForm').action = "{{ route('admin.banner-ads.store') }}";
    document.getElementById("category-id").value = '';
  }

  span.onclick = function() {
    modal.style.display = "none";
  }

  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }

  document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".status-toggle").forEach(toggle => {
      toggle.addEventListener("change", function() {
        let bannerId = this.dataset.id;
        let status = this.checked ? 1 : 0;

        fetch("{{ url('admin/banner-ads/updateStatus') }}/" + bannerId, {
            method: "PATCH",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
              status: status
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert("Banner status updated successfully.");
            } else {
              alert("Something went wrong. Please try again.");
            }
          });
      });
    });
  });
</script>


@endsection