@extends('layouts.admin')

@section('title', 'Business Enquiry')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/business_enquiry.css') }}">
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="enquiry-container">
  <div class="header-container">
    <div>
      <h3 class="categories-label" style="font-size: 1.5rem; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">Business Enquiries</h3>
      <p class="categories-description" style="color: #777; font-size: 0.95rem;">Manage and respond to business-related enquiries</p>
    </div>
  </div>

  <div class="filter-section">
    <form action="{{ route('admin.business-enquiry') }}" method="GET">
      <div class="filter-grid">
        <div class="filter-control">
          <label>Search</label>
          <input type="text" name="search" placeholder="Business, Owner or Phone" value="{{ request()->search }}" class="filter-input">
        </div>

        <div class="filter-control">
          <label>Status</label>
          <select name="status" class="filter-input">
            <option value="">All Statuses</option>
            <option value="Pending" {{ request()->status == 'Pending' ? 'selected' : '' }}>Pending</option>
            <option value="Done" {{ request()->status == 'Done' ? 'selected' : '' }}>Done</option>
          </select>
        </div>

        <div class="filter-control">
          <label>Start Date</label>
          <input type="date" name="start_date" value="{{ request()->start_date }}" class="filter-input">
        </div>

        <div class="filter-control">
          <label>End Date</label>
          <input type="date" name="end_date" value="{{ request()->end_date }}" class="filter-input">
        </div>

        <div class="btn-group">
          <button type="submit" class="filter-btn">Apply</button>
          <a href="{{ route('admin.business-enquiry') }}" class="reset-btn">Reset</a>
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
          <th>Business & Owner</th>
          <th>Contact Info</th>
          <th>Created At</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($business_enquiries as $index => $enquiry)
        <tr>
          <td>{{ $business_enquiries->firstItem() + $index }}</td>
          <td>
            <div style="display: flex; flex-direction: column;">
                <span style="font-weight: 600; color: #2c3e50;">{{ $enquiry->business_name }}</span>
                <span style="font-size: 0.85rem; color: #777;"><i class="fas fa-user" style="font-size: 0.75rem; margin-right: 5px;"></i> {{ $enquiry->owner_name }}</span>
            </div>
          </td>
          <td>
            <div style="display: flex; flex-direction: column; gap: 5px;">
                <span style="font-size: 0.9rem;"><i class="fas fa-phone-alt" style="color: #3498db; width: 15px;"></i> {{ $enquiry->mobile_no }}</span>
                <span style="font-size: 0.9rem;"><i class="fab fa-whatsapp" style="color: #27ae60; width: 15px;"></i> {{ $enquiry->whatsapp_no }}</span>
            </div>
          </td>
          <td>{{ $enquiry->created_at->format('d M Y, h:i A') }}</td>
          <td onclick="toggleDropdown(this)" style="cursor: pointer;">
            <div class="status-wrapper">
                <span class="badge-premium {{ $enquiry->status == 'Done' ? 'badge-success' : 'badge-warning' }} status-label">
                  {{ ucfirst($enquiry->status) }}
                </span>
                <select name="status" class="filter-input hidden status-select" data-id="{{ $enquiry->id }}" onchange="updateStatus(this)">
                  <option value="Pending" {{ $enquiry->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                  <option value="Done" {{ $enquiry->status == 'Done' ? 'selected' : '' }}>Done</option>
                </select>
                <i class="fas fa-edit" style="font-size: 0.7rem; color: #ccc; margin-left: 5px;"></i>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="pagination-container">
      {{ $business_enquiries->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>

  </div>
</div>

<style>
  /* No additional styles needed for container, using global utilities */
  .hidden { display: none; }
  .status-wrapper { display: flex; align-items: center; }
</style>

<script>
  function toggleDropdown(td) {
    let span = td.querySelector(".status-label");
    let select = td.querySelector(".status-select");
    let icon = td.querySelector(".fa-edit");

    if(span.classList.contains('hidden')) return;

    span.classList.add("hidden");
    if(icon) icon.classList.add("hidden");
    select.classList.remove("hidden");
    select.focus();
  }

  function updateStatus(select) {
    let enquiryId = select.getAttribute("data-id");
    let status = select.value;

    fetch(`/admin/business-enquiry/update/${enquiryId}`, {
        method: "PUT",
        headers: {
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          status: status
        }),
      })
      .then(response => response.json())
      .then(data => {
        let td = select.closest("td");
        let span = td.querySelector(".status-label");
        let icon = td.querySelector(".fa-edit");

        span.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        span.className = `badge-premium ${status == 'Done' ? 'badge-success' : 'badge-warning'} status-label`;

        select.classList.add("hidden");
        span.classList.remove("hidden");
        if(icon) icon.classList.remove("hidden");
      })
      .catch(error => console.error("Error:", error));
  }
</script>


@endsection