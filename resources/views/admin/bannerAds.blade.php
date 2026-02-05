@extends('layouts.admin')

@section('title', 'Banner Ads')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/banner-ads.css') }}">
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">

<div class="banner-container">
  <div class="header-container">
    <div>
      <h3 class="categories-label" style="font-size: 1.5rem; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">Banner Advertisements</h3>
      <p class="categories-description" style="color: #777; font-size: 0.95rem;">Manage homepage and section banner ads</p>
    </div>
    <button class="btn-add-premium" id="openModalBtn">
      <i class="fas fa-plus-circle"></i> Add New Banner Ad
    </button>
  </div>

  <div class="filter-section">
    <form action="{{ route('admin.banner-ads') }}" method="GET">
      <div class="filter-grid">
        <div class="filter-control">
          <label>Search Banners</label>
          <input type="text" name="search" placeholder="Search by title..." value="{{ request()->search }}" class="filter-input">
        </div>

        <div class="btn-group">
          <button type="submit" class="filter-btn">Apply</button>
          <a href="{{ route('admin.banner-ads') }}" class="reset-btn">Reset</a>
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
          <th>Banner Info</th>
          <th>Status</th>
          <th>Analytics</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($bannerAds as $index => $banner)
        <tr>
          <td>{{ $bannerAds->firstItem() + $index }}</td>
          <td>
            <div style="display: flex; align-items: center; gap: 15px;">
                @if($banner->image)
                    <img src="{{ asset('storage/' . $banner->image) }}" style="width: 120px; height: 60px; border-radius: 8px; object-fit: cover; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                @else
                    <div style="width: 120px; height: 60px; border-radius: 8px; background: #eee; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-image" style="color: #ccc;"></i>
                    </div>
                @endif
                <div style="display: flex; flex-direction: column;">
                    <span style="font-weight: 600; color: #2c3e50;">{{ $banner->title }}</span>
                    <span style="font-size: 0.75rem; color: #3498db;">{{ $banner->link ?: 'No Link' }}</span>
                </div>
            </div>
          </td>
          <td>
            <label class="switch">
              <input type="checkbox" class="status-toggle" data-id="{{ $banner->id }}" {{ $banner->status ? 'checked' : '' }}>
              <span class="slider round"></span>
            </label>
            <span style="font-size: 0.8rem; margin-left: 10px; color: {{ $banner->status ? '#27ae60' : '#e74c3c' }}; font-weight: 600;">
                {{ $banner->status ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="badge-premium badge-info">
                    <i class="fas fa-mouse-pointer" style="margin-right: 5px;"></i> {{ number_format($banner->touch) }} Taps
                </span>
            </div>
          </td>
          <td>
            <form action="{{ route('admin.banner-ads.destroy', $banner->id) }}" method="POST" onsubmit="return confirmDelete()">
              @csrf
              @method('DELETE')
              <button type="submit" class="delete-btn">
                <i class="fas fa-trash"></i> Delete
              </button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="pagination-container">
        {{ $bannerAds->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
  </div>
</div>

<style>
    .banner-container { padding: 20px; }
    .switch { position: relative; display: inline-block; width: 46px; height: 24px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; }
    input:checked + .slider { background-color: #F2652D; }
    input:checked + .slider:before { transform: translateX(22px); }
    .slider.round { border-radius: 34px; }
    .slider.round:before { border-radius: 50%; }
</style>

<div id="categoryModal" class="modal">
  <div class="modal-content" style="max-width: 600px;">
    <div class="modal-header">
        <h3 id="modal-title">Add New Banner Ad</h3>
        <button type="button" class="close-btn" id="closeModalBtn">&times;</button>
    </div>
    <div class="modal-body">
        <form action="{{ route('admin.banner-ads.store') }}" method="POST" enctype="multipart/form-data" id="categoryForm">
          @csrf
          <input type="hidden" id="category-id" name="id">

          <div class="filter-control" style="margin-bottom: 15px;">
            <label class="required-label">Title</label>
            <input type="text" id="banner-title" name="title" required class="filter-input" style="width: 100%;">
          </div>

          <div class="filter-control" style="margin-bottom: 15px;">
            <label class="required-label">Image</label>
            <input type="file" id="banner-image" name="image" accept="image/*" required class="filter-input" style="width: 100%;">
          </div>

          <!-- Link Types -->
          <div class="filter-control" style="margin-bottom: 15px;">
            <label>Link To:</label>
            <div style="display: flex; gap: 20px; margin-top: 5px;">
                <label style="display:flex; align-items: center; gap: 5px; font-weight: normal; cursor: pointer;">
                  <input type="radio" name="link_type" value="custom" checked onchange="toggleLinkType(this.value)"> Custom
                </label>
                <label style="display:flex; align-items: center; gap: 5px; font-weight: normal; cursor: pointer;">
                  <input type="radio" name="link_type" value="business" onchange="toggleLinkType(this.value)"> Business
                </label>
                <label style="display:flex; align-items: center; gap: 5px; font-weight: normal; cursor: pointer;">
                  <input type="radio" name="link_type" value="tourist_place" onchange="toggleLinkType(this.value)"> Tourist Place
                </label>
            </div>
          </div>

          <div class="filter-control" style="margin-bottom: 15px;">
            <label for="banner-link">Redirection Link / ID</label>
            <input type="text" id="banner-link" name="link" class="filter-input" style="width: 100%;" placeholder="Enter URL or ID">
          </div>

          <!-- Business Selection -->
          <div id="businessSelectDiv" style="display:none; margin-bottom: 15px;">
              <label for="businessSelect">Select Business</label>
              <select name="business_id" id="businessSelect" class="filter-input" style="width: 100%;">
                <option value="">-- Select Business --</option>
                @if(isset($businesses))
                  @foreach($businesses as $business)
                    <option value="{{ $business->id }}" 
                            data-title="{{ $business->name }}" 
                            data-image="{{ $business->thumbnail ? asset('storage/' . $business->thumbnail) : '' }}">
                      {{ Str::limit($business->name, 60) }}
                    </option>
                  @endforeach
                @endif
              </select>
          </div>

          <!-- Tourist Place Selection -->
          <div id="touristPlaceSelectDiv" style="display:none; margin-bottom: 15px;">
              <label for="touristPlaceSelect">Select Tourist Place</label>
              <select name="tourist_place_id" id="touristPlaceSelect" class="filter-input" style="width: 100%;">
                <option value="">-- Select Tourist Place --</option>
                @if(isset($touristPlaces))
                  @foreach($touristPlaces as $place)
                    <option value="{{ $place->id }}" 
                            data-title="{{ $place->name }}" 
                            data-image="{{ $place->thumbnail ? asset('storage/' . $place->thumbnail) : '' }}">
                      {{ Str::limit($place->name, 60) }}
                    </option>
                  @endforeach
                @endif
              </select>
          </div>

          <div class="filter-control" style="margin-bottom: 15px;">
            <label for="banner-status" class="required-label">Status</label>
            <select id="banner-status" name="status" class="filter-input" style="width: 100%;">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
          </div>

          <button type="submit" class="submit-btn" style="margin-top: 20px;">Save Banner Ad</button>
        </form>
    </div>
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

    // Handle Business Selection
    const businessSelect = document.getElementById('businessSelect');
    if (businessSelect) {
      businessSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
          document.getElementById('banner-title').value = selectedOption.getAttribute('data-title');
          // Note: Image input cannot be set programmatically
        }
      });
    }

    // Handle Tourist Place Selection
    const touristPlaceSelect = document.getElementById('touristPlaceSelect');
    if (touristPlaceSelect) {
      touristPlaceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
          document.getElementById('banner-title').value = selectedOption.getAttribute('data-title');
        }
      });
    }
  });

  function toggleLinkType(type) {
    const customLinkInput = document.getElementById('banner-link');
    const businessSelectDiv = document.getElementById('businessSelectDiv');
    const touristPlaceSelectDiv = document.getElementById('touristPlaceSelectDiv');
    
    // Reset selections
    document.getElementById('businessSelect').value = '';
    document.getElementById('touristPlaceSelect').value = '';

    if (type === 'custom') {
      customLinkInput.parentElement.style.display = 'block'; // Or however you want to handle it, maybe disable
      customLinkInput.disabled = false;
      businessSelectDiv.style.display = 'none';
      touristPlaceSelectDiv.style.display = 'none';
    } else if (type === 'business') {
      customLinkInput.disabled = true;
      customLinkInput.value = '';
      businessSelectDiv.style.display = 'block';
      touristPlaceSelectDiv.style.display = 'none';
    } else if (type === 'tourist_place') {
      customLinkInput.disabled = true;
      customLinkInput.value = '';
      businessSelectDiv.style.display = 'none';
      touristPlaceSelectDiv.style.display = 'block';
    }
  }
</script>


@endsection