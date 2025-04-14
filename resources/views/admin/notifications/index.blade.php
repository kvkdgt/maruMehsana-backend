@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/notifications.css'); }}">
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">

<div class="categories-container">
  <div class="header-container">
    <div>
      <h3 class="categories-label">Notifications</h3>
      <p class="categories-description">Send and manage notifications to users.</p>
    </div>
    <button id="openFormButton" class="add-category-btn">Send Notification</button>
  </div>
  
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
<!-- Filter Section -->
<div class="filters-card">
  <div class="filters-header">
    <i class="fas fa-filter"></i>
    <span>Filter Notifications</span>
  </div>
  
  <form action="{{ route('admin.notifications') }}" method="GET" class="filters-form">
    <div class="filters-body">
      <div class="filter-group">
        <label>
          <span class="filter-label">Type</span>
          <select name="type" class="filter-select">
            <option value="">All Types</option>
            <option value="direct" {{ request('type') == 'direct' ? 'selected' : '' }}>Direct Sent</option>
            <option value="scheduled" {{ request('type') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
          </select>
        </label>
      </div>
      
      <div class="filter-group">
        <label>
          <span class="filter-label">Status</span>
          <select name="status" class="filter-select">
            <option value="">All Status</option>
            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
          </select>
        </label>
      </div>
      
      <div class="filter-group">
        <label>
          <span class="filter-label">Image</span>
          <select name="image" class="filter-select">
            <option value="">All</option>
            <option value="with" {{ request('image') == 'with' ? 'selected' : '' }}>With Image</option>
            <option value="without" {{ request('image') == 'without' ? 'selected' : '' }}>Without Image</option>
          </select>
        </label>
      </div>
      
      <div class="filter-actions">
        <button type="submit" class="filter-apply-btn">
          <i class="fas fa-search"></i> Apply
        </button>
        <a href="{{ route('admin.notifications') }}" class="filter-reset-btn">
          <i class="fas fa-redo"></i> Reset
        </a>
      </div>
    </div>
  </form>
</div>
  <!-- Notification Listing Table -->
  <div class="table-container">
    <table class="categories-table">
      <thead>
        <tr>
          <th>Sr. No.</th>
          <th>Title</th>
          <th>Description</th>
          <th>Banner</th>
          <th>Audience</th>
          <th>Scheduled For</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @if(count($notifications) > 0)
          @foreach($notifications as $index => $notification)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $notification->title }}</td>
            <td>{{ Str::limit($notification->description, 50) }}</td>
            <td>
              @if($notification->banner)
                <img src="{{ asset('storage/' . $notification->banner) }}" alt="Banner" width="50">
              @else
                N/A
              @endif
            </td>
            <td>
              @if($notification->audience == 'all_users')
                All Users
              @endif
            </td>
            <td class="scheduled-date">
              {{ $notification->scheduled_at ? $notification->scheduled_at->format('M d, Y h:i A') : 'Direct Sent' }}
            </td>
            <td class="status">
              @if($notification->is_sent == 1)
                <span class="status-sent">Sent</span>
              @elseif($notification->is_sent == 0)
                <span class="status-scheduled">Scheduled</span>
              @endif
            </td>
            <td>
  <form action="{{ route('notifications.send-now', $notification->id) }}" method="POST" class="send-now-form">
    @csrf
    <button type="submit" class="icon-btn edit-btn" title="Send Now">
      <i class="fas fa-paper-plane"></i>
    </button>
  </form>
  @if($notification->is_sent)
    <a href="{{ route('admin.notifications.logs', $notification->id) }}" class="icon-btn btn-info" title="View Logs">
      <i class="fas fa-list-alt"></i>
    </a>
  @endif
  
  <form action="{{ route('notifications.delete', $notification->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirmDelete()">
    @csrf
    @method('DELETE')
    <button type="submit" class="icon-btn delete-btn" title="Delete">
      <i class="fas fa-trash"></i>
    </button>
  </form>
