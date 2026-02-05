@extends('layouts.admin')

@section('title', 'Facts')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/facts.css'); }}">
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">

<div class="facts-container">
  <div class="header-container">
    <div>
      <h3 class="categories-label" style="font-size: 1.5rem; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">Mehsana Facts</h3>
      <p class="categories-description" style="color: #777; font-size: 0.95rem;">Manage interesting and historical facts about Mehsana</p>
    </div>
    <button class="btn-add-premium" id="openModalBtn">
      <i class="fas fa-plus-circle"></i> Add New Fact
    </button>
  </div>

  <div class="filter-section">
    <form action="{{ route('admin.facts') }}" method="GET">
      <div class="filter-grid">
        <div class="filter-control">
          <label>Search Facts</label>
          <input type="text" name="search" placeholder="Type keywords..." value="{{ request()->search }}" class="filter-input">
        </div>

        <div class="btn-group">
          <button type="submit" class="filter-btn">Apply</button>
          <a href="{{ route('admin.facts') }}" class="reset-btn">Reset</a>
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
          <th style="width: 100px;">Sr. No.</th>
          <th>Interesting Fact</th>
          <th style="width: 150px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($facts as $index => $fact)
        <tr>
          <td>{{ $facts->firstItem() + $index }}</td>
          <td style="line-height: 1.6;">{{ $fact->fact }}</td>
          <td>
            <form action="{{ route('facts.delete', $fact->id) }}" method="POST" onsubmit="return confirmDelete()">
              @csrf
              @method('DELETE')
              <button type="submit" class="delete-btn">
                <i class="fas fa-trash-alt"></i> Delete
              </button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="pagination-container">
      {{ $facts->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
  </div>
</div>

<style>
    /* Using global utilities */
</style>
<div id="categoryModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
        <h3 id="modal-title">Add New Fact</h3>
        <button type="button" class="close-btn" id="closeModalBtn">&times;</button>
    </div>
    <div class="modal-body">
        <form action="{{ url('admin/facts/store') }}" method="POST" enctype="multipart/form-data" id="categoryForm">
          @csrf
          <input type="hidden" id="category-id" name="id">

          <label for="category-name" class="filter-control label">Fact Detail</label>
          <textarea id="category-name" name="fact" required class="filter-input" rows="4" style="width: 100%; margin-bottom: 15px;"></textarea>

          <button type="submit" class="submit-btn" style="margin-top: 10px;">Add Fact</button>
        </form>
    </div>
  </div>
</div>
<script>
  function confirmDelete() {
    return confirm("Are you sure you want to delete this fact?, it can't be restore.");
  }

  var modal = document.getElementById("categoryModal");
  var btn = document.getElementById("openModalBtn");
  var span = document.getElementById("closeModalBtn");

  // Open the modal for adding a new category
  btn.onclick = function() {
    modal.style.display = "flex";
    document.getElementById("modal-title").innerText = "Add New Fact"; // Reset title
    document.getElementById('categoryForm').action = "{{ url('admin/facts/store') }}"; // Reset form action for add
    document.getElementById("category-id").value = ''; // Reset category ID
  }

  // Close the modal when clicking on the close button
  span.onclick = function() {
    modal.style.display = "none";
  }

  // Close the modal if user clicks outside of it
  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
</script>

@endsection