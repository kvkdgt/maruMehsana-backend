@extends('layouts.admin')

@section('title', 'Tourist Places')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/tourist_places.css'); }}">

<div class="categories-container">
  <div class="header-container">
    <div>
      <h3 class="categories-label">Tourist Places</h3>
      <p class="categories-description">Manage all tourist places from here</p>
    </div>
    <button class="add-category-btn" onclick="openModal()">+ Add New Place</button>
  </div>

  <form action="{{ route('admin.tourist-places') }}" method="GET" class="filter-form">
    <div class="filter-items">
      <input type="text" name="search" placeholder="Search tourist place" value="{{ request()->search }}" class="search-input">
      <select name="sort_by" class="sort-dropdown">
        <option value="">Sort by Visitors</option>
        <option value="highest" {{ request()->sort_by == 'highest' ? 'selected' : '' }}>Highest Visitors</option>
        <option value="lowest" {{ request()->sort_by == 'lowest' ? 'selected' : '' }}>Lowest Visitors</option>
      </select>
      <button type="submit" class="filter-btn">Apply Filter</button>
      <a href="{{ route('admin.tourist-places') }}" class="reset-btn">Reset Filter</a>
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

  <div class="table-container">
    <table class="categories-table">
      <thead>
        <tr>
          <th>Sr. No.</th>
          <th>Place Name</th>
          <th>Description</th>
          <th>Visitors</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($tourist_places as $index => $place)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $place->name }}  </td>
          <td>{{ $place->description }}</td>
          <td>{{ $place->visitors }}</td>
          <td>
            <!-- <button class="edit-btn">Edit</button> -->
            <button class="delete-btn" data-id="{{ $place->id }}">Delete</button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Add Tourist Place Modal -->
<div id="addPlaceModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3 class="modal-title">Add New Tourist Place</h3>
    <form id="addPlaceForm" enctype="multipart/form-data">
      @csrf
      <div class="form-group">
        <label>Place Name:</label>
        <input type="text" name="name" required>
      </div>
      <div class="form-group">
        <label>Description:</label>
        <textarea name="description" required></textarea>
      </div>
      <div class="form-group">
        <label>Location Link:</label>
        <input type="text" name="location">
      </div>
      <div class="form-group">
  <label>Thumbnail:</label>
  <input type="file" name="thumbnail" accept="image/*" required>
  <img id="thumbnailPreview" class="preview-img" style="display:none; width:100px; margin-top:10px;">
</div>

<div class="form-group">
  <label>Additional Images:</label>
  <input type="file" name="images[]" accept="image/*" multiple>
  <div id="additionalImagesPreview" class="preview-container"></div>
</div>
      <button type="submit" class="submit-btn">Submit</button>
    </form>
  </div>
</div>

<!-- JavaScript for Modal & AJAX -->
<script>
  function openModal() {
    document.getElementById("addPlaceModal").style.display = "flex";
  }
  function closeModal() {
    document.getElementById("addPlaceModal").style.display = "none";
  }
  document.getElementById("addPlaceForm").addEventListener("submit", function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    fetch("{{ route('tourist_place.store') }}", {
      method: "POST",
      body: formData,
      headers: { "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value }
    })
    .then(response => response.json())
    .then(data => {
      alert(data.message);
      closeModal();
      location.reload();
    })
    .catch(error => alert("Error: " + error));
  });

  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", function () {
            let placeId = this.getAttribute("data-id");
            if (confirm("Are you sure you want to delete this place? It can't be restored.")) {
                fetch(`/admin/tourist-places/delete/${placeId}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                        "Content-Type": "application/json"
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    location.reload(); // Refresh the page
                })
                .catch(error => alert("Error: " + error));
            }
        });
    });
});

  
</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Thumbnail Preview
    document.querySelector('input[name="thumbnail"]').addEventListener("change", function (event) {
      previewImage(event, "thumbnailPreview");
    });

    // Additional Images Preview
    document.querySelector('input[name="images[]"]').addEventListener("change", function (event) {
      previewMultipleImages(event, "additionalImagesPreview");
    });
  });

  function previewImage(event, previewId) {
    const previewContainer = document.getElementById(previewId);
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        previewContainer.src = e.target.result;
        previewContainer.style.display = "block";
      };
      reader.readAsDataURL(file);
    }
  }

  function previewMultipleImages(event, previewId) {
    const previewContainer = document.getElementById(previewId);
    previewContainer.innerHTML = ""; // Clear previous images
    const files = event.target.files;
    Array.from(files).forEach(file => {
      const reader = new FileReader();
      reader.onload = function (e) {
        const img = document.createElement("img");
        img.src = e.target.result;
        img.className = "preview-img";
        previewContainer.appendChild(img);
      };
      reader.readAsDataURL(file);
    });
  }
</script>


<style>
  .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); justify-content: center; align-items: center; }
  .modal-content { background: #fff; padding: 20px; border-radius: 8px; width: 450px; position: relative; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2); }
  .modal-title { margin-bottom: 15px; text-align: center; font-size: 20px; }
  .close { position: absolute; top: 10px; right: 15px; font-size: 24px; cursor: pointer; }
  .form-group { margin-bottom: 15px; }
  .form-group label { font-weight: bold; display: block; margin-bottom: 5px; }
  .form-group input, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; }
  .submit-btn { background: #2596be; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; width: 100%; }
  .preview-img {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 5px;
  margin: 5px;
  border: 1px solid #ddd;
}
.preview-container {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}
</style>

@endsection