</td>
          </tr>
          @endforeach
        @else
          <tr>
            <td colspan="8" class="text-center">No notifications found.</td>
          </tr>
        @endif
      </tbody>
    </table>
    <div class="pagination-container">
      {{ $notifications->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
  </div>
</div>

<!-- Send Notification Modal -->
<!-- Modified Send Notification Modal -->
<div id="sendNotificationModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" id="closeFormButton">&times;</span>
    <h3>Send Notification</h3>
    
    <div class="modal-flex-container">
      <!-- Left side - Form -->
      <div class="form-container">
        <form action="{{ route('notifications.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="form-row">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" id="title" class="form-control" required>
          </div>
          
          <div class="form-row">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" required></textarea>
          </div>
          
          <div class="form-row">
            <label for="banner" class="form-label">Banner (Optional)</label>
            <input type="file" name="banner" id="banner" class="form-control" accept="image/*">
            <div class="banner-preview" id="bannerPreview" style="display: none;">
              <img id="previewImage" src="#" alt="Banner Preview">
            </div>
          </div>
          
          <div class="audience-section">
            <label for="audience" class="form-label">Audience</label>
            <select name="audience" id="audience" class="form-select">
              <option value="all_users" selected>All Users</option>
            </select>
          </div>
          
          <div class="form-row">
            <div class="form-check">
              <input type="checkbox" id="scheduleCheckbox" name="schedule" value="yes" class="form-check-input">
              <label for="scheduleCheckbox">Schedule for later</label>
            </div>
            
            <div class="scheduled-options" id="scheduledOptions" style="display: none;">
              <div class="date-time-inputs">
                <div class="form-row">
                  <label for="scheduled_date" class="form-label">Date</label>
                  <input type="date" name="scheduled_date" id="scheduled_date" class="form-control">
                </div>
                <div class="form-row">
                  <label for="scheduled_time" class="form-label">Time</label>
                  <input type="time" name="scheduled_time" id="scheduled_time" class="form-control">
                </div>
              </div>
            </div>
          </div>
          
          <button type="submit" class="submit-btn">
            <span id="submitButtonText">Send Notification</span>
          </button>
        </form>
      </div>
      
      <!-- Right side - Preview -->
      <!-- Right side - Preview -->
<div class="notification-preview-container">
  <h4>Notification Preview</h4>
  <div class="notification-preview">
    <div class="notification-device">
      <div class="device-status-bar">
        <div class="status-time">12:30</div>
        <div class="status-icons">
          <i class="fas fa-wifi"></i>
          <i class="fas fa-battery-three-quarters"></i>
        </div>
      </div>
      
      <div class="notification-drawer">
        <div class="notification-drawer-header">
          <div class="drawer-title">Notifications</div>
          <div class="drawer-clear">Clear all</div>
        </div>
        
        <!-- Main notification (the one being created) -->
        <div class="notification-card">
          <div class="notification-header">
            <img src="{{ URL::asset('assets/images/app-icon.png') }}" alt="App Icon" class="app-icon" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'20\' height=\'20\' viewBox=\'0 0 20 20\'><rect width=\'20\' height=\'20\' fill=\'%232596be\'/><text x=\'50%\' y=\'50%\' font-size=\'12\' text-anchor=\'middle\' fill=\'white\' dominant-baseline=\'middle\'>M</text></svg>';">
            <div class="notification-app-info">
              <div class="app-name">Maru Mehsana</div>
              <div class="notification-time">now</div>
            </div>
          </div>
          <div class="notification-content">
            <h5 id="preview-title">Notification Title</h5>
            <p id="preview-description">Your notification description will appear here. Fill in the form to see a preview.</p>
          </div>
          <div id="preview-banner-container" class="notification-banner" style="display: none;">
            <img id="preview-banner" src="#" alt="Notification Banner">
          </div>
        </div>
        
        <!-- Previous notifications (for visual effect) -->
        <div class="notification-card earlier-notification">
          <div class="notification-header">
            <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20'><rect width='20' height='20' fill='%23f39c12'/><text x='50%' y='50%' font-size='12' text-anchor='middle' fill='white' dominant-baseline='middle'>E</text></svg>" alt="Email Icon" class="app-icon">
            <div class="notification-app-info">
              <div class="app-name">Email</div>
              <div class="notification-time">15m ago</div>
            </div>
          </div>
          <div class="notification-content">
            <h5>New message received</h5>
            <p>You have a new message in your inbox.</p>
          </div>
        </div>
        
        <div class="notification-card earlier-notification">
          <div class="notification-header">
            <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20'><rect width='20' height='20' fill='%233498db'/><text x='50%' y='50%' font-size='12' text-anchor='middle' fill='white' dominant-baseline='middle'>S</text></svg>" alt="Social Icon" class="app-icon">
            <div class="notification-app-info">
              <div class="app-name">Social App</div>
              <div class="notification-time">1h ago</div>
            </div>
          </div>
          <div class="notification-content">
            <h5>New friend request</h5>
            <p>You have a new friend request waiting for your response.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
    </div>
  </div>
</div>

<script>
 document.addEventListener('DOMContentLoaded', function() {
  // Get form elements
  const titleInput = document.getElementById('title');
  const descriptionInput = document.getElementById('description');
  const bannerInput = document.getElementById('banner');
  
  // Get preview elements
  const previewTitle = document.getElementById('preview-title');
  const previewDescription = document.getElementById('preview-description');
  const previewBannerContainer = document.getElementById('preview-banner-container');
  const previewBanner = document.getElementById('preview-banner');
  
  // Update preview as user types title
  titleInput.addEventListener('input', function() {
    previewTitle.textContent = this.value || 'Notification Title';
  });
  
  // Update preview as user types description
  descriptionInput.addEventListener('input', function() {
    previewDescription.textContent = this.value || 'Your notification description will appear here. Fill in the form to see a preview.';
  });
  
  // Update preview when user selects a banner image
  bannerInput.addEventListener('change', function() {
    if(this.files && this.files[0]) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
        previewBanner.src = e.target.result;
        previewBannerContainer.style.display = 'block';
        
        // Existing banner preview functionality
        previewImage.src = e.target.result;
        bannerPreview.style.display = 'block';
      }
      
      reader.readAsDataURL(this.files[0]);
    } else {
      previewBannerContainer.style.display = 'none';
      bannerPreview.style.display = 'none';
    }
  });
  
  // Existing modal functionality
  const modal = document.getElementById('sendNotificationModal');
  const openFormButton = document.getElementById('openFormButton');
  const closeFormButton = document.getElementById('closeFormButton');
  
  openFormButton.addEventListener('click', function() {
    modal.style.display = 'block';
  });
  
  closeFormButton.addEventListener('click', function() {
    modal.style.display = 'none';
  });
  
  window.addEventListener('click', function(event) {
    if (event.target == modal) {
      modal.style.display = 'none';
    }
  });
  
  // Existing scheduled options functionality
  const scheduleCheckbox = document.getElementById('scheduleCheckbox');
  const scheduledOptions = document.getElementById('scheduledOptions');
  const submitButtonText = document.getElementById('submitButtonText');
  
  if(scheduleCheckbox) {
    scheduleCheckbox.addEventListener('change', function() {
      if(this.checked) {
        scheduledOptions.style.display = 'block';
        submitButtonText.textContent = 'Schedule Notification';
      } else {
        scheduledOptions.style.display = 'none';
        submitButtonText.textContent = 'Send Notification';
      }
    });
  }
});

function confirmDelete() {
  return confirm("Are you sure you want to delete this notification? This action cannot be undone.");
}
</script>
@endsection