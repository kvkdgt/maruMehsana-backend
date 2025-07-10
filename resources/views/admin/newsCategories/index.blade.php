@extends('layouts.admin')

@section('title', 'News Categories')

@section('content')
<style>
/* News Categories Specific Styles */
.categories-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    padding: 30px;
    margin-bottom: 30px;
}

.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f8f9fa;
}

.categories-label {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.categories-label::before {
    content: '📰';
    font-size: 1.5rem;
}

.categories-description {
    color: #666;
    margin: 5px 0 0 0;
    font-size: 0.95rem;
}

.add-category-btn {
    background: linear-gradient(135deg, var(--accent), var(--accent-hover));
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(242, 101, 45, 0.3);
    display: flex;
    align-items: center;
    gap: 8px;
}

.add-category-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(242, 101, 45, 0.4);
}

.add-category-btn::before {
    content: '+';
    font-size: 1.2rem;
    font-weight: bold;
}

/* Filter Form Styles */
.filter-form {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    border: 1px solid #e9ecef;
}

.filter-items {
    display: grid;
    grid-template-columns: 1fr auto auto auto auto;
    gap: 15px;
    align-items: center;
}

.search-input, .status-select, .sort-select {
    padding: 10px 15px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: border-color 0.3s ease;
    background: white;
}

.search-input:focus, .status-select:focus, .sort-select:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(242, 101, 45, 0.1);
}

.filter-btn, .reset-btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.filter-btn {
    background: var(--primary);
    color: white;
}

.filter-btn:hover {
    background: var(--primary-light);
    transform: translateY(-1px);
}

.reset-btn {
    background: #6c757d;
    color: white;
}

.reset-btn:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

/* Alert Styles */
.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
    position: relative;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-icon {
    font-size: 1.2rem;
    font-weight: bold;
}

