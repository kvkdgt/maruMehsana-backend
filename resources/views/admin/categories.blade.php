@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">

<div class="categories-container">
  <div class="header-container">
    <div>
      <h3 class="categories-label" style="font-size: 1.5rem; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">Categories</h3>
      <p class="categories-description" style="color: #777; font-size: 0.95rem;">Organize and manage business categories</p>
    </div>

    <button class="btn-add-premium" id="openModalBtn">
      <i class="fas fa-plus-circle"></i> Add New Category
    </button>
  </div>

  <div class="filter-section">
    <form action="{{ route('admin.categories') }}" method="GET">
      <div class="filter-grid">
        <div class="filter-control">
          <label>Search</label>
          <input type="text" name="search" placeholder="Category name or description" value="{{ request()->search }}" class="filter-input">
        </div>

        <div class="filter-control">
          <label>Sort By</label>
          <select name="sort_by" class="filter-input">
            <option value="">Default Sorting</option>
            <option value="highest" {{ request()->sort_by == 'highest' ? 'selected' : '' }}>Highest Visitors</option>
            <option value="lowest" {{ request()->sort_by == 'lowest' ? 'selected' : '' }}>Lowest Visitors</option>
          </select>
        </div>

        <div class="btn-group">
          <button type="submit" class="filter-btn">Apply</button>
          <a href="{{ route('admin.categories') }}" class="reset-btn">Reset</a>
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
          <th>Category Info</th>
          <th>Visitor Count</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($categories as $index => $category)
        <tr>
          <td>{{ $categories->firstItem() + $index }}</td>
          <td>
            <div style="display: flex; align-items: center; gap: 15px;">
                @if($category->image)
                    <img src="{{ asset('storage/' . $category->image) }}" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
                @else
                    <div style="width: 50px; height: 50px; border-radius: 8px; background: #eee; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-tags" style="color: #ccc;"></i>
                    </div>
                @endif
                <div style="display: flex; flex-direction: column;">
                    <span style="font-weight: 600; color: #2c3e50;">{{ $category->name }}</span>
                    <span style="font-size: 0.8rem; color: #777;">{{ Str::limit($category->description, 60) }}</span>
                </div>
            </div>
          </td>
          <td>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="badge-premium badge-info">
                    <i class="fas fa-users" style="margin-right: 5px;"></i> {{ number_format($category->category_visitors) }}
                </span>
            </div>
          </td>
          <td>
            <div style="display: flex; gap: 10px;">
                <button class="edit-btn" onclick="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description) }}', '{{ asset('storage/' . $category->image) }}')">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <form action="{{ route('categories.delete', $category->id) }}" method="POST" onsubmit="return confirmDelete()">
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
      {{ $categories->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
  </div>
</div>



<!-- Modal for Add/Edit Category -->
<div id="categoryModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
        <h3 id="modal-title">Create New Category</h3>
        <button type="button" class="close-btn" id="closeModalBtn">&times;</button>
    </div>
    <div class="modal-body">
        <form action="{{ url('admin/categories/store') }}" method="POST" enctype="multipart/form-data" id="categoryForm">
          @csrf
          <input type="hidden" id="category-id" name="id">

          <label for="category-name">Category Name</label>
          <input type="text" id="category-name" name="name" required class="filter-input" style="width: 100%; margin-bottom: 15px;">

          <label for="category-description">Category Description</label>
          <textarea id="category-description" name="description" rows="4" required class="filter-input" style="width: 100%; margin-bottom: 15px;"></textarea>

          <label for="category-image" class="image-label">Add Category Image</label>
          <input type="file" id="category-image" name="image" accept="image/*" hidden>
          <div id="image-preview-container" style="display: none; margin-top: 15px; text-align: center;">
            <img id="image-preview" src="" alt="Image Preview" style="max-width: 200px; max-height: 200px; border-radius: 10px; object-fit: cover; border: 1px solid #eee;">
          </div>

          <div id="existing-image-container" style="display: none; margin-top: 15px; text-align: center;">
            <label style="display: block; margin-bottom: 10px;">Current Image:</label>
            <img id="existing-image" src="" alt="Current Category Image" style="max-width: 200px; max-height: 200px; border-radius: 10px; object-fit: cover; border: 1px solid #eee;">
          </div>

          <button type="submit" class="submit-btn" style="margin-top: 25px;">Save Category</button>
        </form>
    </div>
  </div>
</div>




<style>
    /* Specific overrides for Categories Modal */
    #categoryModal .modal-content { max-width: 500px; }
    .image-label { display: block; padding: 25px; border: 2px dashed var(--accent); border-radius: 10px; text-align: center; color: var(--accent); cursor: pointer; margin-top: 10px; transition: 0.3s; }
    .image-label:hover { background: rgba(242, 101, 45, 0.05); }
    .submit-btn { background: var(--accent); color: white; padding: 12px; border-radius: 8px; border: none; cursor: pointer; width: 100%; font-weight: 600; margin-top: 20px; transition: 0.3s; }
    .submit-btn:hover { background: var(--accent-hover); transform: translateY(-2px); }
</style>

<script>
  var modal = document.getElementById("categoryModal");
  var btn = document.getElementById("openModalBtn");
  var span = document.getElementById("closeModalBtn");

  // Open the modal for adding a new category
  btn.onclick = function() {
    modal.style.display = "flex";
    document.getElementById("modal-title").innerText = "Create New Category"; // Reset title
    document.getElementById('categoryForm').action = "{{ url('admin/categories/store') }}"; // Reset form action for add
    document.getElementById("category-id").value = ''; // Reset category ID
  }

  // Open the modal for editing an existing category
  // Open the modal for editing an existing category
  function openEditModal(id, name, description, imageUrl) {
    modal.style.display = "flex";
    document.getElementById("modal-title").innerText = "Edit Category"; // Set modal title for editing
    document.getElementById('categoryForm').action = "{{ url('admin/categories/update') }}"; // Set the form action for edit
    document.getElementById("category-id").value = id; // Set the category ID for edit
    document.getElementById("category-name").value = name; // Populate the name field
    document.getElementById("category-description").value = description; // Populate the description field

    if (imageUrl) {
      document.getElementById('existing-image').src = imageUrl; // Set the existing image URL
      document.getElementById('existing-image-container').style.display = 'block'; // Show the current image container
      document.getElementById('image-preview-container').style.display = 'none'; // Hide the new image preview container
    } else {
      document.getElementById('existing-image-container').style.display = 'none'; // Hide if no image exists
    }
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

  document.getElementById('category-image').addEventListener('change', function(event) {
    var reader = new FileReader();
    reader.onload = function(e) {
      var imagePreviewContainer = document.getElementById('image-preview-container');
      var imagePreview = document.getElementById('image-preview');
      var existingImageContainer = document.getElementById('existing-image-container');

      // Hide the existing image container when a new image is selected
      existingImageContainer.style.display = 'none';

      // Show the preview container and display the new image
      imagePreviewContainer.style.display = 'block';
      imagePreview.src = e.target.result; // Set the preview image source to the selected file
    };
    reader.readAsDataURL(this.files[0]);
  });

  function confirmDelete() {
    return confirm("Are you sure you want to delete this category?, businesses related to this category will also removed.");
  }
</script>
@endsection