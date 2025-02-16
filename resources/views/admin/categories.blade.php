@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="categories-container">
  <div class="header-container">
    <div>
      <h3 class="categories-label">Categories</h3>
      <p class="categories-description">Manage your categories from here</p>
    </div>

    <button class="add-category-btn" id="openModalBtn">+ Add New Category</button>
  </div>

  <form action="{{ route('admin.categories') }}" method="GET" class="filter-form">
    <div class="filter-items">
      <!-- Search Field -->
      <input type="text" name="search" placeholder="Search Categories" value="{{ request()->search }}" class="search-input">

      <!-- Dropdown for Sorting by Visitors -->
      <select name="sort_by" class="sort-dropdown">
        <option value="">Sort by Visitors</option>
        <option value="highest" {{ request()->sort_by == 'highest' ? 'selected' : '' }}>Highest Visitors</option>
        <option value="lowest" {{ request()->sort_by == 'lowest' ? 'selected' : '' }}>Lowest Visitors</option>
      </select>

      <!-- Reset Filter Button -->
      <button type="submit" class="filter-btn">Apply Filter</button>
      <a href="{{ route('admin.categories') }}" class="reset-btn">Reset Filter</a>
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
          <th>Category Name</th>
          <th>Description</th>
          <th>Categories Visitor</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Static Data Example -->
        @foreach($categories as $index => $category)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $category->name }}</td>
          <td>{{ $category->description }}</td>
          <td>{{ $category->category_visitors }}</td> <!-- Placeholder value -->
          <td>
            <button class="edit-btn" onclick="openEditModal({{ $category->id }}, '{{ $category->name }}', '{{ $category->description }}', '{{ asset('storage/' . $category->image) }}')">Edit</button>
            <form action="{{ route('categories.delete', $category->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirmDelete()">
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

<!-- Modal for Add/Edit Category -->
<div id="categoryModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" id="closeModalBtn">&times;</span>
    <h3 id="modal-title">Create New Category</h3>
    <form action="{{ url('admin/categories/store') }}" method="POST" enctype="multipart/form-data" id="categoryForm">
      @csrf
      <input type="hidden" id="category-id" name="id">

      <label for="category-name">Category Name</label>
      <input type="text" id="category-name" name="name" required>

      <label for="category-description">Category Description</label>
      <textarea id="category-description" name="description" rows="4" required></textarea>

      <label for="category-image" class="image-label">Add Category Image</label>
      <input type="file" id="category-image" name="image" accept="image/*" hidden>
      <div id="image-preview-container" style="display: none; margin-top: 15px;">
        <img id="image-preview" src="" alt="Image Preview" style="max-width: 200px; max-height: 200px; border-radius: 10px; object-fit: cover;">
      </div>

      <div id="existing-image-container" style="display: none; margin-top: 15px;">
        <label>Current Image:</label>
        <img id="existing-image" src="" alt="Current Category Image" style="max-width: 200px; max-height: 200px; border-radius: 10px; object-fit: cover;">
      </div>

      <button type="submit" class="submit-btn">Save Category</button>
    </form>
  </div>
</div>




<style>

/* General Styles for Alerts */
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

