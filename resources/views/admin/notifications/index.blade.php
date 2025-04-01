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
  </div>
  
  <div class="tabs-container">
    <ul class="tabs">
      <li><a href="{{ route('admin.notifications', ['tab' => 'send']) }}" class="{{ $tab == 'send' ? 'active' : '' }}">Send Notification</a></li>
      <li><a href="{{ route('admin.notifications', ['tab' => 'scheduled']) }}" class="{{ $tab == 'scheduled' ? 'active' : '' }}">Scheduled Notifications</a></li>
    </ul>
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

  @if($tab == 'send')
    <div class="form-section">
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
          <label class="form-label">Audience</label>
          <div class="form-check">
            <input type="radio" id="all_users" name="audience" value="all_users" class="form-check-input" checked>
            <label for="all_users">All Users</label>
          </div>
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
  @elseif($tab == 'scheduled')
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
              <td class="scheduled-date">{{ $notification->scheduled_at->format('M d, Y h:i A') }}</td>
              <td class="status">
      @if($notification->is_sent == 1)
        <span class="status-sent">Sent</span>
      @elseif($notification->is_sent == 0)
        <span class="status-scheduled">Scheduled</span>
      @endif
    </td><td>
                <form action="{{ route('notifications.send-now', $notification->id) }}" method="POST" class="send-now-form">
                  @csrf
                  <button type="submit" class="edit-btn">Send Now</button>
                </form>
                
                <form action="{{ route('notifications.delete', $notification->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirmDelete()">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="delete-btn">Delete</button>
                </form>
              </td>
            </tr>
            @endforeach
          @else
            <tr>
              <td colspan="7" class="text-center">No scheduled notifications found.</td>
            </tr>
          @endif
        </tbody>
      </table>
      <div class="pagination-container">
        {{ $notifications->appends(request()->query())->links('vendor.pagination.custom') }}
      </div>
    </div>
  @endif
</div>

<script>
  // Display the scheduled options when checkbox is checked
  document.addEventListener('DOMContentLoaded', function() {
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
    
    // Banner preview functionality
    const bannerInput = document.getElementById('banner');
    const bannerPreview = document.getElementById('bannerPreview');
    const previewImage = document.getElementById('previewImage');
    
    if(bannerInput) {
      bannerInput.addEventListener('change', function() {
        if(this.files && this.files[0]) {
          const reader = new FileReader();
          
          reader.onload = function(e) {
            previewImage.src = e.target.result;
            bannerPreview.style.display = 'block';
          }
          
          reader.readAsDataURL(this.files[0]);
        } else {
          bannerPreview.style.display = 'none';
        }
      });
    }
  });
  
  function confirmDelete() {
    return confirm("Are you sure you want to delete this notification? This action cannot be undone.");
  }
</script>
@endsection