.alert-text {
    flex: 1;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: inherit;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.close-btn:hover {
    opacity: 1;
}

/* Table Styles */
.table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.categories-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.categories-table th {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: white;
    padding: 18px 15px;
    text-align: left;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.categories-table td {
    padding: 15px;
    border-bottom: 1px solid #f8f9fa;
    vertical-align: middle;
}

.categories-table tr:hover {
    background: rgba(44, 62, 80, 0.02);
}

.categories-table tr:last-child td {
    border-bottom: none;
}

/* Color Preview */
.color-preview {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    display: inline-block;
}

/* Status Badge */
.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.active {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
}

.status-badge.inactive {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
    align-items: center;
}

.edit-btn, .delete-btn, .toggle-btn {
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.edit-btn {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}

.edit-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
}

.delete-btn {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.delete-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.toggle-btn {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.toggle-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(3px);
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        transform: scale(0.7) translateY(-50px);
        opacity: 0;
    }
    to {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
}

.modal-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: white;
    padding: 20px 25px;
    border-radius: 15px 15px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-size: 1.3rem;
    font-weight: 600;
    margin: 0;
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.close-btn:hover {
    opacity: 1;
}

.modal-body {
    padding: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--primary);
    font-size: 0.9rem;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(242, 101, 45, 0.1);
}

.form-check {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
}

.form-check input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: var(--accent);
}

.submit-btn {
    background: linear-gradient(135deg, var(--accent), var(--accent-hover));
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(242, 101, 45, 0.3);
}

/* Pagination Styles */
.pagination-container {
    padding: 20px;
    display: flex;
    justify-content: center;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

/* Responsive */
@media (max-width: 768px) {
    .header-container {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }

    .filter-items {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .categories-table {
        font-size: 0.8rem;
    }

    .categories-table th,
    .categories-table td {
        padding: 10px 8px;
    }

    .action-buttons {
        flex-direction: column;
        gap: 5px;
    }

    .modal-content {
        width: 95%;
        margin: 20px;
    }
}
</style>

<div class="categories-container">
  <div class="header-container">
    <div>
      <h3 class="categories-label">News Categories</h3>
      <p class="categories-description">Manage all news categories for better content organization.</p>
    </div>
    <button class="add-category-btn" id="openModalBtn">Add New Category</button>
  </div>

  <form action="{{ route('admin.news-categories') }}" method="GET" class="filter-form">
    <div class="filter-items">
      <!-- Search Field -->
      <input type="text" name="search" placeholder="Search categories..." value="{{ request()->search }}" class="search-input">

      <!-- Status Filter -->
      <select name="status" class="status-select">
        <option value="">All Status</option>
        <option value="active" {{ request()->status === 'active' ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{ request()->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
      </select>

      <!-- Sort Filter -->
      <select name="sort_by" class="sort-select">
        <option value="">Default Order</option>
        <option value="name_asc" {{ request()->sort_by === 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
        <option value="name_desc" {{ request()->sort_by === 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
        <option value="newest" {{ request()->sort_by === 'newest' ? 'selected' : '' }}>Newest First</option>
        <option value="oldest" {{ request()->sort_by === 'oldest' ? 'selected' : '' }}>Oldest First</option>
      </select>

      <button type="submit" class="filter-btn">Apply Filter</button>
      <a href="{{ route('admin.news-categories') }}" class="reset-btn">Reset Filter</a>
    </div>
  </form>

  @if (session('success'))
  <div class="alert alert-success">
    <span class="alert-icon">✓</span>
    <div class="alert-text">
      {{ session('success') }}
    </div>
    <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
  </div>
  @endif

  @if (session('error'))
  <div class="alert alert-danger">
    <span class="alert-icon">⚠</span>
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
          <th>Color</th>
          <th>Category Name</th>
          <th>Description</th>
          <th>Status</th>
          <th>Sort Order</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($newsCategories as $index => $category)
        <tr>
          <td>{{ $newsCategories->firstItem() + $index }}</td>
          <td>
            <div class="color-preview" style="background-color: {{ $category->color }};"></div>
          </td>
          <td>
            <strong>{{ $category->name }}</strong>
            <br>
            <small style="color: #666;">{{ $category->slug }}</small>
          </td>
          <td>
            @if($category->description)
              {{ Str::limit($category->description, 50) }}
            @else
              <em style="color: #999;">No description</em>
            @endif
          </td>
          <td>
            <span class="status-badge {{ $category->status ? 'active' : 'inactive' }}">
              {{ $category->status ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td>{{ $category->sort_order }}</td>
          <td>{{ $category->created_at->format('M d, Y') }}</td>
          <td>
            <div class="action-buttons">
              <button type="button" class="edit-btn" onclick="editCategory({{ $category->id }})">
                📝 Edit
              </button>
              <button type="button" class="toggle-btn" onclick="toggleStatus({{ $category->id }})">
                🔄 Toggle
              </button>
              <form action="{{ route('news-categories.delete', $category->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirmDelete()">
                @csrf
                @method('DELETE')
                <button type="submit" class="delete-btn">🗑️ Delete</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
            <div style="font-size: 3rem; margin-bottom: 10px;">📂</div>
            <strong>No news categories found</strong>
            <br>
            <small>Create your first category to get started!</small>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
    
    @if($newsCategories->hasPages())
    <div class="pagination-container">
      {{ $newsCategories->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
    @endif
  </div>
</div>

<!-- Add/Edit Modal -->
<div id="categoryModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modal-title" class="modal-title">Add New Category</h3>
      <button class="close-btn" id="closeModalBtn">&times;</button>
    </div>
    <div class="modal-body">
      <form action="{{ route('news-categories.store') }}" method="POST" id="categoryForm">
        @csrf
        <input type="hidden" id="category-id" name="id">

        <div class="form-group">
          <label for="category-name" class="form-label">Category Name *</label>
          <input type="text" id="category-name" name="name" class="form-control" required placeholder="Enter category name">
        </div>

        <div class="form-group">
          <label for="category-description" class="form-label">Description</label>
          <textarea id="category-description" name="description" class="form-control" rows="3" placeholder="Enter category description (optional)"></textarea>
        </div>

        <div class="form-group">
          <label for="category-color" class="form-label">Category Color *</label>
          <input type="color" id="category-color" name="color" class="form-control" value="#2c3e50" required style="height: 50px;">
        </div>

        <div class="form-group">
          <label for="category-sort-order" class="form-label">Sort Order</label>
          <input type="number" id="category-sort-order" name="sort_order" class="form-control" min="0" value="0" placeholder="0">
        </div>

        <div class="form-group">
          <div class="form-check">
            <input type="checkbox" id="category-status" name="status" value="1" checked>
            <label for="category-status" class="form-label">Active Status</label>
          </div>
        </div>

        <button type="submit" class="submit-btn" id="submit-btn">Add Category</button>
      </form>
    </div>
  </div>
</div>

<script>
// Confirm delete function
function confirmDelete() {
  return confirm("Are you sure you want to delete this news category? This action cannot be undone.");
}

// Modal functionality
var modal = document.getElementById("categoryModal");
var btn = document.getElementById("openModalBtn");
var span = document.getElementById("closeModalBtn");

// Open modal for adding new category
btn.onclick = function() {
  modal.style.display = "flex";
  document.getElementById("modal-title").innerText = "Add New Category";
  document.getElementById('categoryForm').action = "{{ route('news-categories.store') }}";
  document.getElementById("category-id").value = '';
  document.getElementById("submit-btn").innerText = "Add Category";
  resetForm();
}

// Close modal
span.onclick = function() {
  modal.style.display = "none";
}

// Close modal when clicking outside
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}

// Reset form function
function resetForm() {
  document.getElementById("category-name").value = '';
  document.getElementById("category-description").value = '';
  document.getElementById("category-color").value = '#2c3e50';
  document.getElementById("category-sort-order").value = '0';
  document.getElementById("category-status").checked = true;
}

// Edit category function
function editCategory(id) {
  fetch(`/admin/news-categories/edit/${id}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const category = data.data;
        
        // Update modal
        modal.style.display = "flex";
        document.getElementById("modal-title").innerText = "Edit Category";
        document.getElementById('categoryForm').action = "{{ route('news-categories.update') }}";
        document.getElementById("submit-btn").innerText = "Update Category";
        
        // Fill form
        document.getElementById("category-id").value = category.id;
        document.getElementById("category-name").value = category.name;
        document.getElementById("category-description").value = category.description || '';
        document.getElementById("category-color").value = category.color;
        document.getElementById("category-sort-order").value = category.sort_order;
        document.getElementById("category-status").checked = category.status;
      } else {
        alert('Error loading category data');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error loading category data');
    });
}

// Toggle status function
function toggleStatus(id) {
  if (confirm('Are you sure you want to change the status of this category?')) {
    fetch(`/admin/news-categories/toggle-status/${id}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload(); // Reload page to show updated status
      } else {
        alert('Error updating status');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error updating status');
    });
  }
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      if (alert.parentElement) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => {
          alert.style.display = 'none';
        }, 300);
      }
    }, 5000);
  });
});

// Form validation
document.getElementById('categoryForm').addEventListener('submit', function(e) {
  const name = document.getElementById('category-name').value.trim();
  const color = document.getElementById('category-color').value;
  
  if (!name) {
    e.preventDefault();
    alert('Category name is required');
    document.getElementById('category-name').focus();
    return;
  }
  
  if (!color || !color.match(/^#[0-9A-Fa-f]{6}$/)) {
    e.preventDefault();
    alert('Please select a valid color');
    document.getElementById('category-color').focus();
    return;
  }
});
</script>

@endsection