@extends('layouts.admin')

@section('title', 'Tourist Places')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/tourist_places.css'); }}">
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">

<div class="tourist-container">
  <div class="header-container" style="margin-bottom: 25px;">
    <div>
      <h3 class="categories-label" style="font-size: 1.5rem; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">Tourist Places</h3>
      <p class="categories-description" style="color: #777; font-size: 0.95rem;">Explore and manage beautiful locations in Mehsana</p>
    </div>
    <button class="btn-add-premium" onclick="openModal()">
      <i class="fas fa-plus-circle"></i> Add New Place
    </button>
  </div>

  <div class="filter-section">
    <form action="{{ route('admin.tourist-places') }}" method="GET">
      <div class="filter-grid">
        <div class="filter-control">
          <label>Search</label>
          <input type="text" name="search" placeholder="Place name or location" value="{{ request()->search }}" class="filter-input">
        </div>

        <div class="filter-control">
          <label>Sort By</label>
          <select name="sort_by" class="filter-input">
            <option value="">Default Sorting</option>
            <option value="highest" {{ request()->sort_by == 'highest' ? 'selected' : '' }}>Most Visitors</option>
            <option value="lowest" {{ request()->sort_by == 'lowest' ? 'selected' : '' }}>Least Visitors</option>
          </select>
        </div>

        <div class="btn-group">
          <button type="submit" class="filter-btn">Apply</button>
          <a href="{{ route('admin.tourist-places') }}" class="reset-btn">Reset</a>
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
          <th>Place Information</th>
          <th>Location & Mapping</th>
          <th>Visitors</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($tourist_places as $index => $place)
        <tr>
          <td>{{ $tourist_places->firstItem() + $index }}</td>
          <td>
            <div style="display: flex; align-items: center; gap: 15px;">
                @if($place->thumbnail)
                    <img src="{{ asset('storage/' . $place->thumbnail) }}" style="width: 55px; height: 55px; border-radius: 10px; object-fit: cover; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                @else
                    <div style="width: 55px; height: 55px; border-radius: 10px; background: #f0f2f5; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-map-marker-alt" style="color: #ccc;"></i>
                    </div>
                @endif
                <div style="display: flex; flex-direction: column;">
                    <span style="font-weight: 600; color: #2c3e50; font-size: 1rem;">{{ $place->name }}</span>
                    <span style="font-size: 0.8rem; color: #777;">{{ Str::limit($place->description, 60) }}</span>
                </div>
            </div>
          </td>
          <td>
            <div style="display: flex; flex-direction: column; gap: 4px; max-width: 250px;">
                @if($place->location)
                    @if(str_starts_with($place->location, 'http'))
                        <a href="{{ $place->location }}" target="_blank" style="font-size: 0.85rem; color: #3498db; text-decoration: none; display: flex; align-items: center; gap: 5px; font-weight: 500;" title="{{ $place->location }}">
                            <i class="fas fa-map-marker-alt" style="color: #e74c3c;"></i>
                            <span style="text-decoration: underline;">View on Google Maps</span>
                        </a>
                    @else
                        <span style="font-size: 0.85rem; font-weight: 500; display: flex; align-items: flex-start; gap: 5px;">
                            <i class="fas fa-map-pin" style="color: #e74c3c; margin-top: 3px;"></i>
                            <span>{{ $place->location }}</span>
                        </span>
                    @endif
                @else
                    <span style="font-size: 0.85rem; color: #999; font-style: italic;">No location added</span>
                @endif

                @if($place->hasCoordinates())
                    <span style="font-size: 0.75rem; color: #3498db; background: rgba(52, 152, 219, 0.1); padding: 2px 10px; border-radius: 20px; width: fit-content; margin-top: 2px; display: flex; align-items: center; gap: 4px;">
                        <i class="fas fa-compass"></i> {{ $place->coordinates }}
                    </span>
                @endif
            </div>
          </td>
          <td>
            <div style="display: flex; align-items: center; gap: 6px;">
                <i class="fas fa-eye" style="color: #9b59b6; font-size: 0.8rem;"></i>
                <span style="font-weight: 600;">{{ number_format($place->visitors) }}</span>
            </div>
          </td>
          <td>
            <div style="display: flex; gap: 10px;">
                <button class="delete-btn" data-id="{{ $place->id }}">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="pagination-container">
      {{ $tourist_places->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
  </div>
</div>

<style>
    /* No additional styles needed for container, using global utilities */
</style>

<!-- Add Tourist Place Modal -->
<div id="addPlaceModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
        <h3 id="modal-title">Add New Tourist Place</h3>
        <button type="button" class="close-btn" onclick="closeModal()">&times;</button>
    </div>
    <div class="modal-body">
        <form id="addPlaceForm" enctype="multipart/form-data">
          @csrf
          <div class="filter-control" style="margin-bottom: 15px;">
            <label>Place Name:</label>
            <input type="text" name="name" required class="filter-input" style="width: 100%;">
          </div>
          <div class="filter-control" style="margin-bottom: 15px;">
            <label>Description:</label>
            <textarea name="description" required class="filter-input" style="width: 100%;" rows="3"></textarea>
          </div>
          <div class="filter-control" style="margin-bottom: 15px;">
            <label>Location (Optional):</label>
            <input type="text" name="location" placeholder="Address or Google Maps link" class="filter-input" style="width: 100%;">
            <small style="color: #777; font-size: 0.75rem; margin-top: 4px; display: block;">You can paste a full Google Maps URL here.</small>
          </div>
          <div class="coordinates-section" style="padding: 15px; border: 1px solid #eee; border-radius: 10px; margin-bottom: 15px; background: #fafafa;">
            <h4 style="margin: 0 0 10px 0; font-size: 0.9rem; color: #333;">Coordinates (Optional)</h4>
            <div class="coordinates-inputs" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
              <div class="filter-control">
                <label style="font-size: 0.75rem;">Latitude:</label>
                <input type="number" name="latitude" step="any" min="-90" max="90" placeholder="e.g., 40.7580" class="filter-input">
              </div>
              <div class="filter-control">
                <label style="font-size: 0.75rem;">Longitude:</label>
                <input type="number" name="longitude" step="any" min="-180" max="180" placeholder="e.g., -73.9855" class="filter-input">
              </div>
            </div>
            <button type="button" class="edit-btn" onclick="getCurrentLocation()" style="width: 100%; justify-content: center; background-color: #e3f2fd; color: #1976d2; border-color: #bbdefb;">
                <i class="fas fa-location-arrow"></i> Get My Location
            </button>
          </div>
          <div class="filter-control" style="margin-bottom: 15px;">
            <label>Thumbnail Image:</label>
            <input type="file" name="thumbnail" accept="image/*" required class="filter-input" style="width: 100%;">
            <img id="thumbnailPreview" class="preview-img" style="display:none; width:100px; height: 100px; object-fit: cover; margin-top:10px; border-radius: 8px;">
          </div>
          <div class="filter-control" style="margin-bottom: 15px;">
            <label>Additional Images (Multiple):</label>
            <input type="file" name="images[]" accept="image/*" multiple class="filter-input" style="width: 100%;">
            <div id="additionalImagesPreview" class="preview-container" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;"></div>
          </div>
          <button type="submit" class="submit-btn" style="margin-top: 10px;">Submit Place</button>
        </form>
    </div>
  </div>
</div>

<!-- JavaScript for Modal & AJAX -->
<script>
  function openModal() {
    document.getElementById("addPlaceModal").style.display = "flex";
  }
  
  function closeModal() {
    document.getElementById("addPlaceModal").style.display = "none";
    document.getElementById("addPlaceForm").reset();
    document.getElementById("thumbnailPreview").style.display = "none";
    document.getElementById("additionalImagesPreview").innerHTML = "";
  }

  // Get user's current location
  function getCurrentLocation() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        function(position) {
          document.querySelector('input[name="latitude"]').value = position.coords.latitude.toFixed(6);
          document.querySelector('input[name="longitude"]').value = position.coords.longitude.toFixed(6);
          alert('Location updated successfully!');
        },
        function(error) {
          alert('Error getting location: ' + error.message);
        }
      );
    } else {
      alert('Geolocation is not supported by this browser.');
    }
  }

  document.getElementById("addPlaceForm").addEventListener("submit", function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    
    // Validate coordinates if provided
    const latitude = formData.get('latitude');
    const longitude = formData.get('longitude');
    
    if ((latitude && !longitude) || (!latitude && longitude)) {
      alert('Please provide both latitude and longitude, or leave both empty.');
      return;
    }
    
    fetch("{{ route('tourist_place.store') }}", {
      method: "POST",
      body: formData,
      headers: { "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value }
    })
    .then(response => response.json())
    .then(data => {
      if (data.message) {
        alert(data.message);
        closeModal();
        location.reload();
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert("Error: " + error);
    });
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
                    location.reload();
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
    previewContainer.innerHTML = "";
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
    /* Specific overrides for Tourist Places */
</style>

@endsection