/* Success Alert Styles */
/* Success Alert Styles */
.alert-success {
    background: linear-gradient(135deg, #2c3e50, #34495e); /* Dark blue-gray to a lighter blue-gray */
    color: #ffffff;
    border-left: 5px solid #2c3e50;
}

/* Error Alert Styles */
.alert-danger {
    background: linear-gradient(135deg, #2c3e50, #7f8c8d); /* Dark blue-gray to a lighter gray */
    color: #ffffff;
    border-left: 5px solid #2c3e50;
}


.alert-success .alert-icon {
    font-size: 20px;
    margin-right: 10px;
}

.alert-success .alert-text {
    flex: 1;
}

/* Error Alert Styles */

.alert-danger .alert-icon {
    font-size: 20px;
    margin-right: 10px;
}

.alert-danger .alert-text {
    flex: 1;
}

/* Close Button Styles */
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

/* Animation for Alerts */
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

/* Responsive Styles */
@media (max-width: 576px) {
    .alert {
        font-size: 16px;
        padding: 15px;
    }

    .alert-icon {
        font-size: 24px;
    }

    .close-btn {
        font-size: 20px;
    }
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
  }

  .filter-btn:hover,
  .reset-btn:hover {
    background-color: #1abc9c;
  }

  .reset-btn {
    text-decoration: none;
    display: inline-block;
    margin-left: 10px;
  }

  /* Table styles */
  .table-container {
    margin-top: 20px;
  }

  .categories-table {
    width: 100%;
    border-collapse: collapse;
  }

  .categories-table th,
  .categories-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
  }

  .categories-table th {
    background-color: #f4f4f4;
  }

  .add-category-btn {
    background-color: #2596be;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
  }

  .add-category-btn:hover {
    background-color: #1abc9c;
  }

  .categories-container {
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

  .categories-label {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
  }

  .categories-description {
    font-size: 1rem;
    color: #777;
    margin-top: 5px;
  }

  .add-category-btn {
    border: 0;
    background: linear-gradient(135deg, #2596be, #16a085);
    padding: 12px 30px;
    font-size: 1.1rem;
    border-radius: 30px;
    /* Rounded corners */
    text-decoration: none;
    color: white;
    text-align: center;
    transition: all 0.3s ease;
    display: inline-block;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  }

  .add-category-btn:hover {
    background: linear-gradient(135deg, #1abc9c, #1e7e70);
    transform: scale(1.05);
    /* Smooth scale effect */
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
  }

  .table-container {
    overflow-x: auto;
  }

  .categories-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  }

  .categories-table th,
  .categories-table td {
    padding: 15px 20px;
    text-align: left;
    border-bottom: 1px solid #e1e8ee;
    color: #333;
    font-size: 1rem;
  }

  .categories-table th {
    background-color: #2596be;
    color: white;
    font-weight: 600;
    text-transform: uppercase;
  }

  .categories-table td {
    background-color: #fafafa;
  }

  .categories-table tr:nth-child(even) {
    background-color: #f9f9f9;
  }

  .categories-table tr:hover {
    background-color: #f1f1f1;
  }

  .edit-btn,
  .delete-btn {
    border: 0;
    padding: 6px 15px;
    margin-right: 10px;
    border-radius: 30px;
    color: #fff;
    text-decoration: none;
    transition: background-color 0.3s;
    font-size: 0.9rem;
    display: inline-block;
  }

  .edit-btn {
    background-color: #f39c12;
  }

  .edit-btn:hover {
    background-color: #e67e22;
  }

  .delete-btn {
    background-color: #e74c3c;
    border: none;
    cursor: pointer;
  }

  .delete-btn:hover {
    background-color: #c0392b;
  }

  /* Modal Styles */
  .modal {
    display: none;
    /* Hidden by default */
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
    justify-content: center;
    align-items: center;
  }

  .modal-content {
    background-color: #fff;
    margin: auto;
    padding: 20px;
    border-radius: 10px;
    width: 80%;
    max-width: 500px;
    position: relative;
    top: 50px;
    /* Offset to prevent overlap with header */
  }

  .close-btn {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    position: absolute;
    top: 10px;
    right: 15px;
  }

  .close-btn:hover,
  .close-btn:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
  }

  label {
    display: block;
    margin-top: 10px;
    font-weight: 400;
  }

  input[type="text"],
  textarea,
  input[type="file"] {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
  }

  /* Image input styling */
  .image-label {
    display: block;
    padding: 30px;
    border: 2px dashed #2596be;
    border-radius: 10px;
    text-align: center;
    font-size: 1rem;
    color: #2596be;
    margin-top: 10px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  .image-label:hover {
    background-color: #f1f1f1;
  }

  .submit-btn {
    background-color: #2596be;
    color: white;
    padding: 10px 20px;
    border-radius: 30px;
    border: none;
    cursor: pointer;
    margin-top: 20px;
    width: 100%;
  }

  .submit-btn:hover {
    background-color: #1abc9c;
  }

  .modal-content h3 {
    font-weight: 600;
  }

  #image-preview-container {
    text-align: center;
    margin-top: 10px;
  }

  #image-preview {
    border: 1px solid #ccc;
    padding: 5px;
    border-radius: 10px;
  }

  .image-label {
    display: block;
    padding: 30px;
    border: 2px dashed #2596be;
    border-radius: 10px;
    text-align: center;
    font-size: 1rem;
    color: #2596be;
    margin-top: 10px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  .image-label:hover {
    background-color: #f1f1f1;
  }


  /* Center modal */
  @media screen and (max-width: 768px) {
    .modal-content {
      width: 90%;
      max-width: 100%;
    }
  }
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