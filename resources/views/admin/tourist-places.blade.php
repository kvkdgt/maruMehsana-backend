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
            <div style="display: flex; gap: 8px;">
                <button class="edit-btn-premium" onclick="editPlace({{ $place->id }})" style="background: #f39c12; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 5px; font-size: 0.85rem; transition: all 0.3s;">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="delete-btn" data-id="{{ $place->id }}" style="background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 5px; font-size: 0.85rem; transition: all 0.3s;">
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

<!-- Add/Edit Tourist Place Modal -->
<div id="addPlaceModal" class="modal">
  <div class="modal-content">
    <div class="modal-header" style="background: linear-gradient(135deg, #2c3e50, #34495e); padding: 20px 25px; color: white;">
        <h3 id="modal-title" style="margin: 0; font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-map-marked-alt"></i> <span>Add New Tourist Place</span>
        </h3>
        <button type="button" class="close-btn" onclick="closeModal()" style="color: white; font-size: 28px; background: none; border: none; cursor: pointer; opacity: 0.8; transition: 0.3s;">&times;</button>
    </div>
    <div class="modal-body" style="padding: 30px;">
        <form id="addPlaceForm" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="place_id" id="place_id">
          
          <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px;">
              <!-- Left Column -->
              <div class="form-left">
                  <div class="filter-control" style="margin-bottom: 25px;">
                    <label style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 0.95rem;">Place Name</label>
                    <input type="text" name="name" id="edit_name" required class="filter-input" style="width: 100%; padding: 14px; border: 2px solid #e1e8ee; border-radius: 8px; font-size: 0.95rem;" placeholder="Enter place name">
                  </div>
                  
                  <div class="filter-control" style="margin-bottom: 25px;">
                    <label style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 0.95rem;">Description</label>
                    <textarea name="description" id="edit_description" required class="filter-input" style="width: 100%; padding: 14px; border: 2px solid #e1e8ee; border-radius: 8px; font-size: 0.95rem; line-height: 1.6;" rows="5" placeholder="Briefly describe the place"></textarea>
                  </div>

                  <div class="filter-control" style="margin-bottom: 25px;">
                    <label style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 0.95rem;">Location / Map Link</label>
                    <input type="text" name="location" id="edit_location" placeholder="Paste Google Maps URL or address" class="filter-input" style="width: 100%; padding: 14px; border: 2px solid #e1e8ee; border-radius: 8px; font-size: 0.95rem;">
                    <small style="color: #3498db; font-size: 0.8rem; margin-top: 6px; display: block;"><i class="fas fa-info-circle"></i> Supports full Google Map URLs for easy navigation</small>
                  </div>
              </div>

              <!-- Right Column -->
              <div class="form-right">
                  <div class="coordinates-section" style="padding: 20px; border: 2px solid #f0f2f5; border-radius: 12px; margin-bottom: 20px; background: #f8f9fa;">
                    <h4 style="margin: 0 0 15px 0; font-size: 1rem; color: #2c3e50; font-weight: 700; border-bottom: 2px solid #e1e8ee; padding-bottom: 10px;">
                        <i class="fas fa-location-arrow"></i> Coordinates
                    </h4>
                    <div class="coordinates-inputs" style="display: grid; grid-template-columns: 1fr; gap: 12px; margin-bottom: 15px;">
                      <div class="filter-control">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #555; margin-bottom: 5px; display: block;">Latitude</label>
                        <input type="number" name="latitude" id="edit_latitude" step="any" min="-90" max="90" placeholder="0.000000" class="filter-input" style="padding: 12px; border-radius: 6px; border: 2px solid #e1e8ee; font-size: 0.9rem;">
                      </div>
                      <div class="filter-control">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #555; margin-bottom: 5px; display: block;">Longitude</label>
                        <input type="number" name="longitude" id="edit_longitude" step="any" min="-180" max="180" placeholder="0.000000" class="filter-input" style="padding: 12px; border-radius: 6px; border: 2px solid #e1e8ee; font-size: 0.9rem;">
                      </div>
                    </div>
                    <button type="button" class="edit-btn" onclick="getCurrentLocation()" style="width: 100%; justify-content: center; background: #fff; color: #3498db; border: 2px solid #3498db; padding: 12px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: 600; transition: all 0.3s; font-size: 0.9rem;">
                        <i class="fas fa-crosshairs"></i> Get Current Location
                    </button>
                  </div>

                  <div class="filter-control" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 0.95rem;">Thumbnail Image</label>
                    <div class="image-upload-wrapper" style="position: relative;">
                        <input type="file" name="thumbnail" accept="image/*" class="filter-input" style="width: 100%; padding: 10px; font-size: 0.9rem;" onchange="previewThumbnail(this)">
                        <div id="thumbnail_preview_container" style="margin-top: 10px; position: relative; display: none;">
                            <img id="thumbnailPreview" style="width: 100px; height: 100px; object-fit: cover; border-radius: 10px; border: 2px solid #3498db; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                            <span style="position: absolute; top: -5px; left: 90px; background: #3498db; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 12px;"><i class="fas fa-check"></i></span>
                        </div>
                    </div>
                  </div>
              </div>
          </div>

          <!-- Full Width Section for Additional Images -->
          <div class="filter-control" style="margin-top: 15px; border-top: 2px solid #f0f2f5; padding-top: 25px;">
            <label style="font-weight: 600; color: #2c3e50; margin-bottom: 15px; display: block; font-size: 0.95rem;">Gallery Images (Multiple)</label>
            <div style="background: #fdfdfd; border: 2px dashed #d1d8e0; padding: 30px; border-radius: 12px; text-align: center; position: relative; cursor: pointer;" onclick="document.getElementById('galleryInput').click();">
                <input type="file" id="galleryInput" name="images[]" accept="image/*" multiple class="filter-input" style="display: none;" onchange="previewGallery(this)">
                <div style="color: #888; pointer-events: none;">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 40px; margin-bottom: 12px; color: #3498db;"></i>
                    <p style="margin: 0; font-size: 1rem; font-weight: 500;">Click or Drag & Drop multiple images here</p>
                    <p style="margin: 5px 0 0 0; font-size: 0.85rem; color: #aaa;">Supports JPG, PNG, GIF</p>
                </div>
            </div>
            
            <div id="existingGallery" style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 20px;"></div>
            <div id="additionalImagesPreview" class="preview-container" style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 20px;"></div>
          </div>

          <div style="margin-top: 30px; display: flex; gap: 15px; border-top: 2px solid #f0f2f5; padding-top: 25px;">
              <button type="submit" id="submitBtn" class="submit-btn" style="flex: 2; padding: 16px; background: linear-gradient(135deg, #1abc9c, #16a085); color: white; border: none; border-radius: 10px; font-weight: 700; font-size: 1.1rem; cursor: pointer; box-shadow: 0 4px 15px rgba(22, 160, 133, 0.3);">
                  Submit Place Details
              </button>
              <button type="button" onclick="closeModal()" style="flex: 1; padding: 16px; background: #f8f9fa; color: #666; border: 2px solid #e1e8ee; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                  Cancel
              </button>
          </div>
        </form>
    </div>
  </div>
