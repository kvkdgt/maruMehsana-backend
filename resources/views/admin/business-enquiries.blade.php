@extends('layouts.admin')

@section('title', 'Business Enquiry')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/business_enquiry.css') }}">
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="enquiry-container">
  <div class="header-container">
    <div>
      <h3 class="enquiry-label">Business Enquiries</h3>
      <p class="enquiry-description">Manage all business enquiries from here</p>
    </div>
  </div>

  <form action="{{ route('admin.business-enquiry') }}" method="GET" class="filter-form">
    <div class="filter-items">
      <select name="status" class="status-dropdown">
        <option value="">Filter by Status</option>
        <option value="Pending" {{ request()->status == 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="Done" {{ request()->status == 'Done' ? 'selected' : '' }}>Done</option>
      </select>
      <button type="submit" class="filter-btn">Apply Filter</button>
      <a href="{{ route('admin.business-enquiry') }}" class="reset-btn">Reset Filter</a>
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
    <table class="enquiry-table">
      <thead>
        <tr>
          <th>Sr. No.</th>
          <th>Business Name</th>
          <th>Owner Name</th>
          <th>Mobile No.</th>
          <th>Whatsapp No.</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($business_enquiries as $index => $enquiry)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $enquiry->business_name }}</td>
          <td>{{ $enquiry->owner_name }}</td>
          <td>{{ $enquiry->mobile_no }}</td>
          <td>{{ $enquiry->whatsapp_no }}</td>
          <td onclick="toggleDropdown(this)">
            <span class="status-label {{ strtolower($enquiry->status) }}">
              {{ ucfirst($enquiry->status) }}
            </span>
            <select name="status" class="status-dropdown hidden" data-id="{{ $enquiry->id }}" onchange="updateStatus(this)">
              <option value="Pending" {{ $enquiry->status == 'Pending' ? 'selected' : '' }}>Pending</option>
              <option value="Done" {{ $enquiry->status == 'Done' ? 'selected' : '' }}>Done</option>
            </select>
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

<script>
  function toggleDropdown(td) {
    let span = td.querySelector(".status-label");
    let select = td.querySelector(".status-dropdown");

    span.classList.add("hidden"); // Hide label
    select.classList.remove("hidden"); // Show dropdown
    select.focus(); // Focus on dropdown
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

        // Update label text
        span.textContent = status.charAt(0).toUpperCase() + status.slice(1);

        // Update label class
        span.className = `status-label ${status.toLowerCase()}`;

        // Hide dropdown and show label again
        select.classList.add("hidden");
        span.classList.remove("hidden");
      })
      .catch(error => console.error("Error:", error));
  }
</script>


@endsection