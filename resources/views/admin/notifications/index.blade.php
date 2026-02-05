@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/notifications.css'); }}">
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">

<div class="notifications-container">
  <div class="header-container">
    <div>
      <h3 class="categories-label" style="font-size: 1.5rem; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">Push Notifications</h3>
      <p class="categories-description" style="color: #777; font-size: 0.95rem;">Broadcast messages and alerts to your app users</p>
    </div>
    <button id="openFormButton" class="btn-add-premium">
      <i class="fas fa-paper-plane"></i> Send Notification
    </button>
  </div>
  
  @if (session('success'))
  <div class="alert alert-success">
    <span class="alert-icon">&#10004;</span>
    <div class="alert-text">{{ session('success') }}</div>
    <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
  </div>
  @endif

  <!-- Live Progress Section -->
  <div id="progressSection" class="progress-section" style="display: none;">
    <div class="progress-header">
      <h4><i class="fas fa-tasks"></i> Active Broadcast Progress</h4>
      <button class="close-progress-btn" onclick="hideProgressSection()"><i class="fas fa-times"></i></button>
    </div>
    <div id="progressCards"></div>
  </div>

  <div class="filter-section">
    <form action="{{ route('admin.notifications') }}" method="GET">
      <div class="filter-grid">
        <div class="filter-control">
          <label>Type</label>
          <select name="type" class="filter-input">
            <option value="">All Types</option>
            <option value="direct" {{ request('type') == 'direct' ? 'selected' : '' }}>Direct Sent</option>
            <option value="scheduled" {{ request('type') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
          </select>
        </div>
        
        <div class="filter-control">
          <label>Status</label>
          <select name="status" class="filter-input">
            <option value="">All Status</option>
            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
          </select>
        </div>
        
        <div class="filter-control">
          <label>Image Presence</label>
          <select name="image" class="filter-input">
            <option value="">Any</option>
            <option value="with" {{ request('image') == 'with' ? 'selected' : '' }}>With Image</option>
            <option value="without" {{ request('image') == 'without' ? 'selected' : '' }}>Without Image</option>
          </select>
        </div>

        <div class="btn-group">
          <button type="submit" class="filter-btn">Apply</button>
          <a href="{{ route('admin.notifications') }}" class="reset-btn">Reset</a>
        </div>
      </div>
    </form>
  </div>

  <div class="premium-table-container">
    <table class="premium-table">
      <thead>
        <tr>
          <th>Sr. No.</th>
          <th>Notification Info</th>
          <th>Redirection / Banner</th>
          <th>Scheduled For</th>
          <th>Status / Analytics</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @if(count($notifications) > 0)
          @foreach($notifications as $index => $notification)
          <tr>
            <td>{{ $notifications->firstItem() + $index }}</td>
            <td>
                <div style="display: flex; flex-direction: column;">
                    <span style="font-weight: 600; color: #2c3e50;">{{ $notification->title }}</span>
                    <span style="font-size: 0.8rem; color: #777;">{{ Str::limit($notification->description, 60) }}</span>
                </div>
            </td>
            <td>
              <div style="display: flex; align-items: center; gap: 10px;">
                @if($notification->banner)
                    <img src="{{ asset('storage/' . $notification->banner) }}" style="width: 50px; height: 35px; border-radius: 4px; object-fit: cover; border: 1px solid #eee;">
                @endif
                <span class="badge-premium badge-info" style="font-size: 0.7rem;">
                    {{ ucfirst(str_replace('_', ' ', $notification->type ?? 'General')) }}
                </span>
              </div>
            </td>
            <td>
                <div style="font-size: 0.85rem; color: #555;">
                    @if($notification->scheduled_at)
                        <i class="far fa-calendar-alt" style="margin-right: 5px;"></i> {{ $notification->scheduled_at->format('d M, h:i A') }}
                    @else
                        <span style="color: #999; font-style: italic;">Instant</span>
                    @endif
                </div>
            </td>
            <td>
              <div style="display: flex; align-items: center; gap: 10px;">
                @if($notification->is_sent == 1)
                  <span class="badge-premium badge-success">Sent</span>
                  <button class="icon-btn" onclick="showProgress({{ $notification->id }})" title="View Analytics" style="color: #3498db; background: none; border: none; cursor: pointer;">
                    <i class="fas fa-chart-bar"></i>
                  </button>
                @else
                  <span class="badge-premium badge-warning">Scheduled</span>
                @endif
              </div>
            </td>
            <td>
               <div style="display: flex; gap: 8px;">
                    @if(!$notification->is_sent)
                        <form action="{{ route('notifications.send-now', $notification->id) }}" method="POST">
                          @csrf
                          <button type="submit" class="edit-btn" title="Send Now" style="background-color: #ebfbee; color: #2b8a3e; border-color: #b2f2bb;">
                            <i class="fas fa-paper-plane"></i>
                          </button>
                        </form>
                    @endif

                    @if($notification->is_sent)
                        <a href="{{ route('admin.notifications.logs', $notification->id) }}" class="edit-btn" title="View Logs">
                          <i class="fas fa-history"></i> Logs
                        </a>
                    @endif
                    
                    <form action="{{ route('notifications.delete', $notification->id) }}" method="POST" onsubmit="return confirmDelete()">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-btn" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                    </form>
               </div>
            </td>
          </tr>
          @endforeach
        @else
          <tr>
            <td colspan="6" style="text-align: center; padding: 40px; color: #999;">No notifications matching your filters.</td>
          </tr>
        @endif
      </tbody>
    </table>
    <div class="pagination-container">
      {{ $notifications->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
  </div>
</div>

<style>
    .notifications-container { padding: 20px; }
    .icon-btn { font-size: 1.1rem; transition: transform 0.2s; padding: 5px; }
    .icon-btn:hover { transform: scale(1.2); }
    .progress-section { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-left: 5px solid #F2652D; }
    .progress-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .close-progress-btn { background: none; border: none; cursor: pointer; color: #999; }
</style>

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
            <label for="notificationType" class="form-label">Notification Type</label>
            <select name="type" id="notificationType" class="form-select" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
              <option value="general">General Notification</option>
              <option value="news">News Article (Deep Link)</option>
              <option value="business">Business (Deep Link)</option>
              <option value="tourist_place">Tourist Place (Deep Link)</option>
            </select>
          </div>
          
          <div class="form-row" id="newsSelectRow" style="display: none;">
            <label for="newsArticleSelect" class="form-label">Select News Article</label>
            <select name="news_article_id" id="newsArticleSelect" class="form-select" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
              <option value="">-- Select News Article --</option>
              @if(isset($newsArticles))
                @foreach($newsArticles as $article)
                  <option value="{{ $article->id }}" 
                          data-title="{{ $article->title }}" 
                          data-desc="{{ $article->excerpt }}"
                          data-image="{{ $article->image ? asset('storage/news/' . $article->image) : '' }}">
                    {{ Str::limit($article->title, 60) }}
                  </option>
                @endforeach
              @endif
            </select>
            <small style="color: #666; font-size: 0.85em; display: block; margin-top: 5px;">Selecting a news article will auto-fill the Title, Description and Banner.</small>
          </div>

          <div class="form-row" id="businessSelectRow" style="display: none;">
            <label for="businessSelect" class="form-label">Select Business</label>
            <select name="business_id" id="businessSelect" class="form-select" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
              <option value="">-- Select Business --</option>
              @if(isset($businesses))
                @foreach($businesses as $business)
                  <option value="{{ $business->id }}" 
                          data-title="{{ $business->name }}" 
                          data-desc="{{ $business->description }}"
                          data-image="{{ $business->thumbnail ? asset('storage/' . $business->thumbnail) : '' }}">
                    {{ Str::limit($business->name, 60) }}
                  </option>
                @endforeach
              @endif
            </select>
            <small style="color: #666; font-size: 0.85em; display: block; margin-top: 5px;">Selecting a business will auto-fill the Title, Description and Banner.</small>
          </div>

          <div class="form-row" id="touristPlaceSelectRow" style="display: none;">
            <label for="touristPlaceSelect" class="form-label">Select Tourist Place</label>
            <select name="tourist_place_id" id="touristPlaceSelect" class="form-select" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
              <option value="">-- Select Tourist Place --</option>
              @if(isset($touristPlaces))
                @foreach($touristPlaces as $place)
                  <option value="{{ $place->id }}" 
                          data-title="{{ $place->name }}" 
                          data-desc="{{ $place->description }}"
                          data-image="{{ $place->thumbnail ? asset('storage/' . $place->thumbnail) : '' }}">
                    {{ Str::limit($place->name, 60) }}
                  </option>
                @endforeach
              @endif
            </select>
            <small style="color: #666; font-size: 0.85em; display: block; margin-top: 5px;">Selecting a place will auto-fill the Title, Description and Banner.</small>
          </div>
            <div class="form-row">
              <label for="title" class="form-label">
                Title 
                <button type="button" class="emoji-trigger-btn" onclick="openEmojiPicker('title')">😀</button>
              </label>
              <input type="text" name="title" id="title" class="form-control" required>
            </div>
            
            <div class="form-row">
              <label for="description" class="form-label">
                Description
                <button type="button" class="emoji-trigger-btn" onclick="openEmojiPicker('description')">😀</button>
              </label>
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
  
  // New Elements for Type/News/Business/TouristPlace
  const notificationType = document.getElementById('notificationType');
  const newsSelectRow = document.getElementById('newsSelectRow');
  const newsArticleSelect = document.getElementById('newsArticleSelect');
  const businessSelectRow = document.getElementById('businessSelectRow');
  const businessSelect = document.getElementById('businessSelect');
  const touristPlaceSelectRow = document.getElementById('touristPlaceSelectRow');
  const touristPlaceSelect = document.getElementById('touristPlaceSelect');
  
  // Handle Type Change
  if (notificationType) {
    notificationType.addEventListener('change', function() {
      // Reset all
      newsSelectRow.style.display = 'none';
      if(newsArticleSelect) newsArticleSelect.value = '';
      
      businessSelectRow.style.display = 'none';
      if(businessSelect) businessSelect.value = '';

      touristPlaceSelectRow.style.display = 'none';
      if(touristPlaceSelect) touristPlaceSelect.value = '';

      if (this.value === 'news') {
        newsSelectRow.style.display = 'block';
      } else if (this.value === 'business') {
        businessSelectRow.style.display = 'block';
      } else if (this.value === 'tourist_place') {
        touristPlaceSelectRow.style.display = 'block';
      }
    });
  }
  
  // Helper to autofill form
  function autoFillForm(selectElement) {
      const selectedOption = selectElement.options[selectElement.selectedIndex];
      if (selectedOption.value) {
        // Auto-fill Title
        const title = selectedOption.getAttribute('data-title');
        titleInput.value = title;
        titleInput.dispatchEvent(new Event('input')); // Trigger preview update
        
        // Auto-fill Description
        const desc = selectedOption.getAttribute('data-desc');
        descriptionInput.value = desc;
        descriptionInput.dispatchEvent(new Event('input')); // Trigger preview update
        
        // Auto-fill Banner Preview
        const imageUrl = selectedOption.getAttribute('data-image');
        if (imageUrl) {
           const previewBanner = document.getElementById('preview-banner');
           const previewBannerContainer = document.getElementById('preview-banner-container');
           
           previewBanner.src = imageUrl;
           previewBannerContainer.style.display = 'block';
           
           // Also update the small preview in the form if it exists
           const smallPreview = document.getElementById('previewImage');
           const smallPreviewContainer = document.getElementById('bannerPreview');
           if (smallPreview && smallPreviewContainer) {
             smallPreview.src = imageUrl;
             smallPreviewContainer.style.display = 'block';
           }
        }
      }
  }

  // Handle News Selection (Auto-fill)
  if (newsArticleSelect) {
    newsArticleSelect.addEventListener('change', function() {
      autoFillForm(this);
    });
  }

  // Handle Business Selection (Auto-fill)
  if (businessSelect) {
    businessSelect.addEventListener('change', function() {
      autoFillForm(this);
    });
  }

  // Handle Tourist Place Selection (Auto-fill)
  if (touristPlaceSelect) {
    touristPlaceSelect.addEventListener('change', function() {
      autoFillForm(this);
    });
  }
  
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

// Progress tracking
let progressInterval = null;
let activeNotificationId = null;

function showProgress(notificationId) {
  activeNotificationId = notificationId;
  document.getElementById('progressSection').style.display = 'block';
  fetchProgress(notificationId);
  
  // Auto-refresh every 2 seconds
  if (progressInterval) clearInterval(progressInterval);
  progressInterval = setInterval(() => fetchProgress(notificationId), 2000);
}

function hideProgressSection() {
  document.getElementById('progressSection').style.display = 'none';
  if (progressInterval) {
    clearInterval(progressInterval);
    progressInterval = null;
  }
}

function fetchProgress(notificationId) {
  fetch(`/admin/notifications/${notificationId}/progress`)
    .then(response => response.json())
    .then(data => {
      renderProgressCard(data);
      
      // Stop polling if complete
      if (data.is_complete && progressInterval) {
        clearInterval(progressInterval);
        progressInterval = null;
      }
    })
    .catch(error => console.error('Error fetching progress:', error));
}

function renderProgressCard(data) {
  const container = document.getElementById('progressCards');
  const statusClass = data.is_complete ? 'complete' : 'processing';
  const statusText = data.is_complete ? 'Completed' : 'Processing...';
  
  container.innerHTML = `
    <div class="progress-card ${statusClass}">
      <div class="progress-card-header">
        <h5>${data.title}</h5>
        <span class="progress-status ${statusClass}">${statusText}</span>
      </div>
      
      <div class="progress-bar-container">
        <div class="progress-bar" style="width: ${data.progress}%"></div>
        <span class="progress-text">${data.progress}%</span>
      </div>
      
      <div class="progress-stats">
        <div class="stat-item total">
          <i class="fas fa-users"></i>
          <span class="stat-value">${data.total_users}</span>
          <span class="stat-label">Total Users</span>
        </div>
        <div class="stat-item success">
          <i class="fas fa-check-circle"></i>
          <span class="stat-value">${data.sent}</span>
          <span class="stat-label">Sent</span>
        </div>
        <div class="stat-item danger">
          <i class="fas fa-times-circle"></i>
          <span class="stat-value">${data.failed}</span>
          <span class="stat-label">Failed</span>
        </div>
        <div class="stat-item warning">
          <i class="fas fa-clock"></i>
          <span class="stat-value">${data.pending}</span>
          <span class="stat-label">Pending</span>
        </div>
      </div>
      
      ${data.is_complete ? `
        <div class="progress-summary">
          <strong>Success Rate:</strong> ${data.success_rate}%
        </div>
      ` : `
        <div class="progress-summary processing">
          <i class="fas fa-spinner fa-spin"></i> Processing notifications...
        </div>
      `}
    </div>
  `;
}

// Check for active notifications on page load
document.addEventListener('DOMContentLoaded', function() {
  checkActiveNotifications();
});

function checkActiveNotifications() {
  fetch('/admin/notifications-progress')
    .then(response => response.json())
    .then(data => {
      if (data.has_active && data.notifications.length > 0) {
        showProgress(data.notifications[0].notification_id);
      }
    })
    .catch(error => console.error('Error checking active notifications:', error));
}
</script>
<!-- Emoji Picker Modal -->
<div id="emojiModal" class="modal">
  <div class="modal-content emoji-modal-content">
    <div class="emoji-header">
      <h3>Select Emoji</h3>
      <span class="close-btn" onclick="closeEmojiPicker()">&times;</span>
    </div>
    <div class="emoji-grid" id="emojiGrid"></div>
  </div>
</div>

<style>
.emoji-trigger-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  transition: transform 0.2s;
}
.emoji-trigger-btn:hover {
  transform: scale(1.2);
}
.emoji-modal-content {
  max-width: 400px;
  text-align: center;
}
.emoji-grid {
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 10px;
  max-height: 300px;
  overflow-y: auto;
  padding: 10px;
}
.emoji-item {
  font-size: 1.5rem;
  cursor: pointer;
  padding: 5px;
  border-radius: 5px;
  transition: background 0.2s;
}
.emoji-item:hover {
  background-color: #f0f0f0;
}
.emoji-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 1px solid #eee;
}
</style>

<script>
  // Emoji Picker Logic
  const commonEmojis = [
    '😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃','😉','😊','😇',
    '😍','🤩','😘','😗','😚','Vk','😋','😛','😜','🤪','😝','🤑','🤗',
    '🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😒','🙄','😬','🤥',
    '😌','😔','😪','🤤','😴','😷','🤒','🤕','🤢','🤮','🤧','🥵','🥶',
    '🥴','😵','🤯','🤠','🥳','😎','🤓','🧐','😕','😟','🙁','😮','😯',
    '😲','😳','🥺','😦','😧','😨','😰','😥','😢','😭','😱','😖','😣',
    '😞','😓','😩','😫','🥱','😤','😡','😠','🤬','😈','👿','💀','☠️',
    '💩','🤡','👹','👺','👻','👽','👾','🤖','😺','😸','😹','😻','😼',
    '😽','🙀','😿','😾','👋','🤚','Mw','✋','🖖','👌','🤏','✌️','🤞',
    '🤟','🤘','🤙','👈','👉','👆','🖕','👇','👍','👎','✊','👊','🤛',
    '🤜','👏','🙌','👐','🤲','🤝','🙏','✍️','💅','🤳','💪','🦾','🦵',
    '🦿','🦶','👣','👂','🦻','👃','🫀','🫁','🧠','🦷','🦴','👀','👁',
    '👅','👄','💋','🩸','🔥','🌈','✨','⭐','🌟','💥','💯','💢','💬',
    '📢','📣','🔔','🛑','🚧','🚨','🚩','🏳️','🏴','🏁','🏹','💘','💝',
    '💖','💗','💓','💞','💕','💟','❣️','💔','❤️','🧡','💛','💚','💙',
    '💜','🤎','🖤','🤍','🥘','🍕','🍔','🍟','🌭','🍿','🥞','🥐','🥯',
    '🥖','🥨','🧀','🥚','🍳','🥓','🥩','🍗','🍖','🍬','🍭','🍫','🍩',
    '🍪','🎂','🍰','🧁','🥧','🍨','🍦','🍧','🍮','🍯','🍼','🥛','☕',
    '🍵','🧉','🍾','🍷','🍸','🍹','🍺','🍻','🥂','🥃','🥤','🧊','🥣',
    '🥡','🥢','🧂'
  ];

  let currentTargetInputId = null;

  function openEmojiPicker(inputId) {
    currentTargetInputId = inputId;
    const modal = document.getElementById('emojiModal');
    const grid = document.getElementById('emojiGrid');
    
    // Populate if empty
    if (grid.children.length === 0) {
      commonEmojis.forEach(emoji => {
        const span = document.createElement('span');
        span.textContent = emoji;
        span.className = 'emoji-item';
        span.onclick = () => insertEmoji(emoji);
        grid.appendChild(span);
      });
    }
    
    modal.style.display = 'flex';
  }

  function closeEmojiPicker() {
    document.getElementById('emojiModal').style.display = 'none';
  }

  function insertEmoji(emoji) {
    if (currentTargetInputId) {
      const input = document.getElementById(currentTargetInputId);
      const start = input.selectionStart;
      const end = input.selectionEnd;
      const text = input.value;
      const before = text.substring(0, start);
      const after = text.substring(end, text.length);
      
      input.value = before + emoji + after;
      input.selectionStart = input.selectionEnd = start + emoji.length;
      input.focus();
      
      // Trigger input event for preview update
      input.dispatchEvent(new Event('input'));
    }
    closeEmojiPicker();
  }
  
  // Close emoji modal when clicking outside
  window.addEventListener('click', function(event) {
    const emojiModal = document.getElementById('emojiModal');
    if (event.target == emojiModal) {
      emojiModal.style.display = 'none';
    }
  });
</script>
@endsection