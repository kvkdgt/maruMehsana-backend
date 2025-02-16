@extends('layouts.admin')

@section('title', 'Facts')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/facts.css'); }}">
<div class="categories-container">
  <div class="header-container">
    <div>
      <h3 class="categories-label">Facts</h3>
      <p class="categories-description">Manage all Facts about Mehsana from here.</p>
    </div>
    <button class="add-category-btn" id="openModalBtn">+ Add New Fact</button>
    <!-- <button class="add-category-btn" id="openModalBtn" onclick="window.location.href='{{ url('admin/businesses/create') }}'">+ Add New Fact</button> -->

  </div>

  <form action="{{ route('admin.facts') }}" method="GET" class="filter-form">
    <div class="filter-items">
      <!-- Search Field -->
      <input type="text" name="search" placeholder="Search business" value="{{ request()->search }}" class="search-input">

      <!-- Dropdown for Sorting by Visitors -->
     
      <button type="submit" class="filter-btn">Apply Filter</button>
      <a href="{{ route('admin.facts') }}" class="reset-btn">Reset Filter</a>
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
          <th>Fact</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Static Data Example -->
        @foreach($facts as $index => $fact)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $fact->fact }}</td>
          <td>
            <form action="{{ route('facts.delete', $fact->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirmDelete()">
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
    <h3 id="modal-title">Add New Fact</h3>
    <form action="{{ url('admin/facts/store') }}" method="POST" enctype="multipart/form-data" id="categoryForm">
      @csrf
      <input type="hidden" id="category-id" name="id">

      <label for="category-name">Fact</label>
      <input type="text" id="category-name" name="fact" required>

      <button type="submit" class="submit-btn">Add Fact</button>
    </form>
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