</div>

<!-- JavaScript for Modal & AJAX -->
<script>
  function openModal() {
    document.getElementById("modal-title").innerHTML = '<i class="fas fa-plus-circle"></i> Add New Tourist Place';
    document.getElementById("submitBtn").innerText = "Submit Place Details";
    document.getElementById("addPlaceForm").action = "{{ route('tourist_place.store') }}";
    document.getElementById("place_id").value = "";
    document.getElementById("addPlaceModal").style.display = "flex";
    document.querySelector('input[name="thumbnail"]').required = true;
  }
  
  function closeModal() {
    document.getElementById("addPlaceModal").style.display = "none";
    document.getElementById("addPlaceForm").reset();
    document.getElementById("thumbnailPreview").src = "";
    document.getElementById("thumbnail_preview_container").style.display = "none";
    document.getElementById("additionalImagesPreview").innerHTML = "";
    document.getElementById("existingGallery").innerHTML = "";
  }

  function editPlace(id) {
    document.getElementById("modal-title").innerHTML = '<i class="fas fa-edit"></i> Edit Tourist Place';
    document.getElementById("submitBtn").innerText = "Update Place Details";
    document.getElementById("addPlaceForm").action = "{{ url('admin/tourist-place/update') }}/" + id;
    document.getElementById("place_id").value = id;
    document.querySelector('input[name="thumbnail"]').required = false;

    // Fetch place details
    fetch("{{ url('admin/tourist-places') }}/" + id + "/data")
    .then(response => response.json())
    .then(res => {
        const data = res.data;
        document.getElementById("edit_name").value = data.name;
        document.getElementById("edit_description").value = data.description;
        document.getElementById("edit_location").value = data.location || "";
        document.getElementById("edit_latitude").value = data.latitude || "";
        document.getElementById("edit_longitude").value = data.longitude || "";
        
        if (data.thumbnail) {
            document.getElementById("thumbnailPreview").src = "{{ asset('storage') }}/" + data.thumbnail;
            document.getElementById("thumbnail_preview_container").style.display = "block";
        }

        const existingGallery = document.getElementById("existingGallery");
        existingGallery.innerHTML = "";
        // Check for both camelCase and snake_case relationship names
        const images = data.place_images || data.placeImages;
        if (images && images.length > 0) {
            existingGallery.innerHTML = '<h5 style="width: 100%; margin-bottom: 10px; font-size: 0.85rem; color: #666;">Current Gallery:</h5>';
            images.forEach(img => {
                const div = document.createElement("div");
                div.style.position = "relative";
                div.innerHTML = `
                    <img src="{{ asset('storage') }}/${img.image}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                `;
                existingGallery.appendChild(div);
            });
        }

        document.getElementById("addPlaceModal").style.display = "flex";
    })
    .catch(error => alert("Error fetching data: " + error));
  }

  // Preview Thumbnail
  function previewThumbnail(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("thumbnailPreview").src = e.target.result;
            document.getElementById("thumbnail_preview_container").style.display = "block";
        };
        reader.readAsDataURL(input.files[0]);
    }
  }

  // Preview Gallery
  function previewGallery(input) {
    const previewContainer = document.getElementById("additionalImagesPreview");
    previewContainer.innerHTML = "";
    
    if (input.files && input.files.length > 0) {
        // Add a header to show how many images were selected
        const header = document.createElement("h5");
        header.style.width = "100%";
        header.style.marginBottom = "10px";
        header.style.fontSize = "0.85rem";
        header.style.color = "#2c3e50";
        header.style.fontWeight = "600";
        header.innerHTML = `<i class="fas fa-images"></i> ${input.files.length} image(s) selected:`;
        previewContainer.appendChild(header);
        
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgWrapper = document.createElement("div");
                imgWrapper.style.position = "relative";
                imgWrapper.style.display = "inline-block";
                
                const img = document.createElement("img");
                img.src = e.target.result;
                img.style.width = "80px";
                img.style.height = "80px";
                img.style.objectFit = "cover";
                img.style.borderRadius = "10px";
                img.style.border = "2px solid #3498db";
                img.style.boxShadow = "0 2px 8px rgba(0,0,0,0.1)";
                
                // Add image number badge
                const badge = document.createElement("span");
                badge.style.position = "absolute";
                badge.style.top = "-8px";
                badge.style.right = "-8px";
                badge.style.background = "#3498db";
                badge.style.color = "white";
                badge.style.borderRadius = "50%";
                badge.style.width = "24px";
                badge.style.height = "24px";
                badge.style.display = "flex";
                badge.style.alignItems = "center";
                badge.style.justifyContent = "center";
                badge.style.fontSize = "11px";
                badge.style.fontWeight = "bold";
                badge.innerText = index + 1;
                
                imgWrapper.appendChild(img);
                imgWrapper.appendChild(badge);
                previewContainer.appendChild(imgWrapper);
            };
            reader.readAsDataURL(file);
        });
    }
  }

  // Get user's current location
  function getCurrentLocation() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        function(position) {
          document.getElementById('edit_latitude').value = position.coords.latitude.toFixed(6);
          document.getElementById('edit_longitude').value = position.coords.longitude.toFixed(6);
          alert('Coordinates captured successfully!');
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
    let actionUrl = this.action;
    
    // Disable submit button during processing
    const submitBtn = document.getElementById("submitBtn");
    const originalText = submitBtn.innerText;
    submitBtn.innerText = "Processing...";
    submitBtn.disabled = true;

    fetch(actionUrl, {
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
    })
    .finally(() => {
        submitBtn.innerText = originalText;
        submitBtn.disabled = false;
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

<style>
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(3px);
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease;
        padding: 20px;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-content {
        background-color: #fff;
        width: 100%;
        max-width: 1000px;
        max-height: calc(100vh - 40px);
        display: flex;
        flex-direction: column;
        animation: slideDown 0.3s ease;
        position: relative;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    }

    .modal-header {
        flex-shrink: 0;
    }

    .modal-body {
        overflow-y: auto;
        overflow-x: hidden;
        flex: 1;
    }

    @keyframes slideDown {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Button Hover Effects */
    .edit-btn-premium:hover {
        background: #e67e22 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(243, 156, 18, 0.4);
    }

    .delete-btn:hover {
        background: #c0392b !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
    }

    .close-btn:hover {
        opacity: 1 !important;
        transform: rotate(90deg);
    }

    /* Form Input Focus States */
    .filter-input:focus {
        outline: none;
        border-color: #3498db !important;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    /* Scrollbar Styling for Modal Body */
    .modal-body::-webkit-scrollbar {
        width: 10px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #3498db;
        border-radius: 10px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #2980b9;
    }

    /* Submit Button Hover */
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(22, 160, 133, 0.4) !important;
    }

    /* Get Location Button Hover */
    .edit-btn:hover {
        background: #3498db !important;
        color: white !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .modal {
            padding: 10px;
        }
        
        .modal-content {
            max-height: calc(100vh - 20px);
        }

        .form-left, .form-right {
            grid-column: span 2;
        }
    }
</style>

@endsection